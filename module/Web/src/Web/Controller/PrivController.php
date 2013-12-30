<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Web\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PrivController extends AbstractActionController
{
    public function userAction()
    {
        return new ViewModel();
    }

    public function groupAction()
    {
        return new ViewModel();
    }
    public function moduleAction()
    {
        return new ViewModel();
    }
}
