<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Menus.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Messages_Plugin_Menus
{
  // core_mini
  
  public function onMenuInitialize_CoreMiniMessages($row)
  {
      return false;
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() )
    {
      return false;
    }

    // Get permission setting
    $permission = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'create');
    if( Authorization_Api_Core::LEVEL_DISALLOW === $permission )
    {
      return false;
    }

    $message_count = Engine_Api::_()->messages()->getUnreadMessageCount($viewer);
    $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl() . '/';

    return array(
      'label' => Zend_Registry::get('Zend_Translate')->_($row->label) . ( $message_count ? ' (' . $message_count .')' : '' ),
      'route' => 'messages_general',
      'params' => array(
        'action' => 'inbox'
      )
    );
  }



  // user_profile

  public function onMenuInitialize_UserProfileMessage($row)
  {
    // Not logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( !$viewer->getIdentity() || $viewer->getGuid(false) === $subject->getGuid(false) ) {
      return false;
    }

    // Get setting?
    $permission = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'create');
    if( Authorization_Api_Core::LEVEL_DISALLOW === $permission )
    {
      return false;
    }
    $messageAuth = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'auth');
    if( $messageAuth == 'none' ) {
      return false;
    } else if( $messageAuth == 'friends' ) {
      // Get data
      $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
      if( !$direction ) {
        //one way
        $friendship_status = $viewer->membership()->getRow($subject);
      }
      else $friendship_status = $subject->membership()->getRow($viewer);

      if( !$friendship_status || $friendship_status->active == 0 ) {
        return false;
      }
    }
    
    return array(
      'label' => "Send Message",
      'iconfont' => 'fa fa-send',
      'route' => 'messages_general',
      'class' => 'smoothbox',
      'params' => array(
        'action' => 'compose',
         'to' => $subject->getIdentity()
      ),
    );
  }
}
