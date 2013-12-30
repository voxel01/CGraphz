<?php
namespace Graph\Model\Graph;

class RrdRenderer
{
    const TYPE_PNG = 'png';
    const TYPE_SVG = 'svg';
    const TYPE_DEBUG = 'debug';
    const TYPE_CANVAS = 'canvas';

    const TYPE_DS_DEF = 'DEF';
    const TYPE_DS_CDEF = 'CDEF';

    const TYPE_DRAW_AREA = 'AREA';
    const TYPE_DRAW_LINE = 'LINE';
    const TYPE_DRAW_GPRINT = 'GPRINT';
    const TYPE_DRAW_PRINT = 'PRINT';

    const MAX_WIDTH=2048;
    const MAX_HEIGHT=2048;

    protected $definition= array();
    protected $draw= array();
    protected $rrdtool='/usr/bin/rrdtool';
    protected $rrdOption='';
    protected $type=self::TYPE_DEBUG;
    protected $width = 400;
    protected $height = 175;
    protected $smooth = false;

    protected $title = '';
    protected $titleVertical='';

    protected $secondsOffset=0;
    protected $secondsDuration=3600;

    protected $definedVarnames = array();

    /**
     * @param $def
     * @return RrdRenderer
     */
    public function addDefinition($def)
    {
        $this->definition[] = $def;
        return $this;
    }
    public function addDraw($draw)
    {
        $this->draw[] = $draw;
        return $this;
    }

    /**
     * @param $definition
     * @param $color
     * @return RrdRenderer
     */
    public function addArea($definition,$color)
    {
        $this->addDraw(sprintf(self::TYPE_DRAW_AREA.':%s#%s', $definition, $this->validateColor($color)));
        return $this;
    }

    /**
     * @param $definition
     * @param $filename
     * @param $dataSource
     * @param $type
     * @return RrdRenderer
     */
    public function addDataSourceDefinition($varname,$rrdfile,$dataSource,$consolidationFunction)
    {
        $this->definedVarnames[] = $varname;
        $this->addDefinition(sprintf(self::TYPE_DS_DEF.':%s=%s:%s:%s', $varname,$rrdfile,$dataSource,$consolidationFunction));
        return $this;
    }

    /**
     * @param $definition
     * @param $definitonTo
     * @param $scale
     * @return RrdRenderer
     */
    public function addDataSourceCalculatedDefinition($varname,$expression)
    {
        $this->definedVarnames[] = $varname;
        $this->addDefinition(sprintf(self::TYPE_DS_CDEF.':%s=%s', $varname,$expression));
        return $this;
    }

    /**
     * @param $varname
     * @param $rrdfile
     * @param $dataSource
     * @param $consolidationFunction
     * @param $scale
     */
    public function addDataSourceScaledDefinition($varname,$rrdfile,$dataSource, $consolidationFunction, $scale)
    {
        $this->addDataSourceDefinition($varname.'_raw',$rrdfile,$dataSource,$consolidationFunction)
            ->addDataSourceCalculatedDefinition($varname,$varname.'_raw,'.$scale.',*');
        return $this;
    }

    /**
     * @param $varname
     * @param $color
     * @param $name
     * @return RrdRenderer
     */
    public function addLine($varname,$color,$name,$width=1)
    {
        if(in_array($varname,$this->definedVarnames))
        {
            $this->addDraw(sprintf(self::TYPE_DRAW_LINE.intval($width).':%s#%s:"%s"',$varname,$this->validateColor($color),$name));
        }
        return $this;
    }

    /**
     * @param $varname
     * @param $format
     * @param string $consolidationFunction
     */
    public function addGprint($varname,$format,$consolidationFunction='')
    {
        if(in_array($varname,$this->definedVarnames))
        {
            if($consolidationFunction) $consolidationFunction = ':'.$consolidationFunction;
            $this->addDraw(sprintf(self::TYPE_DRAW_GPRINT.':%s%s:"%s"',$varname,$consolidationFunction,$format));
        }
        return $this;
    }

    /**
     * @param $path
     * @return RrdRenderer
     */
    public function setRrdtool($path)
    {
        if(file_exists($path) && is_executable($path))
        {
            $this->rrdtool = $path;
        }
        return $this;
    }

    /**
     * @param $value
     * @return RrdRenderer
     */
    public function setRrdtoolOptions($value)
    {
        $value= (string) $value;
        if($value)
        {
            $this->rrdtoolOptions = $value;
        }
        return $this;
    }

    /**
     * @return RrdRenderer
     */
    public function resetDraw()
    {
        $this->draw = array();
        return $this;
    }

    /**
     * @return RrdRenderer
     */
    public function resetDefinition()
    {
        $this->resetDraw();
        $this->definition = array();
        return $this;
    }
    /**
     * @param $t
     * @return RrdRenderer
     */
    public function setType($t)
    {
        if(
            $t == self::TYPE_DEBUG ||
            $t == self::TYPE_PNG ||
            $t == self::TYPE_SVG ||
            $t == self::TYPE_CANVAS
        )
        {
            $this->type=$t;
        }
        return $this;
    }
    /**
     * @param $w
     * @return RrdRenderer
     */
    public function setWidth($w)
    {
        $w=intval($w);
        if($w>0 && $w < self::MAX_WIDTH)
        {
            $this->width = $w;
        }
        return $this;
    }
    /**
     * @param $h
     * @return RrdRenderer
     */
    public function setHeight($h)
    {
        $h=intval($h);
        if($h>0 && $h < self::MAX_HEIGHT)
        {
            $this->height = $h;
        }
        return $this;
    }

