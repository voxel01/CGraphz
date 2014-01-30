<?php
namespace Web\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DashboardController extends AbstractActionController
{
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
