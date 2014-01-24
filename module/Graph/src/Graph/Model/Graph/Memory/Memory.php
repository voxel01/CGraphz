<?php
namespace Graph\Model\Graph\Memory;
use Graph\Model\Graph\AbstractGraphStacked;

class Memory extends AbstractGraphStacked
{
    protected $rrdFormat = '%5.1lf%s';
    protected function generateGraphDefinition()
    {
        $this->order = array(
            'free',
            'buffered',
            'cached',
            'locked',
            'used',
        );
        $this->dataSourceNames = array(
            'free'     => 'Free',
            'cached'   => 'Cached',
            'buffered' => 'Buffered',
            'locked'   => 'Locked',
            'used'     => 'Used',
        );
        $this->colors = array(
            'free' => '00e000',
            'cached' => '0000ff',
            'buffered' => 'ffb000',
            'locked' => 'ff00ff',
            'used' => 'ff0000',
        );

        $this->title = 'Physical memory utilization';
        $this->rrdRenderer->setTitleVertical('Bytes');
    }
}