    /**
     * @param $s
     * @return RrdRenderer
     */
    public function setSmooth($s)
    {
        $s = (bool)$s;
        $this->smooth = $s;
        return $this;
    }
    /**
     * @param $s
     * @return int
     */
    protected function checkSeconds($s)
    {
        $s = abs(intval($s));
        return $s;
    }
    /**
     * @param $s
     * @return RrdRenderer
     */
    public function setSecondsOffset($s)
    {
        $this->secondsOffset = $this->checkSeconds($s);
        return $this;
    }
    /**
     * @param $s
     * @return RrdRenderer
     */
    public function setSecondsDuration($s)
    {
        $this->secondsDuration = $this->checkSeconds($s);
        return $this;
    }
    /**
     * @param $s
     * @return RrdRenderer
     */
    public function setTitle($s)
    {
        $this->title = (string)$s;
        return $this;
    }

    /**
     * @param $s
     * @return RrdRenderer
     */
    public function setTitleVertical($s)
    {
        $this->titleVertical = (string)$s;
        return $this;
    }

    /**
     * @param $color
     * @return string
     */
    public function validateColor($color) {
        if (!preg_match('/^[0-9a-f]{6}$/', $color))
            return '000000';
        else
            return $color;
    }
    public function getType()
    {
        return $this->type;
    }
    /**
     * @return array
     */
    protected function getGraphDefinitionHeader()
    {

        if ($this->type != 'canvas') {
            $rrdgraph[] = $this->rrdtool;
            switch($this->type) {
                case 'png':
                    $rrdgraph[] = 'graph - -a PNG';
                    break;
                case 'svg':
                    $rrdgraph[] = 'graph - -a SVG -R light --font DEFAULT:7';
                    break;
                default:
                    $this->type = 'debug';
                    return;
            }
        }
        if ($this->rrdOption != '')
            $rrdgraph[] = $this->rrdOption;
        if ($this->smooth)
            $rrdgraph[] = '-E';

        # In the case of SVG files, we will want to have rrdgraph generate it at a higher "resolution"
        # Then use a width= attribute in the <img> tag to scale it to the desired width
        if ($this->type == 'svg') {
            $svg_factor = 1;
            $rrdgraph[] = sprintf('-w %d', ($this->width*$svg_factor));
            $rrdgraph[] = sprintf('-h %d', ($this->height*$svg_factor));
        } else {
            $rrdgraph[] = sprintf('-w %d', $this->width);
            $rrdgraph[] = sprintf('-h %d', $this->height);
        }

        $rrdgraph[] = '-l 0';
        $rrdgraph[] = sprintf('-t "%s"', $this->title);
        if ($this->titleVertical)
            $rrdgraph[] = sprintf('-v "%s"', $this->titleVertical);
        if ($this->secondsOffset == 0) {
            $rrdgraph[] = sprintf('-s e-%d', $this->secondsDuration);
        } else {
            $rrdgraph[] = sprintf('-s %s -e now-%s seconds', $this->secondsDuration, $this->secondsOffset);
        }

        return $rrdgraph;
    }

    protected function getFullGraphDefinition()
    {
        return array_merge($this->getGraphDefinitionHeader(),$this->definition,$this->draw);
    }

    public function renderGraph($debug=false) {
        $graphdata = $this->getFullGraphDefinition();

        $style = $debug !== false ? $debug : $this->type;
        switch ($style) {
            case 'canvas':
                array_unshift(sprintf('<canvas id="%s" class="rrd">', sha1(serialize($graphdata))),$graphdata);
                $graphdata[] = '</canvas>';
                foreach ($graphdata as $k => $d) {
                    $graphdata[$k] = sprintf("%s\n", $d);
                }
                return implode($graphdata);
                break;
            case 'cmd':
            case 'debug':
            case 1:
                return var_export($graphdata,true);
                break;
            case 'svg':
            case 'png':
            default:
                $graphdata = implode(' ',$graphdata);
                error_log($graphdata);
                ob_start();
                passthru($graphdata);
                $content = ob_get_clean();
                ob_end_clean();
                return $content;
                break;
        }
    }

    public function setHeaders(\Zend\Http\Headers $header)
    {
        switch($this->type)
        {
            case 'cmd':
            case 'canvas':
            case 'debug':
            case 1:
                break;
            case 'svg':

                $header->addHeaderLine('Content-Transfer-Encoding', 'binary')
                ->addHeaderLine('Content-Type', 'image/svg+xml');
                break;
            case 'png':
            default:
                $header->addHeaderLine('Content-Transfer-Encoding', 'binary')
                ->addHeaderLine('Content-Type', 'image/png');
                break;
        }
    }
}
