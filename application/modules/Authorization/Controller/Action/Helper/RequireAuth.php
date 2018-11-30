<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: RequireAuth.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Authorization_Controller_Action_Helper_RequireAuth extends Core_Controller_Action_Helper_RequireAbstract
{
  protected $_authResource;

  protected $_authRole;

  protected $_authAction;

  protected $_errorAction = array('requireauth', 'error', 'core');
  
  public function checkRequire()
  {
    $ret = Engine_Api::_()->authorization()->isAllowed(
      $this->getAuthResource(),
      $this->getAuthRole(),
      str_replace('-', '.', $this->getAuthAction())
    );

    if( !$ret && APPLICATION_ENV == 'development' && Zend_Registry::isRegistered('Zend_Log') && ($log = Zend_Registry::get('Zend_Log')) instanceof Zend_Log )
    {
      $target = $this->getRequest()->getModuleName() . '.' .
                $this->getRequest()->getControllerName() . '.' .
                $this->getRequest()->getActionName();
      $log->log('Require class '.get_class($this).' failed check for: '.$target, Zend_Log::DEBUG);
    }

    return $ret;
  }

  

  // Auth stuff
  
  public function clearAuthParams()
  {
    $this->_authResource = null;
    $this->_authRole = null;
    $this->_authAction = null;
    return $this;
  }

  public function setAuthParams($resource = null, $role = null, $action = null)
  {
    $this->clearAuthParams();
    if( $resource !== null )
    {
      $this->setAuthResource($resource);
    }
    
    if( $role !== null )
    {
      $this->setAuthRole($role);
    }

    if( $action !== null )
    {
      $this->setAuthAction($action);
    }
    
    return $this;
  }
  
  public function setAuthResource($resource = null)
  {
    $this->_authResource = $resource;
    return $this;
  }

  public function getAuthResource()
  {
    if( is_null($this->_authResource) )
    {
      if( Engine_Api::_()->core()->hasSubject() )
      {
        $this->_authResource = Engine_Api::_()->core()->getSubject();
      }
    }
    
    return $this->_authResource;
  }

  public function setAuthRole($role = null)
  {
    $this->_authRole = $role;
    return $this;
  }

  public function getAuthRole()
  {
    if( is_null($this->_authRole) )
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      if( $viewer->getIdentity() )
      {
        $this->_authRole = $viewer;
      }
    }

    return $this->_authRole;
  }

  public function setAuthAction($action = null)
  {
    $this->_authAction = $action;
    return $this;
  }

  public function getAuthAction()
  {
    if( is_null($this->_authAction) )
    {
      $this->_authAction = $this->getActionController()->getRequest()->getActionName();
    }

    return $this->_authAction;
  }

  public function reset()
  {
    parent::reset();
    $this->_errorAction = array('requireauth', 'error', 'core');
    return $this;
  }
}