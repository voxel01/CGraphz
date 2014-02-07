<?php
namespace Core\Model;

class Environment
{
    public $id_config_environment=0;
    public $environment;
    public $environment_description;

    public function exchangeArray($data)
    {
        $this->id_config_environment = (isset($data['id_config_environment'])) ? $data['id_config_environment'] : null;
        $this->environment = (isset($data['environment'])) ? $data['environment'] : null;
        $this->environment_description = (isset($data['environment_description'])) ? $data['environment_description'] : null;
    }
}
