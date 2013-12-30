<?php
namespace Graph\Model\Graph\Apache;

class ApacheConnections extends AbstractApache
{
    protected function generateGraphDefinition()
    {
        $this->dataSources = array('value');
        $this->dataSourceNames = array(
            'value' => 'Conns/s',
        );
        $this->colors = array(
            'value' => '00b000',
        );
        $this->title = sprintf('Webserver Connections%s',
            !empty($this->plugin->pluginInstance) ? ' ('.$this->plugin->pluginInstance.')' : '');
        $this->rrdRenderer->setTitleVertical('Conns/s');
        $this->versionCompatibility();
    }
}
