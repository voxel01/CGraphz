<?php
namespace Web\Event;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Event;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\Permissions\Acl\Resource\GenericResource;

class Auth extends MvcEvent
{
    const ROLE_GUEST='guest';
    protected $debug = false;

    protected function log($msg)
    {
        if($this->debug) error_log($msg);
    }
    public function listen(Event $e)
    {
        $event = $e->getName();
        if($event == 'bootstrap')
        {
            $this->initAcl($e);
        }
        elseif($event == 'route')
        {
            $this->checkAcl($e);
        }
    }

    protected function initAcl(MvcEvent $e) {

        $acl = new Acl();
        //$roles = include __DIR__ . '/config/module.acl.roles.php';
        $roles = $this->getDbRoles($e);
        $allResources = array();
        foreach ($roles as $role => $resources) {

            $role = new GenericRole($role);
            $acl -> addRole($role);

            //$allResources = array_merge($resources, $allResources);

            //adding resources
            foreach ($resources as $resource) {
                if(!$acl ->hasResource($resource))
                    $acl -> addResource(new GenericResource($resource));
                $acl -> allow($role, $resource);
            }
            //adding restrictions
            //foreach ($allResources as $resource) {
            //}
        }
        //setting to view
        $e -> getViewModel() -> acl = $acl;
        //echo "<pre>";var_dump($acl);exit();

    }


    public function getDbRoles(MvcEvent $e){
        // I take it that your adapter is already configured
        $dbAdapter = $e->getApplication()->getServiceManager()->get('Zend\Db\Adapter\Adapter');

        $sql = new \Zend\Db\Sql\Sql($dbAdapter);
        $select = $sql->select('perm_module');
        $select->columns(array( 'module','component'))
            ->join('perm_module_group','perm_module.id_perm_module = perm_module_group.id_perm_module',array())
            ->join('auth_group','perm_module_group.id_auth_group = auth_group.id_auth_group',array('role'=>'group'))
        ;
        //$results = $dbAdapter->query('SELECT * FROM rules left join role ON (rules.roleId = role.id) left join user (rules.userId = user.Id)');
        $stmt = $sql->prepareStatementForSqlObject($select);
        $results = $stmt->execute();
        // making the roles array
        $roles = array();
        foreach($results as $result){
            $roles[$result['role']][] = $result['module'].'-'.$result['component'];
        }
        $this->log('ACL Rules: ',var_export($roles,true));
        return $roles;
    }

    public function checkAcl(MvcEvent $e) {
        $route = $e->getRouteMatch()->getMatchedRouteName();
        error_log("Route: $route");
        //you set your role

        $userRoles = $this->getAuthRole($e);

        foreach($userRoles as $userRole)
        {
            try{
                $isAllowed = $e -> getViewModel() -> acl -> isAllowed($userRole['group'], $route);
                $response = $e -> getResponse();
                if($isAllowed)
                {
                    break;
                }
            }
            catch(\Exception $err)
            {
                error_log($err->getMessage());
                //$response = $e->getMessage();
                $response = new \Zend\Http\Response();
                $response->setContent("<html><body>".$err->getMessage()."</body></html>");
                $isAllowed = false;
            }
        }
        $this->log('is allowed: '.var_export($isAllowed,true));
        if (!$isAllowed) {
            //if ($e -> getViewModel() -> acl ->hasResource($route) && !$e -> getViewModel() -> acl -> isAllowed($userRole, $route)) {
            //$response = $e -> getResponse();
            //location to page or what ever
            if($e->getApplication()->getServiceManager()->get('Web\Auth\Service')->hasIdentity() !== true){
                $response -> getHeaders() -> addHeaderLine('Location', $e -> getRequest() -> getBaseUrl() . $e->getRouter()->assemble(array(),array('name'=>'auth-login')));
                $response -> setStatusCode(302);
            }
            else
            {
                $response -> getHeaders() -> addHeaderLine('Location', $e -> getRequest() -> getBaseUrl() . $e->getRouter()->assemble(array(),array('name'=>'home')));
                $response -> setStatusCode(302);
            }
        }
        $e->setResponse($response);
    }

    protected function getAuthRole(MvcEvent $e)
    {
        $userRoles = array();
        $authAdapter = $e->getApplication()->getServiceManager()->get('Web\Auth\Service');
        if($authAdapter->hasIdentity() === true){
            //is logged in
            $id= $e->getApplication()->getServiceManager()->get('Core\Model\UserIdentity');

            $userRoles = $id->getGroups();
        }
        if($authAdapter->hasIdentity() !== true || count($userRoles)==0 ) {
            $userRoles[] = array('group'=>self::ROLE_GUEST);
        }
        $this->log("userroles: ".var_export($userRoles,true));
        return $userRoles;
    }
}
