<?php
namespace Web\Navigation;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;

class Navigation extends DefaultNavigationFactory
{
    protected function getPages(ServiceLocatorInterface $serviceLocator)
    {
        if (null === $this->pages) {
            //FETCH data from table menu :
            //$fetchMenu = $serviceLocator->get('menu')->fetchAll();
            $fetchMenu = array();

            //$configuration['navigation'][$this->getName()] = array();
            $configuration = $serviceLocator->get('Config');
            foreach($fetchMenu as $key=>$row)
            {
                $configuration['navigation'][$this->getName()][$row['name']] = array(
                    'label' => $row['label'],
                    'route' => $row['route'],
                );
            }

            if (!isset($configuration['navigation'])) {
                throw new \Zend\Navigation\Exception\InvalidArgumentException('Could not find navigation configuration key');
            }
            if (!isset($configuration['navigation'][$this->getName()])) {
                throw new \Zend\Navigation\Exception\InvalidArgumentException(sprintf(
                    'Failed to find a navigation container by the name "%s"',
                    $this->getName()
                ));
            }

            $application = $serviceLocator->get('Application');
            $routeMatch  = $application->getMvcEvent()->getRouteMatch();
            $router      = $application->getMvcEvent()->getRouter();
            $pages       = $this->getPagesFromConfig($configuration['navigation'][$this->getName()]);

            $this->pages = $this->injectComponents($pages, $routeMatch, $router);
        }
        return $this->pages;
    }
}
