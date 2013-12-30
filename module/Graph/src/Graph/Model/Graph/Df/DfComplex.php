<?php
namespace Graph\Model\Graph\Df;
use Graph\Model\Graph\AbstractGraphStacked;
class DfComplex extends AbstractGraphStacked
{

    protected function generateGraphDefinition()
    {
        $this->order = array('reserved', 'free', 'used');
        $this->dataSourceNames = array(
            'reserved' => 'Reserved',
            'free' => 'Free',
            'used' => 'Used',
        );
        $this->colors = array(
            'reserved' => 'aaaaaa',
            'free' => '00ff00',
            'used' => 'ff0000',
        );

        $this->rrdRenderer->setTitleVertical('Bytes');
        $this->rrdFormat = '%5.1lf%sB';

        # backwards compatibility
        if ($this->version < 5) {
            $this->dataSources = array('free', 'used');
            $this->title = sprintf('Free space (%s)', $this->plugin->typeInstance);
        }
        else
        {
            $this->dataSources = array('value');
            $this->title = sprintf('Free space (%s)', $this->plugin->pluginInstance);
        }
    }
}
