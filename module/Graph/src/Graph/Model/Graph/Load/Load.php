<?php
namespace Graph\Model\Graph\Load;
use Graph\Model\Graph\AbstractGraph;

class Load extends AbstractGraph
{
    protected $rrdFormat = '%.2lf';
    protected function generateGraphDefinition()
    {
        $this->dataSources = array('shortterm', 'midterm', 'longterm');
        $this->dataSourceNames = array(
            'shortterm' => ' 1 min',
            'midterm' => ' 5 min',
            'longterm' => '15 min',
        );
        $this->colors = array(
            'shortterm' => '00ff00',
            'midterm' => '0000ff',
            'longterm' => 'ff0000',
        );
        $this->title = 'System load (%s)';
        $this->rrdRenderer->setTitleVertical('System load');
    }
}
