<?php
namespace Core\Model;

class Server
{
    public $id_config_server=0;
    public $server_name;
    public $server_description;
    public $collectd_version;

    public function exchangeArray($data)
    {
        $this->id_config_server = (isset($data['id_config_server'])) ? $data['id_config_server'] : null;
        $this->server_name = (isset($data['server_name'])) ? $data['server_name'] : null;
        $this->server_description = (isset($data['server_description'])) ? $data['server_description'] : null;
        $this->collectd_version = (isset($data['collectd_version'])) ? $data['collectd_version'] : null;
    }
}
