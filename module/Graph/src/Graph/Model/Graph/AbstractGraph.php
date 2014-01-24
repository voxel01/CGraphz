<?php

namespace Graph\Model\Graph;

use Core\Model\Plugin;

abstract class AbstractGraph {

    const MAX_LEGEND_LENGTH= 90;

    protected $datadir;
    protected $cachetime;
    protected $secondsOffset = 3600;
    protected $secondsDuration=0;
    protected $dataSources = array('value');
    protected $overlap = array();
    protected $order;
    protected $dataSourceNames=array();
    protected $colors;
    protected $rrdFormat;
    protected $scale = 1;
    protected $graphType;
    protected $files;
    protected $tinstances;
    protected $identifiers;
    protected $host='';
    protected $title='%s';
    protected $version=5;
    /**
     * @var RrdRenderer
     */
    protected $rrdRenderer;
    /**
     * @var \Core\Model\Plugin
     */
    protected $plugin;

    public function __construct($config = array()) {
        if(array_key_exists('datadir',$config)) $this->setDatadir($config['datadir']);
        if(array_key_exists('rrdtool',$config)) $this->setRrdtool($config['rrdtool']);
        if(array_key_exists('rrdtoolOptions',$config)) $this->setRrdtoolOptions($config['rrdtoolOptions']);
        if(array_key_exists('cachetime',$config)) $this->setCatchetime($config['cachetime']);
        //$this->rrdFiles();
        //$this->identifiers = $this->file2identifier($this->files);
        /*$this->width = GET('x');
        if (empty($this->width)) $this->width = $config['width'];
        $this->height = GET('y');
        if (empty($this->height)) $this->height = $config['height'];
        $this->graph_type = GET('graph_type');
        if (empty($this->graphType)) $this->graphType = $config['graphType'];*/
        $this->rrdRenderer = new RrdRenderer();
        $this->rrdRenderer->setSmooth($config['smooth']);
    }

    /**
     * @param $datadir string
     * @return AbstractGraph
     */
    public function setDatadir($datadir)
    {
        if(is_dir($datadir))
        {
            $this->datadir = $datadir;
        }
        return $this;
    }

    /**
     * @param $path string
     * @return AbstractGraph
     */
    public function setRrdtool($path)
    {
        $this->rrdRenderer->setRrdtool($path);
        return $this;
    }

    /**
     * @param $value string
     * @return AbstractGraph
     */
    public function setRrdtoolOptions($value)
    {
        $this->rrdRenderer->setRrdtool($value);
        return $this;
    }

    /**
     * @return RrdRenderer
     */
    public function getRrdRender()
    {
        $this->initRrdRenderer();
        return $this->rrdRenderer;
    }

    /**
     * @return AbstractGraph
     */
    public function initRrdRenderer()
    {
        $this->generateGraphDefinition();
        $this->rrdRenderer->resetDefinition();
        $this->findRrdFilesAndInstances();
        $this->genGraphDefinition();
        $this->rrdRenderer->setTitle(sprintf($this->title,$this->host));
        return $this;
    }

    /**
     * @param $w integer
     * @return AbstractGraph
     */
    public function setWidth($w)
    {
        $this->rrdRenderer->setWidth($w);
        return $this;
    }

    /**
     * @param $h
     * @return AbstractGraph
     */
    public function setHeight($h)
    {
        $this->rrdRenderer->setHeight($h);
        return $this;
    }
    /**
     * @param $t integer
     * @return AbstractGraph
     */
    public function setCachetime($t)
    {
        $t=intval($t);
        $this->cachetime=$t;
        return $this;
    }

    /**
     * @param $t string
     * @return AbstractGraph
     */
    public function setGraphtype($t)
    {
        $t = (string)$t;
        $this->graphType = $t;
        return $this;
    }

    /**
     * @param \Core\Model\Plugin $p
     * @return AbstractGraph
     */
    public function setPlugin(Plugin $p)
    {
        $this->plugin = $p;
        return $this;
    }

    /**
     * @param $t
     * @return AbstractGraph
     */
    public function setType($t)
    {
        $this->rrdRenderer->setType($t);
        return $this;
    }
    /**
     * @param $h
     * @return AbstractGraph
     */
    public function setHost($h)
    {
        if(preg_match('/^[a-zA-Z0-9._-]+$/',$h))
        {
            $this->host = $h;
        }
        return $this;
    }
    public function setVersion($v)
    {
        $v = abs(intval($v));
        if($v>3)
        {
            $this->version = $v;
        }
        return $this;
    }
    protected abstract function generateGraphDefinition();

    protected function rainbowColors() {
        $c = 0;
        $sources = count($this->rrdGetSources());
        foreach ($this->rrdGetSources() as $ds) {
            # hue (saturnation=1, value=1)
            $h = $sources > 1 ? 360 - ($c * (330/($sources-1))) : 360;

            $h = ($h %= 360) / 60;
            $f = $h - floor($h);
            $q[0] = $q[1] = 0;
            $q[2] = 1*(1-1*(1-$f));
            $q[3] = $q[4] = 1;
            $q[5] = 1*(1-1*$f);

            $hex = '';
            foreach(array(4,2,0) as $j) {
                $hex .= sprintf('%02x', $q[(floor($h)+$j)%6] * 255);
            }
            $this->colors[$ds] = $hex;
            $c++;
        }
    }

