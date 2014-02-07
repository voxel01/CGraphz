<?php

namespace Core\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class ModuleTable
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

    public function getModule($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_perm_module' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveModule(Module $module)
    {
        $data = get_object_vars($module);
        unset($data['id_perm_module']);

        $id = (int)$module->id_perm_module;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getModule($id)) {
                $this->tableGateway->update($data, array('id_perm_module' => $id));
            } else {
                throw new \Exception('Module id does not exist');
            }
        }
    }

    public function deleteModule($id)
    {
        $this->tableGateway->delete(array('id_perm_module' => $id));
    }

    public function getModulesToGroup(Group $group)
    {
        $sql = $this->tableGateway->getSql();

        $select = $sql->select();
        $expression = new Expression('perm_module_group.id_perm_module = '.$this->tableGateway->getTable().'.id_perm_module AND (id_auth_group='.intval($group->id_auth_group).')');
        $select->join(
            'perm_module_group',$expression,
            array('id_auth_group'),
            Select::JOIN_LEFT.' '.Select::JOIN_OUTER
        );
        //echo $select->getSqlString();exit();

        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $projects = array('member'=>array(),'available'=>array());
        $proto = $this->tableGateway->getResultSetPrototype()->getArrayObjectPrototype();
        foreach($result as $row)
        {
            $ins = ($row['id_auth_group'])?'member':'available';
            $g = clone $proto;
            $g->exchangeArray($row);
            $projects[$ins][] = $g;
        }
        return $projects;
    }

    public function dropModuleFromGroup(Group $group=null,$projectId)
    {
        $where = new \Zend\Db\Sql\Where();
        $where->addPredicate(new Operator('id_perm_module',Operator::OPERATOR_EQUAL_TO,$projectId));
        if($group)
        {
            $where->andPredicate(new Operator('id_auth_group',Operator::OPERATOR_EQUAL_TO,$group->id_auth_group));
        }

        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $del = $sql->delete('perm_module_group')->where($where);
        $sql->prepareStatementForSqlObject($del)->execute();
    }

    public function addModuleToGroup(Group $group, $projectId)
    {
        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $ins = $sql->insert('perm_module_group');
        $ins->values(array(
            'id_auth_group'=>$group->id_auth_group,
            'id_perm_module'=>$projectId
        ));
        $sql->prepareStatementForSqlObject($ins)->execute();
    }
}