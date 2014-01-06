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
            'Core\Model\GroupTable' =>  function($sm) {
                $tableGateway = $sm->get('GroupTableGateway');
                $table = new \Core\Model\GroupTable($tableGateway);
                return $table;
            },
            'GroupTableGateway' => function ($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new \Core\Model\Group());
                return new TableGateway('auth_group', $dbAdapter, null, $resultSetPrototype);
            },
        )
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController'
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
