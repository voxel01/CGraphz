<?php
namespace Graph\Model\Graph\Swap;
use Graph\Model\Graph\AbstractGraphStacked;

class Swap extends AbstractGraphStacked
{
    protected $rrdFormat = '%5.1lf%s';
    protected function generateGraphDefinition()
    {
        $this->order = array(
            'free', 'cached', 'used'
        );
        $this->dataSourceNames = array(
            'free'   => 'Free',
            'cached' => 'Cached',
            'used'   => 'Used',
        );
        $this->colors = array(
            'free'   => '00e000',
            'cached' => '0000ff',
            'used'   => 'ff0000',
        );

        $this->title = 'Swap utilization';
        $this->rrdRenderer->setTitleVertical('Bytes');
    }
}
