<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Web;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;



use Zend\ModuleManager\ModuleManager;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    public function init(ModuleManager $m)
    {
        $event = $m->getEventManager()->getSharedManager();
        $event -> attach('Zend\Mvc\Application','*', array(new \Web\Event\Auth(), 'listen'));
    }
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->bootstrapSession($e);


        $application = $e->getApplication();
        $config      = $application->getConfig();
        $view        = $application->getServiceManager()->get('ViewHelperManager');
        // You must have these keys in you application config
        //$view->headTitle($config['view']['base_title']);

        //$view->setVariable('config',$config);
        //$this->booststrapNavigation($e);
        //$this->initAcl($e);
        //$e -> getApplication() -> getEventManager() -> attach('route', array($this, 'checkAcl'));

    }

    public function bootstrapSession($e)
    {
        $session = $e->getApplication()
            ->getServiceManager()
            ->get('Zend\Session\SessionManager');
        $session->start();

        $container = new Container('initialized');
        if (!isset($container->init)) {
            $session->regenerateId(true);
            $container->init = 1;
        }
    }

    /*public function booststrapNavigation($e)
    {
        $application = $e->getApplication();
        $config      = $application->getConfig();
        $view        = $application->getServiceManager()->get('ViewHelperManager');
        // You must have these keys in you application config
        $pages = array(
            array(
                'label'      => 'Home',
                'title'      => 'Go Home',
                'module'     => 'Web',
                'controller' => 'index',
                'action'     => 'index',
                'order'      => -100 // make sure home is the first page
            ),
            );
        $navigation = new \Zend\View\Helper\Navigation($pages);
        //$view->get('navigation')->setContainer($navigation);
    }*/

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }


}
