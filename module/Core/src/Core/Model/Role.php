<?php
namespace Core\Model;

class Role
{
    public $id_config_role=0;
    public $role;
    public $role_description;

    public function exchangeArray($data)
    {
        $this->id_config_role = (isset($data['id_config_role'])) ? $data['id_config_role'] : null;
        $this->role = (isset($data['role'])) ? $data['role'] : null;
        $this->role_description = (isset($data['role_description'])) ? $data['role_description'] : null;
    }
}
