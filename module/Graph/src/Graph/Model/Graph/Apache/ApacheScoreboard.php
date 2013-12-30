<?php
namespace Graph\Model\Graph\Apache;
use Graph\Model\Graph\AbstractGraphStacked;

class ApacheScoreboard extends AbstractGraphStacked
{
    protected $rrdFormat = '%5.1lf';
    protected $networkDataSize = 'bytes';
    protected function generateGraphDefinition()
    {
        $this->dataSources = array('value');
        $this->order = array(
            'open',
            'idle_cleanup',
            'finishing',
            'logging',
            'closing',
            'dnslookup',
            'keepalive',
            'sending',
            'reading',
            'starting',
            'waiting',

            'connect',
            'hard_error',
            'close',
            'response_end',
            'write',
            'response_start',
            'handle_request',
            'read_post',
            'request_end',
            'read',
            'request_start',
        );
        $this->dataSourceNames = array(
            'open'         => 'Open (empty)',
            'waiting'      => 'Waiting',
            'starting'     => 'Starting up',
            'reading'      => 'Reading request',
            'sending'      => 'Sending reply',
            'keepalive'    => 'Keepalive',
            'dnslookup'    => 'DNS Lookup',
            'closing'      => 'Closing',
            'logging'      => 'Logging',
            'finishing'    => 'Finishing',
            'idle_cleanup' => 'Idle cleanup',

            'connect'        => 'Connect (empty)',
            'close'          => 'Close',
            'hard_error'     => 'Hard error',
            'read'           => 'Read',
            'read_post'      => 'Read POST',
            'write'          => 'Write',
            'handle_request' => 'Handle request',
            'request_start'  => 'Request start',
            'request_end'    => 'Request end',
            'response_start' => 'Response start',
            'response_end'   => 'Response end',
        );
        $this->colors = array(
            'open'         => 'e0e0e0',
            'waiting'      => 'ffb000',
            'starting'     => 'ff00ff',
            'reading'      => '0000ff',
            'sending'      => '00e000',
            'keepalive'    => '0080ff',
            'dnslookup'    => 'ff0000',
            'closing'      => '000080',
            'logging'      => 'a000a0',
            'finishing'    => '008080',
            'idle_cleanup' => 'ffff00',

            'connect'        => 'e0e0e0',
            'close'          => '008080',
            'hard_error'     => 'ff0000',
            'read'           => 'ff00ff',
            'read_post'      => '00e000',
            'write'	         => '000080',
            'handle_request' => '0080ff',
            'request_start'  => 'ffb000',
            'request_end'	 => '0000ff',
            'response_start' => 'ffff00',
            'response_end'   => 'a000a0',
        );
        $this->title = sprintf('Webserver Scoreboard%s',
            !empty($this->plugin->pluginInstance) ? ' ('.$this->plugin->pluginInstance.')' : '');
        $this->rrdRenderer->setTitleVertical('Slots');
        $this->versionCompatibility();
    }
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
