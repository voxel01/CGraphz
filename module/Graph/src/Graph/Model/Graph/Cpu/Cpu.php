<?php

namespace Graph\Model\Graph\Cpu;
use Graph\Model\Graph\AbstractGraphStacked;
class Cpu extends AbstractGraphStacked
{
    protected $dataSources = array('value');
    protected $order = array('idle', 'nice', 'user', 'wait', 'system', 'softirq', 'interrupt', 'steal');
    protected $rrdFormat = '%5.2lf';
    protected function generateGraphDefinition()
    {
        $this->dataSourceNames = array(
            'idle' => 'Idle',
            'nice' => 'Nice',
            'user' => 'User',
            'wait' => 'Wait-IO',
            'system' => 'System',
            'softirq' => 'SoftIRQ',
            'interrupt' => 'IRQ',
            'steal' => 'Steal',
        );
        $this->colors = array(
            'idle' => 'e8e8e8',
            'nice' => '00e000',
            'user' => '0000ff',
            'wait' => 'ffb000',
            'system' => 'ff0000',
            'softirq' => 'ff00ff',
            'interrupt' => 'a000a0',
            'steal' => '000000',
        );

        $this->title = sprintf('CPU-%s usage', $this->plugin->pluginInstance);
        $this->rrdRenderer->setTitleVertical('Jiffies');
        $this->setRrdtoolOptions(' -u 100');
    }
}
