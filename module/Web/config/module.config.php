<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
use Zend\Session\SessionManager;
use Zend\Session\Container;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;

return array(
    'navigation' => array(
        'default' => array(
            array(
                'label' => 'Home',
                'route' => 'home',
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

            'Core\Model\User' => function ($sm) {
                $user = new Core\Model\User();
                $user->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
                return $user;
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
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
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
