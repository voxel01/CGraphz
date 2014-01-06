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
use Core\Model\ModuleTable;
use Core\Model\Module;

class PermController extends AbstractActionController
{

    public function moduleAction()
    {
        $moduleTable = $this->getServiceLocator()->get('Core\Model\ModuleTable');
        $groupTable = $this->getServiceLocator()->get('Core\Model\GroupTable');
        $return = array();
        $form = new \Web\Form\Module();

        $return['form'] = $form;
        $moduleId = intval($this->params('id_perm_module',0));

        $request = $this->getRequest();
        if($moduleId)
        {
            $module = $moduleTable->getModule($moduleId);
            //var_dump($module);exit();
            if($module)
            {
                $deleteGroup = intval($this->params()->fromQuery('deleteGroup',0));
                if($deleteGroup)
                {
                    $groupTable->dropGroupFromModule($module,$deleteGroup);
                }
                $form->setData(get_object_vars($module));
            }
        }
        if ($request->isPost()) {
            if($this->params()->fromPost('f_submit_module_group',null))
            {
                $groupId = intval($this->params()->fromPost('f_id_auth_group',0));
                if($groupId)
                {
                    $groupTable->addGroupToModule($module,$groupId);
                }
            }
            else
            {
                $form->setData($request->getPost()->toArray());
                if ($form->isValid()) {
                    if($this->params('delete',false))
                    {
                        $moduleTable->deleteModule($moduleId);
                        unset($return['module']);
                    }
                    else
                    {
                        $m = new \Core\Model\Module();
                        $m->exchangeArray($form->getData());
                        $moduleTable->saveModule($m);
                    }
                }
            }
        }

        if($module)
        {
            $return['module'] = $module;
            $return['groups'] = $groupTable->getGroupsToModule($module);
        }


        $return['modules'] = $moduleTable->fetchAll();
        return new ViewModel($return);
    }

    public function groupAction()
    {
        $groupTable = $this->getServiceLocator()->get('Core\Model\GroupTable');
        $return = array();
        $groupId = intval($this->params('id_auth_group',0));
        $function = $this->params('function','show');
        $form = new \Web\Form\Group();
        $request = $this->getRequest();
        if($groupId)
        {
            $group = $groupTable->getGroup($groupId);
        }
        if($function == 'edit')
        {
            $form->setData(get_object_vars($group));
            if($request->isPost())
            {
                $form->setData($request->getPost()->toArray());
                if($form->isValid())
                {
                    if($this->params('delete',false))
                    {
                        $groupTable->deleteGroup($groupId);
                        unset($return['module']);
                    }
                    else
                    {
                        $m = new \Core\Model\Group();
                        $m->exchangeArray($form->getData());
                        $groupTable->saveGroup($m);
                    }
                }
            }
        }
        elseif($function == 'add')
        {
            if($request->isPost())
            {
                $form->setData(array_merge($request->getPost()->toArray(),array('id_auth_group' => "0")));
                var_dump($form);
                echo "<br>\n";
                echo "<br>\n";
                var_dump($form->isValid());exit();
                if($form->isValid())
                {
                    $m = new \Core\Model\Group();
                    $m->exchangeArray($form->getData());
                    $groupId = $groupTable->saveGroup($m);
                    $this->forward('perm-group',array('function'=>'edit','id_auth_group'=>$groupId));
                }
            }
        }


        if($group) $return['group'] = $group;
        $return['groups'] = $groupTable->fetchAll();
        $return['form'] = $form;

        return new ViewModel($return);
    }
}
