<?php
namespace Core\Model;

use Zend\Db\Adapter\Adapter;

abstract class AbstractDbAware
{

    /**
     * @var Zend\Db\Adapter\Adapter
     */
    protected $adapter = null;

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }
    /**
     * @return Zend\Db\Adapter\Adapter
     * @throws Exception
     */
    protected function getDb()
    {
        if($this->adapter === null)
            throw new Exception("DB Adapter not set");
        return $this->adapter;
    }
}
