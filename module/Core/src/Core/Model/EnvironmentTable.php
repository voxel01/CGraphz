<?php

namespace Core\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class EnvironmentTable
{
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

    public function getEnvironment($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_config_environment' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveEnvironment(Environment $environment)
    {
        $data = get_object_vars($environment);
        unset($data['id_config_environment']);

        $id = (int)$environment->id_config_environment;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getEnvironment($id)) {
                $this->tableGateway->update($data, array('id_config_environment' => $id));
            } else {
                throw new \Exception('Environment id does not exist');
            }
        }
    }

    public function deleteEnvironment($id)
    {
        $this->tableGateway->delete(array('id_config_environment' => $id));
    }

    public function getEnvironmentsToServer(Server $server)
    {
        $sql = $this->tableGateway->getSql();

        $select = $sql->select();
        $expression = new Expression('config_environment_server.id_config_environment = '.$this->tableGateway->getTable().'.id_config_environment AND (id_config_server='.intval($server->id_config_server).')');
        $select->join(
            'config_environment_server',$expression,
            array('id_config_server'),
            Select::JOIN_LEFT.' '.Select::JOIN_OUTER
        );
        //echo $select->getSqlString();exit();

        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $servers = array('member'=>array(),'available'=>array());
        $proto = $this->tableGateway->getResultSetPrototype()->getArrayObjectPrototype();
        foreach($result as $row)
        {
            $ins = ($row['id_config_server'])?'member':'available';
            $g = clone $proto;
            $g->exchangeArray($row);
            $servers[$ins][] = $g;
        }
        return $servers;
    }

    public function dropEnvironmentFromServer(Server $server=null,$environmentId)
    {
        $where = new \Zend\Db\Sql\Where();
        $where->addPredicate(new Operator('id_config_environment',Operator::OPERATOR_EQUAL_TO,$environmentId));
        if($server)
        {
            $where->andPredicate(new Operator('id_config_server',Operator::OPERATOR_EQUAL_TO,$server->id_config_server));
        }

        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $del = $sql->delete('config_environment_server')->where($where);
        $sql->prepareStatementForSqlObject($del)->execute();
    }

    public function addEnvironmentToServer(Server $server, $environmentId)
    {
        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $ins = $sql->insert('config_environment_server');
        $ins->values(array(
            'id_config_environment'=>$environmentId,
            'id_config_server'=>$server->id_config_server
        ));
        $sql->prepareStatementForSqlObject($ins)->execute();
    }
}
