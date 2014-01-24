<?php
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
