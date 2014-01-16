<?php

namespace Core\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class ProjectTable
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

    public function getProject($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_config_project' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveProject(Project $project)
    {
        $data = get_object_vars($project);
        unset($data['id_config_project']);

        $id = (int)$project->id_config_project;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            return $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getProject($id)) {
                $this->tableGateway->update($data, array('id_config_project' => $id));
                return $id;
            } else {
                throw new \Exception('Module id does not exist');
            }
        }
    }

    public function deleteProject($id)
    {
        $this->dropProjectFromGroup(null,$id);
        $this->tableGateway->delete(array('id_config_project' => $id));
    }

    public function getProjectsToGroup(Group $group)
    {
        $sql = $this->tableGateway->getSql();

        $select = $sql->select();
        $expression = new Expression('perm_project_group.id_config_project = '.$this->tableGateway->getTable().'.id_config_project AND (id_auth_group='.intval($group->id_auth_group).')');
        $select->join(
            'perm_project_group',$expression,
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

    public function dropProjectFromGroup(Group $group=null,$projectId)
    {
        $where = new \Zend\Db\Sql\Where();
        $where->addPredicate(new Operator('id_config_project',Operator::OPERATOR_EQUAL_TO,$projectId));
        if($group)
        {
            $where->andPredicate(new Operator('id_auth_group',Operator::OPERATOR_EQUAL_TO,$group->id_auth_group));
        }

        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $del = $sql->delete('perm_project_group')->where($where);
        $sql->prepareStatementForSqlObject($del)->execute();
    }

    public function addProjectToGroup(Group $group, $projectId)
    {
        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->getAdapter());
        $ins = $sql->insert('perm_project_group');
        $ins->values(array(
            'id_auth_group'=>$group->id_auth_group,
            'id_config_project'=>$projectId
        ));
        $sql->prepareStatementForSqlObject($ins)->execute();
    }
}
