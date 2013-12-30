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
use Web\Form\Login;

class AuthController extends AbstractActionController
{
    public function loginAction()
    {
        $form = new Login();
        $message = '';

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost()->toArray());
            if ($form->isValid()) {
                $as = $this->serviceLocator->get('Web\Auth\Service');
                $data = $form->getData();
                $adapter=$as->getAdapter()->setIdentity($data['f_user'])->setCredential($data['f_passwd']);
                $as->setAdapter($adapter);
                //var_dump($data);exit();hasIdentity

                $result = $as->authenticate();
                if($result->isValid()) {
                    // Form is valid, save the form!
                    $user = $this->getServiceLocator()->get('Core\Model\User');
                    $user->exchangeArray(get_object_vars($adapter->getResultRowObject()));
                    $as->getStorage()->write($user);
                    return $this->redirect()->toRoute('dashboard-view');
                }
                else
                {
                    $message = 'Username or Password not found';
                }
            }
        }

        return array('form' => $form,'message' => $message);
    }
}
