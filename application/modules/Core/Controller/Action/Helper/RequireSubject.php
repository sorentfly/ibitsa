<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Core_Controller_Action_Helper_RequireSubject extends Core_Controller_Action_Helper_RequireAbstract
{
    protected $_errorAction = array('requiresubject', 'error', 'core');

    protected $_requiredType;

    protected $_actionRequireTypes = array();

    public function direct($type = null)
    {
        if( null !== $type ) {
            $this->setRequiredType($type);
        }
        return parent::direct();
    }

    public function reset()
    {
        parent::reset();

        $this->_errorAction = array('requiresubject', 'error', 'core');
        $this->_requiredType = null;
        $this->_actionRequireTypes = array();

        return $this;
    }

    public function checkRequire()
    {
        try
        {
            $subject = Engine_Api::_()->core()->getSubject();
        }
        catch( Exception $e )
        {
            $subject = null;
        }

        $actionName = $this->getFrontController()->getRequest()->getActionName();
        $ret = true;

        if( !($subject instanceof Core_Model_Item_Abstract) || !$subject->getIdentity() )
        {
            $ret = false;
        }

        else if( null !== $this->_requiredType && $subject->getType() != $this->_requiredType )
        {
            $ret = false;
        }

        else if( null !== ($requireType = $this->getActionRequireType($actionName)) &&
            $subject->getType() != $requireType )
        {
            $ret = false;
        }

        if( !$ret && APPLICATION_ENV == 'development' && Zend_Registry::isRegistered('Zend_Log') && ($log = Zend_Registry::get('Zend_Log')) instanceof Zend_Log )
        {
            $target = $this->getRequest()->getModuleName() . '.' .
                $this->getRequest()->getControllerName() . '.' .
                $this->getRequest()->getActionName();
            $log->log('Require class '.get_class($this).' failed check for: '.$target, Zend_Log::DEBUG);
        }

        return $ret;
    }

    public function setRequiredType($type = null)
    {
        $this->_requiredType = $type;
        return $this;
    }



    // Action requires

    public function setActionRequireTypes(array $data)
    {
        foreach( $data as $key => $value )
        {
            $this->setActionRequireType($key, $value);
        }
        return $this;
    }

    public function setActionRequireType($action, $type = null)
    {
        $this->_actionRequireTypes[$action] = $type;
        $this->addActionRequire($action);
        return $this;
    }

    public function hasActionRequireType($action)
    {
        return ( null !== $this->getActionRequireType($action) );
    }

    public function getActionRequireType($action)
    {
        if( !isset($this->_actionRequireTypes[$action]) )
        {
            return null;
        }
        return $this->_actionRequireTypes[$action];
    }

    /**
     * @return $this
     * @internal param bool $action
     */
    public function removeActionRequireType()
    {
        unset($this->_actionRequireTypes);
        return $this;
    }
}