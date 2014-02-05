<?php

namespace Core\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class ServerTable
{
    /**
     * @var \Zend\Db\TableGateway\TableGateway
     */
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getServer($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_config_server' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getServerbyName($name)
    {
        $rowset = $this->tableGateway->select(array('server_name' => $name));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $name");
        }
        return $row;
    }

    public function saveServer(Server $server)
    {
        $data = get_object_vars($server);
        unset($data['id_config_server']);

        $id = (int)$server->id_config_server;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            return $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getServer($id)) {
                $this->tableGateway->update($data, array('id_config_server' => $id));
                return $id;
            } else {
                throw new \Exception('Server id does not exist');
            }
        }
    }

    public function findNew($dir)
    {
        $filelist= array();
        $regexHost = '/^([a-zA-Z0-9_-]+\\.)+[a-zA-Z0-9_-]+/';
        foreach (scandir($dir) as $file)
        {
            if($file == '.' || $file == '..' || $file == 'lost+found' || strpos($file,':')!==false || !preg_match($regexHost,$file))
            {
                continue;
            }
            $filelist[]= $file;
        }
        $where = new \Zend\Db\Sql\Predicate\In('server_name',$filelist);
        $resultset = $this->tableGateway->select($where);

        if($resultset)
        {
            foreach($resultset as $delete)
            {
                unset($filelist[array_search($delete->server_name,$filelist)]);
            }
        }
        return $filelist;
    }

    public function deleteServer($id)
    {
        $this->dropServerFromProject(null,$id);
        $this->tableGateway->delete(array('id_config_server' => $id));
    }

    public function getServersToProject(Project $project)
    {
        $sql = $this->tableGateway->getSql();

        $select = $sql->select();
        $expression = new Expression('config_server_project.id_config_server = '.$this->tableGateway->getTable().'.id_config_server AND (id_config_project='.intval($project->id_config_project).')');
        $select->join(
            'config_server_project',$expression,
            array('id_config_project'),
            Select::JOIN_LEFT.' '.Select::JOIN_OUTER
        );
        //echo $select->getSqlString();exit();

        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $servers = array('member'=>array(),'available'=>array());
        $proto = $this->tableGateway->getResultSetPrototype()->getArrayObjectPrototype();
        foreach($result as $row)
        {
            $ins = ($row['id_config_project'])?'member':'available';
            $g = clone $proto;
            $g->exchangeArray($row);
            $servers[$ins][] = $g;
        }
        return $servers;
    }

    public function dropServerFromProject(Project $project=null,$serverId)
    {
        $where = new \Zend\Db\Sql\Where();
        $where->addPredicate(new Operator('id_config_server',Operator::OPERATOR_EQUAL_TO,$serverId));
        if($project)
        {
            $where->andPredicate(new Operator('id_config_project',Operator::OPERATOR_EQUAL_TO,$project->id_config_project));
        }

        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $del = $sql->delete('config_server_project')->where($where);
        $sql->prepareStatementForSqlObject($del)->execute();
    }

    public function addServerToProject(Project $project, $serverId)
    {
        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $ins = $sql->insert('config_server_project');
        $ins->values(array(
            'id_config_project'=>$project->id_config_project,
            'id_config_server'=>$serverId
        ));
        $sql->prepareStatementForSqlObject($ins)->execute();
    }
}
