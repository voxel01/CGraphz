<?php
namespace Core\Model;

class Plugin
{
    public $plugin;
    public $pluginCategory;
    public $pluginInstance;
    public $type;
    public $typeCategory;
    public $typeInstance;

    public function getUrlParams()
    {
        $return = array();
        foreach(get_object_vars($this) as $key => $val)
        {
            $return[$key] = $val?$val:'-';
        }
        return $return;
    }
}
