<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
class Core_Model_DbTable_Modules extends Engine_Db_Table
{
    /**
     * @var
     */
    protected $_modules;

    /**
     * @var array
     */
    protected $_modulesAssoc = [];

    /**
     * @var
     */
    protected $_enabledModuleNames;

    /**
     * @param $name
     * @return mixed|null
     */
    public function getModule($name)
    {
        if( null === $this->_modules ) {
            $this->getModules();
        }

        if( !empty($this->_modulesAssoc[$name]) ) {
            return $this->_modulesAssoc[$name];
        }

        return null;
    }

    /**
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getModules()
    {
        if( null === $this->_modules ) {
            $this->_modules = $this->fetchAll();
            foreach( $this->_modules as $module ) {
                $this->_modulesAssoc[$module->name] = $module;
            }
        }

        return $this->_modules;
    }

    /**
     * @return array
     */
    public function getModulesAssoc()
    {
        if( null === $this->_modules ) {
            $this->getModules();
        }

        return $this->_modulesAssoc;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasModule($name)
    {
        return !empty($this->_modulesAssoc[$name]);
    }

    /**
     * @param $name
     * @return bool
     */
    public function isModuleEnabled($name)
    {
        return in_array($name, $this->getEnabledModuleNames());
    }

    /**
     * @return array
     */
    public function getEnabledModuleNames()
    {
        if( null === $this->_enabledModuleNames ) {
            $this->_enabledModuleNames = $this->select()
                ->from($this, 'name')
                ->where('enabled = ?', true)
                ->query()
                ->fetchAll(Zend_Db::FETCH_COLUMN);
        }

        return $this->_enabledModuleNames;
    }
}
