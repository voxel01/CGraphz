<?php
namespace Core\Model;

class Group
{
    public $id_auth_group;
    public $group;
    public $group_description;


    public function exchangeArray($data)
    {
        $this->id_auth_group     = (isset($data['id_auth_group'])) ? $data['id_auth_group'] : null;
        $this->group = (isset($data['group'])) ? $data['group'] : null;
        $this->group_description  = (isset($data['group_description'])) ? $data['group_description'] : null;
    }
}
