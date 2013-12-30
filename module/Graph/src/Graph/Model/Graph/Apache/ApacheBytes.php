<?php
namespace Graph\Model\Graph\Apache;

class ApacheBytes extends AbstractApache
{
    protected function generateGraphDefinition()
    {
        $this->dataSources = array('value');
        $this->dataSourceNames = array(
            'value' => sprintf('%s/s', ucfirst($this->networkDataSize)),
        );
        $this->colors = array(
            'value' => '0000ff',
        );
        $this->title = sprintf('Webserver Traffic%s',
            !empty($this->plugin->pluginInstance) ? ' ('.$this->plugin->pluginInstance.')' : '');
        $this->rrdRenderer->setTitleVertical(sprintf('%s/s', ucfirst($this->networkDataSize)));
        $this->scale = $this->networkDataSize == 'bits' ? 8 : 1;
        $this->versionCompatibility();
    }
}
