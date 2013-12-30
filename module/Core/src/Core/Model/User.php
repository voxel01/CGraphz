<?php

namespace Core\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;


class User extends AbstractDbAware implements AdapterAwareInterface
{
    public $id_auth_user;
    public $nom;
    public $prenom;
    public $user;
    public $mail;
    public $passwd;
    public $type;

    protected $filter = null;
    protected $projects = null;
    protected $groups = null;



    public function __sleep()
    {
        return array('id_auth_user','nom','prenom','user','mail','passwd','type');
    }


    public function exchangeArray($data)
    {
        $this->id_auth_user = (isset($data['id_auth_user'])) ? $data['id_auth_user'] : null;
        $this->nom = (isset($data['nom'])) ? $data['nom'] : null;
        $this->prenom = (isset($data['prenom'])) ? $data['prenom'] : null;
        $this->user = (isset($data['user'])) ? $data['user'] : null;
        $this->mail = (isset($data['mail'])) ? $data['mail'] : null;
        $this->passwd = (isset($data['passwd'])) ? $data['passwd'] : null;
        $this->type = (isset($data['type'])) ? $data['type'] : null;
    }


    public function getGroups()
    {
        if(null === $this->groups)
        {
            $sql = new \Zend\Db\Sql\Sql($this->getDb());
            $select = $sql->select('auth_group') ->columns(array('id_auth_group','group'))
                ->join('auth_user_group','auth_user_group.id_auth_group = auth_group.id_auth_group',array())
                ->where('id_auth_user = :user')
            ;
            //echo $select->getSqlString();
            //echo "<br>User: ".var_export($this->id_auth_user)."<br>";
            $result = $sql->prepareStatementForSqlObject($select)->execute(array(':user'=>$this->id_auth_user));
            foreach($result as $res)
            {
                $userRoles[] = $res;
            }
            $this->groups = $userRoles;
        }
        return $this->groups;
    }

    public function getProjects()
    {
        if(null === $this->projects)
        {
            $groups = $this->getGroups();
            $gids = array();
            foreach($groups as $group)
            {
                $gids[] = $group['id_auth_group'];
            }
            $adapter=$this->getDb();
            $sql = new \Zend\Db\Sql\Sql($adapter);
            $where = new \Zend\Db\Sql\Predicate\In('id_auth_group',$gids);
            $select = $sql->select('perm_project_group') ->columns(array())
                ->join('config_project','perm_project_group.id_config_project = config_project.id_config_project')
                ->where($where)
            ;
            //var_dump($groups);
            //echo $select->getSqlString();exit();
            $result = $sql->prepareStatementForSqlObject($select)->execute();
            foreach($result as $res)
            {
                $p = new Project();
                $p->setDbAdapter($adapter);
                $p->exchangeArray($res);
                $projects[$p->id_config_project] = $p;
            }
            $this->projects = $projects;
        }
        return $this->projects;
    }

    public function getFilter()
    {
        if(null === $this->filter)
        {
            $groups = $this->getGroups();
            $gids = array();
            foreach($groups as $group)
            {
                $gids[] = $group['id_auth_group'];
            }
            $adapter=$this->getDb();
            $sql = new \Zend\Db\Sql\Sql($adapter);
            $where = new \Zend\Db\Sql\Predicate\In('id_auth_group',$gids);
            $select = $sql->select('config_plugin_filter_group') ->columns(array())
                ->join('config_plugin_filter','config_plugin_filter.id_config_plugin_filter = config_plugin_filter_group.id_config_plugin_filter')
                ->where($where)
            ;
            //var_dump($groups);
            //echo $select->getSqlString();exit();
            $result = $sql->prepareStatementForSqlObject($select)->execute();
            foreach($result as $res)
            {
                $f = new Filter();
                $f->exchangeArray($res);
                $filter[$f->id_config_plugin_filter] = $f;
            }
            $this->filter = $filter;
        }
        return $this->filter;
    }
}
