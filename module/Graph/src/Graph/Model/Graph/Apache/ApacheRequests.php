<?php
namespace Graph\Model\Graph\Apache;

class ApacheRequests extends AbstractApache
{
    protected function generateGraphDefinition()
    {
        $this->dataSources = array('value');
        $this->dataSourceNames = array(
            'value' => 'Requests/s',
        );
        $this->colors = array(
            'value' => '00b000',
        );
        $this->title = sprintf('Webserver Requests%s',
            !empty($this->plugin->pluginInstance) ? ' ('.$this->plugin->pluginInstance.')' : '');
        $this->rrdRenderer->setTitleVertical('Requests/s');
        $this->versionCompatibility();
    }
}
