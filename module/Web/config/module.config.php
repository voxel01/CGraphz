<?php
use Zend\Session\SessionManager;
use Zend\Session\Container;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;

return array(
    'navigation' => array(
        'default' => array(
            array(
                'label' => 'Performance Analysis',
                'route' => 'dashboard-view',
                'pages' => array(
                    array(
                        'label' => 'Dashboard',
                        'route' => 'dashboard-view',
                        'resource' => 'mvc:dashboard-view',
                    ),
                    array(
                        'label' => 'Dynamic Dashboard',
                        'route' => 'dashboard-view',
                        'resource' => 'mvc:dashboard-view',
                    ),
                ),
            ),
            array(
                'label' => 'Small Admin',
                'route' => 'dashboard-view',
                'pages' => array(
                    array(
                        'label' => 'My Account',
                        'route' => 'dashboard-view',
                        'resource' => 'mvc:dashboard-view',
                    ),
                    array(
                        'label' => 'My Groups',
                        'route' => 'dashboard-view',
                        'resource' => 'mvc:dashboard-view',
                    ),
                    array(
                        'label' => 'New User',
                        'route' => 'dashboard-view',
                        'resource' => 'mvc:dashboard-view',
                    ),
                    array(
                        'label' => 'My Dynamic Dashboards',
                        'route' => 'dashboard-view',
                        'resource' => 'mvc:dashboard-view',
                    ),
                ),
            ),
                array(
                    'label' => 'Administration',
                    'route' => 'dashboard-view',
                    'pages' => array(
                        array(
                            'label' => 'Permissions',
                            'route' => 'dashboard-view',
                            'resource' => 'mvc:dashboard-view',
                            'pages' => array(
                                array(
                                    'label' => 'Modules',
                                    'route' => 'perm-module',
                                    'resource' => 'mvc:perm-module',
                                ),
                                array(
                                    'label' => 'Users',
                                    'route' => 'dashboard-view',
                                    'resource' => 'mvc:dashboard-view',
                                ),
                                array(
                                    'label' => 'Groups',
                                    'route' => 'perm-group',
                                    'resource' => 'mvc:auth-group',
                                ),
                            ),
                        ),
                        array(
                            'label' => 'Settings',
                            'route' => 'dashboard-view',
                            'resource' => 'mvc:dashboard-view',
                            'pages' => array(
                                array(
                                    'label' => 'Servers',
                                    'route' => 'dashboard-view',
                                    'resource' => 'mvc:dashboard-view',
                                ),
                                array(
                                    'label' => 'Projects',
                                    'route' => 'dashboard-view',
                                    'resource' => 'mvc:dashboard-view',
                                ),
                                array(
                                    'label' => 'Filters',
                                    'route' => 'dashboard-view',
                                    'resource' => 'mvc:dashboard-view',
                                ),
                                array(
                                    'label' => 'Dynamic Dashboards',
                                    'route' => 'dashboard-view',
                                    'resource' => 'mvc:dashboard-view',
                                ),
                                array(
                                    'label' => 'Roles',
                                    'route' => 'dashboard-view',
                                    'resource' => 'mvc:dashboard-view',
                                ),
                                array(
                                    'label' => 'Environments',
                                    'route' => 'dashboard-view',
                                    'resource' => 'mvc:dashboard-view',
                                ),
                            ),
                    ),
                ),
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Web\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
            'auth-login' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        'controller' => 'Web\Controller\Auth',
                        'action' => 'login',
                    ),
                ),
            ),
            'dashboard-view' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/dashboard[/:project[/:server]]',
                    'defaults' => array(
                        'controller' => 'Web\Controller\Dashboard',
                        'action' => 'view',
                        'project' => '0',
                        'server' => '0',
                    ),
                ),
            ),
            'perm-module' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/perm/module[/:id_perm_module]',
                    'constraints' => array(
                        'id_perm_module' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Web\Controller\Perm',
                        'action' => 'module',
                        'id_perm_module' => '0',
                    ),
                ),
            ),
            'perm-group' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/perm/group[/:id_auth_group[/:function]]',
                    'constraints' => array(
                        'id_auth_group' => '[0-9]+',
                        'function' => '(show|edit|add|user|project|filter|module)',
                    ),
                    'defaults' => array(
                        'controller' => 'Web\Controller\Perm',
                        'action' => 'group',
                        'id_auth_group' => '0',
                        'function' => 'show',
                    ),
                ),
            ),
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
            'navigation' => 'Web\Navigation\Factory',
            //'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'Zend\Session\SessionManager' => function ($sm) {
                $config = $sm->get('config');
                if (isset($config['session'])) {
                    $session = $config['session'];

                    $sessionConfig = null;
                    if (isset($session['config'])) {
                        $class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                        $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                        $sessionConfig = new $class();
                        $sessionConfig->setOptions($options);
                    }

                    $sessionStorage = null;
                    if (isset($session['storage'])) {
                        $class = $session['storage'];
                        $sessionStorage = new $class();
                    }

                    $sessionSaveHandler = null;
                    if (isset($session['save_handler'])) {
                        // class should be fetched from service manager since it will require constructor arguments
                        $sessionSaveHandler = $sm->get($session['save_handler']);
                    }

                    $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                    if (isset($session['validators'])) {
                        $chain = $sessionManager->getValidatorChain();
                        foreach ($session['validators'] as $validator) {
                            $validator = new $validator();
                            $chain->attach('session.validate', array($validator, 'isValid'));

                        }
                    }
                } else {
                    $sessionManager = new SessionManager();
                }
                Container::setDefaultManager($sessionManager);
                return $sessionManager;
            },
            'Web\Auth\Service' => function ($sm) {
                $authAdapter = new AuthAdapter($sm->get('Zend\Db\Adapter\Adapter'));
                $authAdapter
                    ->setTableName('auth_user')
                    ->setIdentityColumn('user')
                    ->setCredentialColumn('passwd')
                //->setCredentialTreatment("SHA2(CONCAT('static',?,salt),512) and status='active'")
                    ->setCredentialTreatment("PASSWORD(?)");

                $authService = new AuthenticationService();
                $authService->setAdapter($authAdapter);
                $authService->setStorage(new SessionStorage('user'));
                return $authService;
            },


        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Web\Controller\Index' => 'Web\Controller\IndexController',
            'Web\Controller\Auth' => 'Web\Controller\AuthController',
            'Web\Controller\Dashboard' => 'Web\Controller\DashboardController',
            'Web\Controller\Perm' => 'Web\Controller\PermController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(),
        ),
    ),


);
