<?php
namespace Graph\Model\Graph\Swap;
use Graph\Model\Graph\AbstractGraphStacked;

class SwapIo extends AbstractGraphStacked
{
    protected $rrdFormat = '%5.1lf%s';
    protected function generateGraphDefinition()
    {
        $this->order = array(
            'out', 'in'
        );
        $this->dataSourceNames = array(
            'out' => 'Out',
            'in'  => 'In',
        );
        $this->colors = array(
            'out' => '0000ff',
            'in'  => '00b000',
        );

        $this->title = 'Swapped I/O pages';
        $this->rrdRenderer->setTitleVertical('Pages');
    }
}
