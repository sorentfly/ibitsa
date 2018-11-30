<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Cleanup.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Plugin_Task_Cleanup extends Core_Plugin_Task_Abstract
{
  public function execute()
  {
    // Garbage collect the online users table
    Engine_Api::_()->getDbtable('online', 'user')->gc();

    // Garbage collect the forgot password table
    Engine_Api::_()->getDbtable('forgot', 'user')->gc();

    // Garbage collect the verification table
    Engine_Api::_()->getDbtable('verify', 'user')->gc();
    
    // Garbage collect the expired application tokens
    if (Engine_Api::_()->hasModuleBootstrap('application')){
        Engine_Api::_()->getDbtable('tokens', 'application')->gc();
    }
    //Cleanup old sessions
    Engine_Api::_()->getDbtable('session', 'core')->gc();

    Engine_Api::_()->getDbtable('logins', 'user')->gc();

      Engine_Api::_()->getDbTable('AccessTokens', 'user')->gc();
    // This task shouldn't take too long, just set was idle
    $this->_setWasIdle();
  }
}