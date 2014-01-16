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
            $form->setData(get_object_vars($group));
            $userTable  = $this->getServiceLocator()->get('Core\Model\UserTable');
            $projectTable  = $this->getServiceLocator()->get('Core\Model\ProjectTable');
            $filterTable  = $this->getServiceLocator()->get('Core\Model\FilterTable');
            $moduleTable  = $this->getServiceLocator()->get('Core\Model\ModuleTable');
        }
        if($function == 'edit')
        {
            if($request->isPost())
            {
                $form->setData($request->getPost()->toArray());
                if($form->isValid())
                {
                    if($this->params()->fromPost('delete',false))
                    {
                        $groupTable->deleteGroup($groupId);
                        return $this->redirect()->toRoute('perm-group',array('function'=>'show'));
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
                $form->remove('id_auth_group');
                $data = $request->getPost()->toArray();
                unset($data['id_auth_group']);
                $form->setData($data);
                if($form->isValid())
                {
                    $m = new \Core\Model\Group();
                    $m->exchangeArray($form->getData());
                    $groupId = $groupTable->saveGroup($m);
                    return $this->redirect()->toRoute('perm-group',array('function'=>'edit','id_auth_group'=>$groupId));
                }
            }
        }
        elseif($function == 'user')
        {
            $this->saveEdit($request,'user',$group);
        }
        elseif($function == 'project')
        {
            $this->saveEdit($request,'project',$group);
        }
        elseif($function == 'filter')
        {
            $this->saveEdit($request,'filter',$group);
        }
        elseif($function == 'module')
        {
            $this->saveEdit($request,'module',$group);
        }

        if($group){
            $return['group'] = $group;
            $return['users'] = $userTable->getUsersToGroup($group);
            $return['projects'] = $projectTable->getProjectsToGroup($group);
            $return['filters'] = $filterTable->getFiltersToGroup($group);
            $return['modules'] = $moduleTable->getModulesToGroup($group);
        }
        $return['groups'] = $groupTable->fetchAll();
        $return['form'] = $form;

        return new ViewModel($return);
    }

    protected function saveEdit($request, $type, $group)
    {
        $base = array_pop(explode('\\',get_class($group)));
        if($request->isPost()) //Maybe add something
        {
            if($this->params()->fromPost('f_submit_'.$type,false))
            {
                $table  = $this->getServiceLocator()->get('Core\Model\\'.ucfirst($type).'Table');
                $add = intval($this->params()->fromPost('f_id_'.$type,0));
                if($add)
                {
                    $func = 'add'.ucfirst($type).'to'.$base;
                    $table->$func($group, $add);
                }
            }
        }
        else
        {
            $table  = $this->getServiceLocator()->get('Core\Model\\'.ucfirst($type).'Table');
            $drop = intval($this->params()->fromQuery('delete'.$type,false));
            if($drop)
            {
                $func = 'drop'.ucfirst($type).'from'.$base;
                $table->$func($group, $drop);
            }
        }
    }
}
