<?php
namespace Graph\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Core\Model\Plugin;
use Graph\Model\Graph\RrdRenderer;

class GraphController extends AbstractActionController
{
    public function viewAction()
    {
        $config = new \Zend\Config\Config($this->getServiceLocator()->get('Config'));

        //@todo: Check if user is Allowed to view this graph
        $plugin = new Plugin();
        foreach(get_object_vars($plugin) as $key => $value)
        {
            $v = $this->params($key,'-');
            $plugin->$key = ($v !== '-')?base64_decode($v):'';
        }

        $classname = 'Graph\\Model\\Graph\\'.ucfirst($plugin->plugin);
        if($plugin->type)
        {
            $sub = explode('_',$plugin->type);
            $sub = array_map('ucfirst',$sub);
            $classname .= '\\'.implode('',$sub);
        }
        //$var = new \Graph\Model\Graph\Apache\Apache_connections();
        //var_dump($classname);exit();
        if(!class_exists($classname))
        {
            throw new \Exception('Graph not exists ('.$classname.')');
        }

        $graph = new $classname();
        if(!($graph instanceof \Graph\Model\Graph\AbstractGraph))
        {
            throw new \Exception('Invalid Graph definition');
        }

        $graph->setDatadir($config->collectd->datadir)
            ->setHeight(intval($this->params('height',$config->collectd->height)))
            ->setWidth(intval($this->params('width',$config->collectd->width)))
            ->setHost($this->params('host'))
            ->setPlugin($plugin)
            ->setType(RrdRenderer::TYPE_PNG);
        ;
        //@todo set version from information in database.

        if(isset($config->collectd->rrdtool))
        {
            $graph->setRrdtool($config->collectd->rrdtool);
        }
        $duration = abs(intval($this->params()->fromQuery('s')));
        if(!$duration)
        {
            $duration = abs(intval($this->params('secondsDuration',3600)));
        }
        $secondsOffset = abs(intval($this->params()->fromQuery('e')));
        if(!$secondsOffset)
        {
            $secondsOffset = abs(intval($this->params('secondsOffset',0)));
        }
        error_log('Duration: '.$duration);
        error_log('URI: '.$_SERVER['REQUEST_URI']);
        $rrdrenderer = $graph->getRrdRender();
        $rrdrenderer->setSecondsDuration($duration);
        $rrdrenderer->setSecondsOffset($secondsOffset);

        $response = $this->getResponse();

        $rrdrenderer->setHeaders($response->getHeaders());
        $content = $rrdrenderer->renderGraph();
        //var_dump($content);exit();
        $response->setContent($content);

        $response
            ->getHeaders()
            ->addHeaderLine('Content-Length', mb_strlen($content));

        return $response;
    }
}
