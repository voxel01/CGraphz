<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'graph-view' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/graph/:host/:plugin/:pluginCategory/:pluginInstance/:type/:typeCategory/:typeInstance[/:secondsDuration[/:secondsOffset]]/graph.png',
                    'constraints' => array(
                        'host' => '[a-zA-Z0-9._-]+',
                        'plugin' => '[a-zA-Z0-9._-]+',
                        'pluginCategory' => '[a-zA-Z0-9._-]+',
                        'pluginInstance' => '[a-zA-Z0-9._-]+',
                        'type' => '[a-zA-Z0-9._-]+',
                        'typeCategory' => '[a-zA-Z0-9._-]+',
                        'typeInstance' => '[a-zA-Z0-9._-]+',
                        'secondsDuration' => '[0-9]+',
                        'secondsOffset' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Graph\Controller\Graph',
                        'action' => 'view',
                        'secondsOffset' => 0,
                        'secondsDuration' => 3600,
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(

        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Graph\Controller\Graph' => 'Graph\Controller\GraphController'
        ),
    ),
);
