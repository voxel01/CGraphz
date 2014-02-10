<?php

namespace Core\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class FilterTable
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

    public function getFilter($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_config_plugin_filter' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveFilter(Filter $filter)
    {
        $id = (int)$filter->id_config_plugin_filter;
        $data = get_object_vars($filter);
        unset($data['id_config_plugin_filter']);

        if ($id == 0) {
            $this->tableGateway->insert($data);
            return $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getFilter($id)) {
                $this->tableGateway->update($data, array('id_config_plugin_filter' => $id));
                return $id;
            } else {
                throw new \Exception('Filter id does not exist');
            }
        }
    }

    public function deleteFilter($id)
    {
        $this->dropFilterFromGroup(null,$id);
        $this->tableGateway->delete(array('id_config_plugin_filter' => $id));
    }

    public function getFiltersToGroup(Group $group)
    {
        $sql = $this->tableGateway->getSql();

        $select = $sql->select();
        $expression = new Expression('config_plugin_filter_group.id_config_plugin_filter = '.$this->tableGateway->getTable().'.id_config_plugin_filter AND (id_auth_group='.intval($group->id_auth_group).')');
        $select->join(
            'config_plugin_filter_group',$expression,
            array('id_auth_group'),
            Select::JOIN_LEFT.' '.Select::JOIN_OUTER
        );
        //echo $select->getSqlString();exit();

        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $filters = array('member'=>array(),'available'=>array());
        $proto = $this->tableGateway->getResultSetPrototype()->getArrayObjectPrototype();
        foreach($result as $row)
        {
            $ins = ($row['id_auth_group'])?'member':'available';
            $g = clone $proto;
            $g->exchangeArray($row);
            $filters[$ins][] = $g;
        }
        return $filters;
    }

    public function dropFilterFromGroup(Group $group=null,$filterId)
    {
        $where = new \Zend\Db\Sql\Where();
        $where->addPredicate(new Operator('id_config_plugin_filter',Operator::OPERATOR_EQUAL_TO,$filterId));
        if($group)
        {
            $where->andPredicate(new Operator('id_auth_group',Operator::OPERATOR_EQUAL_TO,$group->id_auth_group));
        }

        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $del = $sql->delete('config_plugin_filter_group')->where($where);
        $sql->prepareStatementForSqlObject($del)->execute();
    }

    public function addFilterToGroup(Group $group, $filterId)
    {
        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $ins = $sql->insert('config_plugin_filter_group');
        $ins->values(array(
            'id_auth_group'=>$group->id_auth_group,
            'id_config_plugin_filter'=>$filterId
        ));
        $sql->prepareStatementForSqlObject($ins)->execute();
    }
}
