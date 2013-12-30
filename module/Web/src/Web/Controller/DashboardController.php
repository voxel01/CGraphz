<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Web\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DashboardController extends AbstractActionController
{
    public function preDispatch()
    {
        $this->view->headLink()
            ->prependStylesheet($this->basePath() . '/lib/css/demo_table.css','screen')
            ->prependStylesheet($this->basePath() . '/lib/multiselect/css/common.css','screen')
            ->prependStylesheet($this->basePath() . '/lib/multiselect/css/ui.multiselect.css','screen')
            ;
        $this->view->headScript()
            ->prependFile($this->view->basePath() . '/lib/dyn_js.php')
            ->prependFile($this->view->basePath() . '/lib/jquery.dataTables.min.js')
            ->prependFile($this->view->basePath() . '/lib/multiselect/js/jquery.tmpl.1.1.1.js')
            ->prependFile($this->view->basePath() . '/lib/multiselect/js/ui.multiselect.js')
            ;
    }
    public function viewAction()
    {
        $user = $this->getServiceLocator()->get('Core\Model\UserIdentity');
        $project = $this->params('project',0);
        $server = $this->params('server',0);
        $config = new \Zend\Config\Config($this->getServiceLocator()->get('Config'));

        $projects = $user->getProjects();
        $params = array('projects' => $projects);
        //var_dump($servers);exit;
        if($project && array_key_exists($project,$projects))
        {
            $servers = $projects[$project]->getServer();
            $params['project'] = $projects[$project];

            if($server && array_key_exists($server,$servers))
            {
                $params['server'] = $servers[$server];
                $pluginFilter = new \Core\Model\PluginFilter();
                $pluginFilter->setConfig($config->collectd);
                $pluginFilter->setFilter($user->getFilter());
                $pluginFilter->setServer($servers[$server]);

                $plugs = $pluginFilter->getPlugins();
                $plugins = array();
                foreach($plugs as $p)
                {
                    $plugins[$p->plugin][] = $p;
                }
                $params['plugins'] = $plugins;
            }
        }
        return new ViewModel($params);
    }
}
