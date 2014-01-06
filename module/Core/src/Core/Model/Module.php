<?php
namespace Core\Model;

class Module
{
    public $id_perm_module;
    public $module;
    public $component;
    public $menu_name;
    public $menu_order;


    public function exchangeArray($data)
    {
        $this->id_perm_module     = (isset($data['id_perm_module'])) ? $data['id_perm_module'] : null;
        $this->module = (isset($data['module'])) ? $data['module'] : null;
        $this->component  = (isset($data['component'])) ? $data['component'] : null;
        $this->menu_name  = (isset($data['menu_name'])) ? $data['menu_name'] : null;
        $this->menu_order  = (isset($data['menu_order'])) ? $data['menu_order'] : null;
    }
}
