<?php
namespace Web\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ConfigController extends AbstractActionController
{
    const FUNCT_SHOW = 'show';
    const FUNCT_ADD = 'add';
    const FUNCT_EDIT = 'edit';
    const FUNCT_DEL = 'delete';
    const FUNCT_ROLE = 'role';
    const FUNCT_PROJECT = 'project';
    const FUNCT_ENV = 'environment';

    public function projectAction()
    {

    }

    public function serverAction()
    {
        $function = $this->params('function',self::FUNCT_SHOW);
        $params = array();
        $request = $this->getRequest();

        $serverTable = $this->getServiceLocator()->get('Core\Model\ServerTable');

        if( $function == self::FUNCT_SHOW)
        {
            $config = new \Zend\Config\Config($this->getServiceLocator()->get('Config'));
            //Get Serverlist
            $params['serverNew'] = $serverTable->findNew($config->collectd->datadir);
        }
        elseif($function==self::FUNCT_ADD)
        {
            $config = new \Zend\Config\Config($this->getServiceLocator()->get('Config'));
            $basedir = $config->collectd->datadir;
            $desc = $this->params()->fromPost('f_server_description','');
            $version = intval($this->params()->fromPost('f_server_version',5));
            foreach($this->params()->fromPost('f_server_name',array()) as $server)
            {
                $server = base64_decode($server);
                if(is_dir($basedir.DIRECTORY_SEPARATOR.$server))
                {
                    $s = $this->getServiceLocator()->get('Core\Model\Server');
                    $s->server_name = $server;
                    $s->server_description = $desc;
                    $s->collectd_version = $version;
                    $serverTable->saveServer($s);
                }
            }
            $s = $serverTable->getServerbyName($server);
            return $this->redirect()->toRoute('config-server',array('id_config_server'=>$s->id_config_server,'function'=>self::FUNCT_EDIT));
        }
        elseif($function==self::FUNCT_EDIT || $function == self::FUNCT_ROLE || $function == self::FUNCT_PROJECT || $function == self::FUNCT_ENV || ($function==self::FUNCT_DEL)&&$request->isPost())
        {
            try{
                $server = $serverTable->getServer(intval($this->params('id_config_server',0)));
            }
            catch(\Exception $e)
            {
                $server = false;
            }
            $roleTable = $this->getServiceLocator()->get('Core\Model\RoleTable');
            $projectTable = $this->getServiceLocator()->get('Core\Model\ProjectTable');
            $environmentTable = $this->getServiceLocator()->get('Core\Model\EnvironmentTable');
            if(!$server)
            {
                return $this->redirect()->toRoute('config-server',array('function',self::FUNCT_SHOW));
            }
            if($function==self::FUNCT_DEL)
            {
                $serverTable->deleteServer($server->id_config_server);
                return $this->redirect()->toRoute('config-server',array('function',self::FUNCT_SHOW));
            }
            elseif($function==self::FUNCT_EDIT && $request->isPost())
            {
                $server->server_description = $this->params()->fromPost('f_server_description','');
                $server->collectd_version = intval($this->params()->fromPost('f_server_version',5));
                $serverTable->saveServer($server);
            }
            elseif($function == self::FUNCT_ROLE)
            {
                $this->saveEdit($request,'role',$server);
            }
            elseif($function == self::FUNCT_PROJECT)
            {
                $this->saveEdit($request,'project',$server);
            }
            elseif($function == self::FUNCT_ENV)
            {
                $this->saveEdit($request,'environment',$server);
            }
            $params['projects'] = $projectTable->getProjectsToServer($server);
            $params['environments'] = $environmentTable->getEnvironmentsToServer($server);
            $params['roles'] = $roleTable->getRolesToServer($server);
            $params['server'] = $server;
        }
        else{
            return $this->redirect()->toRoute('config-server',array('function',self::FUNCT_SHOW));
        }
        $params['serverList'] = $serverTable->fetchAll();
        return new ViewModel($params);
    }

    public function roleAction()
    {

    }

    public function environmentAction()
    {

    }

