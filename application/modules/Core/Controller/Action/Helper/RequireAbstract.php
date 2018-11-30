<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
abstract class Core_Controller_Action_Helper_RequireAbstract extends Zend_Controller_Action_Helper_Abstract
{
    protected $_require = false;

    protected $_actionRequires = array();

    protected $_noForward = false;

    protected $_errorAction = array('error', 'error', 'core');

    /**
     * @return $this
     */
    public function direct()
    {
        $this->setRequire(true);
        return $this;
    }

    /**
     * @return mixed
     */
    public function isValid()
    {
        $valid = $this->checkRequire();

        if( !$valid && !$this->getNoForward() )
        {
            $this->forward();
        }

        return $valid;
    }

    /**
     *
     */
    public function forward()
    {
        // Stolen from Zend_Controller_Action::forward
        list($action, $controller, $module) = $this->getErrorAction();
        $request = $this->getActionController()->getRequest();

        if (null !== $controller) {
            $request->setControllerName($controller);
        }

        if (null !== $module) {
            $request->setModuleName($module);
        }

        $request->setActionName($action)
            ->setDispatched(false);
    }

    /**
     *
     */
    public function preDispatch()
    {
        // Require all
        if( $this->getRequire() || $this->hasActionRequire($this->getActionController()->getRequest()->getActionName()) ) {
            $this->isValid();
            // Should we do a reset here?
            $this->reset();
            //$this->setRequire(false);
        }
    }

    /**
     *
     */
    public function postDispatch()
    {
        $this->reset();
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setRequire($flag = true)
    {
        $this->_require = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRequire()
    {
        return (bool) $this->_require;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setNoForward($flag = true)
    {
        $this->_noForward = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getNoForward()
    {
        return $this->_noForward;
    }

    /**
     * @param $action
     * @param null $controller
     * @param null $module
     * @return $this
     */
    public function setErrorAction($action, $controller = null, $module = null)
    {
        $this->_errorAction = array($action, $controller, $module);
        return $this;
    }

    /**
     * @return array
     * @throws Zend_Controller_Action_Exception
     */
    public function getErrorAction()
    {
        if( is_null($this->_errorAction) )
        {
            throw new Zend_Controller_Action_Exception('No action was set');
        }
        return $this->_errorAction;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        // $this->_errorAction = array('error', 'error', 'core');
        $this->_noForward = false;
        $this->_require = false;
        $this->_actionRequires = array();

        return $this;
    }



    # Action requires

    /**
     * @param $action
     * @param bool $options
     * @return $this
     */
    public function addActionRequire($action, $options = true)
    {
        $this->_actionRequires[$action] = $options;
        return $this;
    }

    /**
     * @param array $actions
     * @return $this
     */
    public function addActionRequires(array $actions)
    {
        foreach( $actions as $key => $value )
        {
            if( is_numeric($key) ) {
                $this->addActionRequire($value);
            } else {
                $this->addActionRequire($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param $action
     * @return mixed|null
     */
    public function getActionRequire($action)
    {
        if( !$this->hasActionRequire($action) )
        {
            return null;
        }

        return $this->_actionRequires[$action];
    }

    /**
     * @param $action
     * @return bool
     */
    public function hasActionRequire($action)
    {
        return isset($this->_actionRequires[$action]);
    }

    public function removeActionRequire($action)
    {
        unset($this->_actionRequires[$action]);
        return $this;
    }



    // Abstract

    /**
     * @return mixed
     */
    abstract public function checkRequire();
}