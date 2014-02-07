<?php

namespace Core\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class RoleTable
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

    public function getRole($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_config_role' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveRole(Role $role)
    {
        $data = get_object_vars($role);
        unset($data['id_config_role']);

        $id = (int)$role->id_config_role;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getRole($id)) {
                $this->tableGateway->update($data, array('id_config_role' => $id));
            } else {
                throw new \Exception('Role id does not exist');
            }
        }
    }

    public function deleteRole($id)
    {
        $this->tableGateway->delete(array('id_config_role' => $id));
    }

    public function getRolesToServer(Server $server)
    {
        $sql = $this->tableGateway->getSql();

        $select = $sql->select();
        $expression = new Expression('config_role_server.id_config_role = '.$this->tableGateway->getTable().'.id_config_role AND (id_config_server='.intval($server->id_config_server).')');
        $select->join(
            'config_role_server',$expression,
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

    public function dropServerFromRole(Role $role=null,$serverId)
    {
        $where = new \Zend\Db\Sql\Where();
        $where->addPredicate(new Operator('id_config_server',Operator::OPERATOR_EQUAL_TO,$serverId));
        if($role)
        {
            $where->andPredicate(new Operator('id_config_role',Operator::OPERATOR_EQUAL_TO,$role->id_config_role));
        }

        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $del = $sql->delete('config_role_server')->where($where);
        $sql->prepareStatementForSqlObject($del)->execute();
    }

    public function addServerToRole(Role $role, $serverId)
    {
        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $ins = $sql->insert('config_role_server');
        $ins->values(array(
            'id_config_role'=>$role->id_config_role,
            'id_config_server'=>$serverId
        ));
        $sql->prepareStatementForSqlObject($ins)->execute();
    }
}
