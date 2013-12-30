<?php
namespace Core\Model;

use Zend\Db\Adapter\AdapterAwareInterface;

class Project extends AbstractDbAware implements AdapterAwareInterface
{
    public $id_config_project=0;
    public $project;
    public $project_description;

    protected $server = null;
    public function exchangeArray($data)
    {
        $this->id_config_project = (isset($data['id_config_project'])) ? $data['id_config_project'] : null;
        $this->project = (isset($data['project'])) ? $data['project'] : null;
        $this->project_description = (isset($data['project_description'])) ? $data['project_description'] : null;
    }

    public function getServer()
    {
        if(null === $this->server)
        {
            $sql = new \Zend\Db\Sql\Sql($this->getDb());
            $where = new \Zend\Db\Sql\Predicate\Operator('id_config_project',\Zend\Db\Sql\Predicate\Operator::OPERATOR_EQUAL_TO,$this->id_config_project);
            $select = $sql->select('config_server')->columns(array('*'))
                ->join('config_server_project','config_server_project.id_config_server = config_server.id_config_server',array())
                ->where($where)
            ;
            //echo $select->getSqlString();exit();
            $result = $sql->prepareStatementForSqlObject($select)->execute();
            $server = array();
            foreach($result as $res)
            {
                $s = new Server();
                $s->exchangeArray($res);
                $server[$res['id_config_server']] = $s;
            }
            $this->server = $server;
        }
        return $this->server;
    }
}
