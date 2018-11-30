<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Action.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Controller_Action extends Zend_Controller_Action
{
  // Session

  /**
   * Namespace unique to this controller
   * 
   * @var Zend_Session_Namespace
   */
  protected $_session;

  /**
   * Gets a session namespace unique to this controller
   * 
   * @return Zend_Session_Namespace
   */
  public function getSession()
  {
    if( is_null($this->_session) )
    {
      Engine_Loader::loadClass('Zend_Session_Namespace');
      $namespace = get_class($this);
      $this->_session = new Zend_Session_Namespace($namespace);
    }
    return $this->_session;
  }

  /**
   * Set the session namespace
   * 
   * @param Zend_Session_Abstract $session
   * @return Engine_Controller_Action
   */
  public function setSession(Zend_Session_Abstract $session)
  {
    $this->_session = $session;
    return $this;
  }

  protected function _getParam($paramName, $default = null)
  {
    $value = $this->getRequest()->getParam($paramName);
    if ((null === $value) && (null !== $default)) {
      $value = $default;
    }

    return $value;
  }
}