<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */


use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

return array(
    'router' => array(
        'routes' => array(
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
        'initializers' => array(
            function ($instance, $sm) {
                if ($instance instanceof \Zend\Db\Adapter\AdapterAwareInterface) {
                    $instance->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
                }
            }
        ),
        'factories' => array(

            'Core\Model\ModuleTable' =>  function($sm) {
                $tableGateway = $sm->get('ModuleTableGateway');
                $table = new \Core\Model\ModuleTable($tableGateway);
                return $table;
            },
            'ModuleTableGateway' => function ($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new \Core\Model\Module());
                return new TableGateway('perm_module', $dbAdapter, null, $resultSetPrototype);
            },
            'Core\Model\Group' =>  function($sm) {
                $g = new Core\Model\Group();
                return $g;
            },
            'Core\Model\GroupTable' =>  function($sm) {
                $tableGateway = $sm->get('GroupTableGateway');
                $table = new \Core\Model\GroupTable($tableGateway);
                return $table;
            },
            'GroupTableGateway' => function ($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype($sm->get('Core\Model\Group'));
                return new TableGateway('auth_group', $dbAdapter, null, $resultSetPrototype);
            },

            'Core\Model\User' => function ($sm) {
                $user = new Core\Model\User();
                $user->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
                return $user;
            },
            'Core\Model\UserTable' =>  function($sm) {
                $tableGateway = $sm->get('UserTableGateway');
                $table = new \Core\Model\UserTable($tableGateway);
                return $table;
            },
            'UserTableGateway' => function ($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype($sm->get('Core\Model\User'));
                return new TableGateway('auth_user', $dbAdapter, null, $resultSetPrototype);
            },
            'Core\Model\Project' => function ($sm) {
                $p = new Core\Model\Project();
                $p->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
                return $p;
            },
            'Core\Model\UserIdentity' => function ($sm) {
                $as = $sm->get('Web\Auth\Service');
                if ($as->hasIdentity() === true) {
                    $user = $as->getIdentity();
                } else {
                    $user = new Core\Model\User();
                }
                $user->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
                return $user;
            },
            'Core\Model\PluginFilter' => function ($sm) {
                $filter = new Core\Model\PluginFilter();
                $config = new \Zend\Config\Config($this->getServiceLocator()->get('Config'));
                $filter->setConfig($config->collectd);
                return $filter;
            },
            'Core\Model\Filter' => function ($sm) {
                $filter = new Core\Model\Filter();
                return $filter;
            },
            'Core\Model\FilterTable' =>  function($sm) {
                $tableGateway = $sm->get('FilterTableGateway');
                $table = new \Core\Model\FilterTable($tableGateway);
                return $table;
            },
            'FilterTableGateway' => function ($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype($sm->get('Core\Model\Filter'));
                return new TableGateway('config_plugin_filter', $dbAdapter, null, $resultSetPrototype);
            },
            'Core\Model\Project' => function ($sm) {
                $filter = new Core\Model\Project();
                return $filter;
            },
            'Core\Model\ProjectTable' =>  function($sm) {
                $tableGateway = $sm->get('ProjectTableGateway');
                $table = new \Core\Model\ProjectTable($tableGateway);
                return $table;
            },
            'ProjectTableGateway' => function ($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype($sm->get('Core\Model\Project'));
                return new TableGateway('config_project', $dbAdapter, null, $resultSetPrototype);
            },
        )
    ),
    'controllers' => array(
        'invokables' => array(
        ),
    ),
    'view_manager' => array(
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
