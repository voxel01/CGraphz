<?php

namespace Core\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class UserTable
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

    public function getUser($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_auth_user' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveUser(User $user)
    {
        $data = get_object_vars($user);
        unset($data['id_auth_user']);

        $id = (int)$user->id_auth_user;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            return $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getUser($id)) {
                $this->tableGateway->update($data, array('id_auth_user' => $id));
                return $id;
            } else {
                throw new \Exception('Module id does not exist');
            }
        }
    }

    public function deleteUser($id)
    {
        $this->dropUserFromGroup(null,$id);
        $this->tableGateway->delete(array('id_auth_user' => $id));
    }

    public function getUsersToGroup(Group $group)
    {
        $sql = $this->tableGateway->getSql();

        $select = $sql->select();
        $expression = new Expression('auth_user_group.id_auth_user = '.$this->tableGateway->getTable().'.id_auth_user AND (id_auth_group='.intval($group->id_auth_group).')');
        $select->join(
            'auth_user_group',$expression,
            array('id_auth_group'),
            Select::JOIN_LEFT.' '.Select::JOIN_OUTER
        );
        //echo $select->getSqlString();exit();

        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $users = array('member'=>array(),'available'=>array());
        $proto = $this->tableGateway->getResultSetPrototype()->getArrayObjectPrototype();
        foreach($result as $row)
        {
            $ins = ($row['id_auth_group'])?'member':'available';
            $g = clone $proto;
            $g->exchangeArray($row);
            $users[$ins][] = $g;
        }
        return $users;
    }

    public function dropUserFromGroup(Group $group=null,$userId)
    {
        $where = new \Zend\Db\Sql\Where();
        $where->addPredicate(new Operator('id_auth_user',Operator::OPERATOR_EQUAL_TO,$userId));
        if($group)
        {
            $where->andPredicate(new Operator('id_auth_group',Operator::OPERATOR_EQUAL_TO,$group->id_auth_group));
        }

        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $del = $sql->delete('auth_user_group')->where($where);
        $sql->prepareStatementForSqlObject($del)->execute();
    }

    public function addUserToGroup(Group $group, $userId)
    {
        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $ins = $sql->insert('auth_user_group');
        $ins->values(array(
            'id_auth_group'=>$group->id_auth_group,
            'id_auth_user'=>$userId
        ));
        $sql->prepareStatementForSqlObject($ins)->execute();
    }
}
