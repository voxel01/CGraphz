<?php

return array(
    'router' => array(
        'routes' => array(
            'graph-view' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/graph/:host/:plugin/:pluginCategory/:pluginInstance/:type/:typeCategory/:typeInstance[/:secondsDuration[/:secondsOffset]]/graph.png',
                    'constraints' => array(
                        'host' => '[a-zA-Z0-9._-]+',
                        'plugin' => '[a-z=A-Z0-9-]+',
                        'pluginCategory' => '[a-z=A-Z0-9-]+',
                        'pluginInstance' => '[a-z=A-Z0-9-]+',
                        'type' => '[a-z=A-Z0-9-]+',
                        'typeCategory' => '[a-z=A-Z0-9-]+',
                        'typeInstance' => '[a-z=A-Z0-9-]+',
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
