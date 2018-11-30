<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
abstract class Core_Api_Abstract
{
    protected $_moduleName;

    public function getModuleName()
    {
        if( empty($this->_moduleName) )
        {
            $class = get_class($this);
            if (preg_match('/^([a-z][a-z0-9]*)_/i', $class, $matches)) {
                $prefix = $matches[1];
            } else {
                $prefix = $class;
            }
            $this->_moduleName = strtolower($prefix);
        }
        return $this->_moduleName;
    }

    public function __call($method, array $arguments = array())
    {
        throw new Engine_Exception(sprintf('Method "%s" not supported', $method));
    }
}