    /**
     * @param $fgc
     * @param string $bgc
     * @param float $percent
     * @return string
     */
    protected function getFadedColor($fgc, $bgc='ffffff', $percent=0.25) {
        $fgc = $this->rrdRenderer->validateColor($fgc);
        if (!is_numeric($percent))
            $percent=0.25;

        $rgb = array('r', 'g', 'b');

        $fg['r'] = hexdec(substr($fgc,0,2));
        $fg['g'] = hexdec(substr($fgc,2,2));
        $fg['b'] = hexdec(substr($fgc,4,2));
        $bg['r'] = hexdec(substr($bgc,0,2));
        $bg['g'] = hexdec(substr($bgc,2,2));
        $bg['b'] = hexdec(substr($bgc,4,2));

        foreach ($rgb as $pri) {
            $c[$pri] = dechex(round($percent * $fg[$pri]) + ((1.0 - $percent) * $bg[$pri]));
            if ($c[$pri] == '0')
                $c[$pri] = '00';
        }

        return $c['r'].$c['g'].$c['b'];
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function rrdEscape($value) {
        if ($this->graphType == 'canvas') {
            # http://oss.oetiker.ch/rrdtool/doc/rrdgraph_graph.en.html#IEscaping_the_colon
            return str_replace(':', '\:', $value);
        } else {
            # php needs it double escaped to execute rrdtool correctly
            return str_replace(':', '\\\:', $value);
        }
    }

    /**
     * @param $file
     * @return mixed
     */
    protected function parseFilename($file) {
        if ($this->graphType == 'canvas') {
            $file = DIR_WEBROOT.'/rrd.php/' . str_replace($this->datadir . '/', '', $file);
            # rawurlencode all but /
            $file = str_replace('%2F', '/', rawurlencode($file));
        } else {
            # escape characters
            $file = str_replace(array(' ', '(', ')'), array('\ ', '\(', '\)'), $file);
        }
        return $this->rrdEscape($file);
    }
    protected function findRrdFilesAndInstances() {
        $files = $this->findRrdFiles();

        foreach($files as $filename) {
            $basename=basename($filename,'.rrd');
            $instance = strpos($basename,'-')
                ? substr($basename, strpos($basename,'-') + 1)
                : 'value';

            $this->tinstances[] = $instance;
            $this->files[$instance] = $filename;
        }
        if ($this->tinstances) { sort($this->tinstances); }
        if ($this->files) { ksort($this->files); }
    }

    protected function findRrdFiles() {
        $identifier = sprintf('%s/%s%s%s/%s%s%s',
            $this->host,
            $this->plugin->plugin,
            strlen($this->plugin->pluginCategory) ? '-'.$this->plugin->pluginCategory : '',
            strlen($this->plugin->pluginInstance) ? '-'.$this->plugin->pluginInstance : '',
            $this->plugin->type,
            strlen($this->plugin->typeCategory) ? '-'.$this->plugin->typeCategory : '',
            (!strlen($this->plugin->typeInstance) && strlen($this->plugin->typeInstance)) ? '-'.$this->plugin->typeInstance.'' : ''
        );

        $wildcard = strlen($this->plugin->typeInstance) ? '.' : '[-.]*';
        /*echo "id:". $identifier."\n";
        echo "wildcard:". $wildcard."\n";*/
        $files = glob($this->datadir. '/'.$identifier.$wildcard.'rrd');
        return $files;
    }

    protected function file2identifier($files) {
        if ($files) {
            foreach($files as $key => $file) {
                if (is_file($file)) {
                    $files[$key] = preg_replace("#^$this->datadir/#u", '', $files[$key]);
                    $files[$key] = preg_replace('#\.rrd$#', '', $files[$key]);
                }
            }
        }
        return $files;
    }

    protected function getRrdSources() {
        # is the source spread over multiple files?
        if (is_array($this->files) && count($this->files)>1) {
            # and must it be ordered?
            if (is_array($this->order)) {
                $this->tinstances = array_merge(array_intersect($this->order, $this->tinstances));
            }
            # use tinstances as sources
            if(is_array($this->dataSources) && count($this->dataSources)>1) {
                $sources = array();
                foreach($this->tinstances as $f) {
                    foreach($this->dataSources as $s) {
                        $sources[] = $f . '-' . $s;
                    }
                }
            }
            else {
                $sources = $this->tinstances;
            }
        }
        # or one file with multiple data_sources
        else {
            if(is_array($this->dataSources) && count($this->dataSources)==1 && in_array('value', $this->dataSources)) {
                # use tinstances as sources
                $sources = $this->tinstances;
            } else {
                # use data_sources as sources
                $sources = $this->dataSources;
            }
        }

        $this->parseDataSourceNames($sources);
        return $sources;
    }

    protected function parseDataSourceNames($sources) {
        # fill ds_names if not defined by plugin
        if (!is_array($this->dataSourceNames)) {
            $this->dataSourceNames = array_combine($sources, $sources);
        }
    }

    public function addDataSource($source, $sourceName, $color=null)
    {
        $this->dataSources[] = $source;
        $this->dataSourceNames[] = $sourceName;
        if($color) $this->colors[] = $color;
        return $this;
    }

    public function addOverlap($source1,$source2)
    {
        $this->overlap[] = array(array_search($source1,$this->dataSources),array_search($source2,$this->dataSources));
    }

    public function genGraphDefinition() {
        $sources = $this->getRrdSources();

        $i=0;
        /*echo "<pre>";
        echo "TInstances\n";
        var_dump($this->tinstances);
        echo "\nDataSources\n";
        var_dump($this->dataSources);
        echo "\nSources\n";
        var_dump($sources);
        */
        if ($this->scale) {
            foreach ($this->tinstances as $tinstance) {
                foreach ($this->dataSources as $ds) {
                    $formatedSource=$this->formatSource($sources[$i]);
                    //echo "Source($i): $formatedSource \n";
                    $this->rrdRenderer->addDataSourceScaledDefinition('min_'.$formatedSource,$this->parseFilename($this->files[$tinstance]),$ds,'MIN',$this->scale)
                        ->addDataSourceScaledDefinition('avg_'.$formatedSource,$this->parseFilename($this->files[$tinstance]),$ds,'AVERAGE',$this->scale)
                        ->addDataSourceScaledDefinition('max_'.$formatedSource,$this->parseFilename($this->files[$tinstance]),$ds,'MAX',$this->scale);
                    $i++;
                }
            }
        }
        else {
            foreach ($this->tinstances as $tinstance) {
                foreach ($this->dataSources as $ds) {
                    $formatedSource=$this->formatSource($sources[$i]);
                    $this->rrdRenderer->addDataSourceDefinition('min_'.$formatedSource,$this->parseFilename($this->files[$tinstance]), $ds,'MIN')
                        ->addDataSourceDefinition('avg_'.$formatedSource,$this->parseFilename($this->files[$tinstance]), $ds,'AVERAGE')
                        ->addDataSourceDefinition('max_'.$formatedSource,$this->parseFilename($this->files[$tinstance]), $ds,'MAX');
                    $i++;
                }
            }
        }
        //exit();

        if(count($this->files)<=1) {
            $c = 0;
            foreach ($sources as $source) {
                $color = is_array($this->colors) ? (isset($this->colors[$source])?$this->colors[$source]:$this->colors[$c++]): $this->colors;
                $this->rrdRenderer->addArea(sprintf('max_%x', crc32($source)),$this->getFadedColor($color))
                    ->addArea(sprintf('min_%x', crc32($source)),$this->getFadedColor($color));
                break; # only 1 area to draw
            }
        }

        $lengths = array_map('strlen', $sources);
        $max_src = max($lengths);
        $max_src = $max_src > self::MAX_LEGEND_LENGTH ? self::MAX_LEGEND_LENGTH : $max_src;

        $lengths = array_map('strlen', $this->dataSourceNames);
        $max_ds = max($lengths);
        $max_ds = $max_ds > self::MAX_LEGEND_LENGTH ? self::MAX_LEGEND_LENGTH : $max_ds;

        $c = 0;
        foreach ($sources as $source) {
            if (empty($this->dataSourceNames[$source])) {
                //$dsname =  sprintf('%1$-'.$max_src.'s', $source);
                $dsname = sprintf('%1$-'.$max_src.'s',preg_replace('/\s+?(\S+)?$/u', '', mb_substr($source, 0, $max_src)));
            } else {
                //$dsname = sprintf('%1$-'.$max_ds.'s', $this->ds_names[$source]);
                $dsname = sprintf('%1$-'.$max_ds.'s',preg_replace('/\s+?(\S+)?$/u', '', mb_substr($this->dataSourceNames[$source], 0, $max_ds)));
            }
            //$dsname = empty($this->ds_names[$source]) ? $source : $this->ds_names[$source];
            $color = is_array($this->colors) ? (isset($this->colors[$source])?$this->colors[$source]:$this->colors[$c++]): $this->colors;

            $formatedSource=sprintf('%x', crc32($source));
            $this->rrdRenderer->addLine('avg_'.$formatedSource,$color,$this->rrdEscape(ucfirst(str_replace('_', ' ',$dsname))))
                ->addGprint('min_'.$formatedSource,$this->rrdFormat.' Min','MIN')
                ->addGprint('avg_'.$formatedSource,$this->rrdFormat.' Avg','AVERAGE')
                ->addGprint('max_'.$formatedSource,$this->rrdFormat.' Min','MAX')
                ->addGprint('avg_'.$formatedSource,$this->rrdFormat.' Last\\l','LAST');
        }
    }

    protected function formatSource($s)
    {
        return sprintf('%x', crc32($s));
    }
}

?>
