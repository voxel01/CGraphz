<?php
namespace Core\Model;

class Filter
{
    public $id_config_plugin_filter=0;
    public $plugin;
    public $plugin_instance;
    public $type;
    public $type_instance;
    public $plugin_filter_desc;
    public $plugin_order;

    public function exchangeArray($data)
    {
        $this->id_config_plugin_filter = (isset($data['id_config_plugin_filter'])) ? $data['id_config_plugin_filter'] : null;
        $this->plugin = (isset($data['plugin'])) ? $data['plugin'] : null;
        $this->plugin_instance = (isset($data['plugin_instance'])) ? $data['plugin_instance'] : null;
        $this->type = (isset($data['type'])) ? $data['type'] : null;
        $this->type_instance = (isset($data['type_instance'])) ? $data['type_instance'] : null;
        $this->plugin_filter_desc = (isset($data['plugin_filter_desc'])) ? $data['plugin_filter_desc'] : null;
        $this->plugin_order = (isset($data['plugin_order'])) ? $data['plugin_order'] : null;
    }
}
