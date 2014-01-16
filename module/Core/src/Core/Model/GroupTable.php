<?php

namespace Core\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class GroupTable
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

    public function getGroup($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_auth_group' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveGroup(Group $group)
    {
        $data = get_object_vars($group);
        unset($data['id_auth_group']);

        $id = (int)$group->id_auth_group;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            return $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getGroup($id)) {
                $this->tableGateway->update($data, array('id_auth_group' => $id));
                return $id;
            } else {
                throw new \Exception('Module id does not exist');
            }
        }
    }

    public function deleteGroup($id)
    {
        $this->dropGroupFromModule(null,$id);
        $this->tableGateway->delete(array('id_auth_group' => $id));
    }

    public function getGroupsToModule(Module $module)
    {
        $sql = $this->tableGateway->getSql();

        $select = $sql->select();
        $expression = new Expression('perm_module_group.id_auth_group = '.$this->tableGateway->getTable().'.id_auth_group AND (id_perm_module='.intval($module->id_perm_module).')');
        $select->join(
            'perm_module_group',$expression,
            array('id_perm_module'),
            Select::JOIN_LEFT.' '.Select::JOIN_OUTER
        );
        //echo $select->getSqlString();exit();

        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $groups = array('allowed'=>array(),'available'=>array());
        $proto = $this->tableGateway->getResultSetPrototype()->getArrayObjectPrototype();
        foreach($result as $row)
        {
            $ins = ($row['id_perm_module'])?'allowed':'available';
            $g = clone $proto;
            $g->exchangeArray($row);
            $groups[$ins][] = $g;
        }
        return $groups;
    }

    public function dropGroupFromModule(Module $module=null,$groupId)
    {
        $where = new \Zend\Db\Sql\Where();
        $where->addPredicate(new Operator('id_auth_group',Operator::OPERATOR_EQUAL_TO,$groupId));
        if($module)
        {
            $where->andPredicate(new Operator('id_perm_module',Operator::OPERATOR_EQUAL_TO,$module->id_perm_module));
        }

        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $del = $sql->delete('perm_module_group')->where($where);
        $sql->prepareStatementForSqlObject($del)->execute();
    }

    public function addGroupToModule(Module $module, $groupId)
    {
        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $ins = $sql->insert('perm_module_group');
        $ins->values(array(
            'id_perm_module'=>$module->id_perm_module,
            'id_auth_group'=>$groupId
        ));
        $sql->prepareStatementForSqlObject($ins)->execute();
    }
}
