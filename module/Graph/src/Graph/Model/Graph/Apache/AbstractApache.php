<?php
namespace Graph\Model\Graph\Apache;
use Graph\Model\Graph\AbstractGraph;

abstract class AbstractApache extends AbstractGraph
{
    protected $rrdFormat = '%5.1lf';
    protected $networkDataSize = 'bytes';
    protected function versionCompatibility()
    {
        # backwards compatibility
        if ($this->version < 5) {
            $this->dataSources = array('count');
            if (count($this->dataSourceNames) == 1) {
                $this->dataSourceNames['count'] = $this->dataSourceNames['value'];
                unset($this->dataSourceNames['value']);
            }
            if (count($this->colors) == 1) {
                $this->colors['count'] = $this->colors['value'];
                unset($this->colors['value']);
            }
        }
    }
}