    public function filterAction()
    {
        $function = $this->params('function',self::FUNCT_SHOW);
        $params = array();
        $request = $this->getRequest();
        $filterTable = $this->getServiceLocator()->get('Core\Model\FilterTable');

        if( $function == self::FUNCT_SHOW)
        {
            /* Nothing to do */
        }
        elseif($function==self::FUNCT_ADD && $request->isPost())
        {
            $f_desc = $this->params()->fromPost('f_plugin_filter_desc','');
            $f_plugin = $this->params()->fromPost('f_plugin_filter_p','');
            $f_plugin_instance = $this->params()->fromPost('f_plugin_filter_pi','');
            $f_type = $this->params()->fromPost('f_plugin_filter_t','');
            $f_type_instance = $this->params()->fromPost('f_plugin_filter_ti','');
            $f_order = $this->params()->fromPost('f_plugin_filter_plugin_order','');
            //@todo: Paramchecks
            $p = $this->getServiceLocator()->get('Core\Model\Filter');
            //$p = new \Core\Model\Filter();
            $p->plugin_filter_desc = $f_desc;
            $p->plugin = $f_plugin;
            $p->plugin_instance = $f_plugin_instance;
            $p->type = $f_type;
            $p->type_instance = $f_type_instance;
            $p->plugin_order = $f_order;
            $filterTable->saveFilter($p);
        }
        elseif($function==self::FUNCT_EDIT || $function==self::FUNCT_DEL)
        {

            try{
                $filter = $filterTable->getFilter(intval($this->params('id_config_plugin_filter',0)));
            }
            catch(\Exception $e)
            {
                $filter = false;
            }
            if(!$filter)
            {
                return $this->redirect()->toRoute('config-filter',array('function',self::FUNCT_SHOW));
            }

            if($function==self::FUNCT_DEL)
            {
                $filterTable->deleteFilter($filter->id_config_plugin_filter);
                return $this->redirect()->toRoute('config-filter',array('function',self::FUNCT_SHOW));
            }
            elseif($function==self::FUNCT_EDIT && $request->isPost())
            {
                //var_dump($filter);exit();
                $f_desc = $this->params()->fromPost('f_plugin_filter_desc','');
                $f_plugin = $this->params()->fromPost('f_plugin_filter_p','');
                $f_plugin_instance = $this->params()->fromPost('f_plugin_filter_pi','');
                $f_type = $this->params()->fromPost('f_plugin_filter_t','');
                $f_type_instance = $this->params()->fromPost('f_plugin_filter_ti','');
                $f_order = $this->params()->fromPost('f_plugin_filter_plugin_order','');
                //@todo: Paramchecks
                $filter->plugin_filter_desc = $f_desc;
                $filter->plugin = $f_plugin;
                $filter->plugin_instance = $f_plugin_instance;
                $filter->type = $f_type;
                $filter->type_instance = $f_type_instance;
                $filter->plugin_order = $f_order;
                $filterTable->saveFilter($filter);
            }
            $params['filter'] = $filter;
        }
        else{
            return $this->redirect()->toRoute('config-filter',array('function',self::FUNCT_SHOW));
        }

        $params['filterList'] = $filterTable->fetchAll();
        return new ViewModel($params);
    }

    public function dynamic_dashboardAction()
    {

    }


    protected function saveEdit($request, $type, $group)
    {
        $base = array_pop(explode('\\',get_class($group)));
        if($request->isPost()) //Maybe add something
        {
            if($this->params()->fromPost('f_submit_'.$type,false))
            {
                $table  = $this->getServiceLocator()->get('Core\Model\\'.ucfirst($type).'Table');
                $add = intval($this->params()->fromPost('f_id_'.$type,0));
                if($add)
                {
                    $func = 'add'.ucfirst($type).'to'.$base;
                    $table->$func($group, $add);
                }
            }
        }
        else
        {
            $table  = $this->getServiceLocator()->get('Core\Model\\'.ucfirst($type).'Table');
            $drop = intval($this->params()->fromQuery('delete'.$type,false));
            if($drop)
            {
                $func = 'drop'.ucfirst($type).'from'.$base;
                $table->$func($group, $drop);
            }
        }
    }
}
