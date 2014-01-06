<?php

namespace Core\Model;
use Zend\Db\TableGateway\TableGateway;

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
}
