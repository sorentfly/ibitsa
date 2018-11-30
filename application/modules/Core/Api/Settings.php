<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Core_Api_Settings extends Core_Api_Abstract
{
    /* @var Core_Model_DbTable_Settings  */
    protected $_table;

    public function  __construct()
    {
        $this->_table = Engine_Api::_()->getDbtable('settings', 'core');
    }

    public function __get($key)
    {
        return $this->_table->getSetting($key);
    }

    public function __set($key, $value)
    {
        return $this->_table->setSetting($key, $value);
    }

    public function __isset($key)
    {
        return $this->_table->hasSetting($key);
    }

    public function __unset($key)
    {
        return $this->_table->removeSetting($key);
    }

    public function __call($method, array $arguments = array())
    {
        $r = new ReflectionMethod($this->_table, $method);
        return $r->invokeArgs($this->_table, $arguments);
    }
}