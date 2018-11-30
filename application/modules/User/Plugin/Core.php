<?
class User_Plugin_Core
{
  public function onUserDeleteBefore($event)
  {
    $payload = $event->getPayload();
    if( $payload instanceof User_Model_User ) {

      // Remove from online users
      $onlineUsersTable = Engine_Api::_()->getDbtable('online', 'user');
      $onlineUsersTable->delete(array(
        'user_id = ?' => $payload->getIdentity(),
      ));

      // Remove friends
      $payload->membership()->removeAllUserFriendship();

      // Remove all cases user is in a friend list
      $payload->lists()->removeUserFromLists();

      // Remove all friend list created by the user
      $payload->lists()->removeUserLists();
      
      // Remove facebook/twitter associations
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->delete('engine4_user_facebook', array(
        'user_id = ?' => $payload->getIdentity(),
      ));
      $db->delete('engine4_user_twitter', array(
        'user_id = ?' => $payload->getIdentity(),
      ));
      $db->delete('engine4_user_janrain', array(
        'user_id = ?' => $payload->getIdentity(),
      ));
    }
  }

  public function onUserEnable($event)
  {
    /* @var User_Model_User $user */
    $user = $event->getPayload();
  }


  public function onUserCreateAfter($event)
  {
    $payload = $event->getPayload();
    if( $payload instanceof User_Model_User ) {
//      if( 'none' != Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable ){
//        $facebook = User_Model_DbTable_Facebook::getFBInstance();
//        if ($facebook->getUser()) {
//          try {
//            $facebook->api('/me');
//            $table = Engine_Api::_()->getDbtable('facebook', 'user');
//            $row = $table->fetchRow(array('user_id = ?'=>$payload->getIdentity()));
//            if (!$row) {
//              $row = Engine_Api::_()->getDbtable('facebook', 'user')->createRow();
//              $row->user_id = $payload->getIdentity();
//            }
//            $row->facebook_uid = $facebook->getUser();
//            $row->save();
//          } catch (Exception $e) {}
//        }
//      }
    
      // Set default email notifications
      $notificationTypesTable = Engine_Api::_()->getDbtable('notificationTypes', 'activity');
      
      // For backwards compatiblitiy this block will only execute if the 
      // getDefaultNotifications function exists. If notifications aren't 
      // being added to the engine4_activity_notificationsettings table
      // check to see if the Activity_Model_DbTable_NotificationTypes class
      // is out of date
      if( method_exists($notificationTypesTable, 'getDefaultNotifications') ){
        $defaultNotifications = $notificationTypesTable->getDefaultNotifications();
        
        Engine_Api::_()->getDbtable('notificationSettings', 'activity')
          ->setEnabledNotifications($payload, $defaultNotifications);
      }
    }
  }

}