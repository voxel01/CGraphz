<?php
namespace Graph\Model\Graph\Apache;

class ApacheIdleWorkers extends AbstractApache
{
    protected function generateGraphDefinition()
    {
        $this->dataSources = array('value');
        $this->dataSourceNames = array(
            'value' => 'Workers',
        );
        $this->colors = array(
            'value' => '0000ff',
        );
        $this->title = sprintf('Webserver Idle Workers%s',
            !empty($this->plugin->pluginInstance) ? ' ('.$this->plugin->pluginInstance.')' : '');
        $this->rrdRenderer->setTitleVertical('Workers');
        $this->versionCompatibility();
    }
}
