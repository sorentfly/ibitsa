<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Membership.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_DbTable_Membership extends Core_Model_DbTable_Membership
{
  protected $_type = 'user';

  public function isReciprocal()
  {
    return (bool) Engine_Api::_()->getApi('settings', 'core')
        ->getSetting('user.friends.direction', 1);
  }

  public function isUserApprovalRequired()
  {
    return (bool) Engine_Api::_()->getApi('settings', 'core')
        ->getSetting('user.friends.verification', true);
  }

  public function isResourceApprovalRequired(Core_Model_Item_Abstract $resource)
  {
    return true;
  }


  // Implement reciprocal

  public function addMember(Core_Model_Item_Abstract $resource, User_Model_User $user)
  {
    parent::addMember($resource, $user);
  
    if( $this->isReciprocal() ) {
      parent::addMember($user, $resource);
    }
    
//    parent::setResourceApproved($resource, $user);
//
//    if( $this->isReciprocal() ) {
//      parent::setUserApproved($user, $resource);
//    }

    return $this;
  }

  public function removeMember(Core_Model_Item_Abstract $resource, User_Model_User $user)
  {
    parent::removeMember($resource, $user);

    if( $this->isReciprocal() ) {
      parent::removeMember($user, $resource);
    }
    
    return $this;
  }

  public function setResourceApproved(Core_Model_Item_Abstract $resource, User_Model_User $user)
  {
    parent::setResourceApproved($resource, $user);

    if( $this->isReciprocal() ) {
      parent::setUserApproved($user, $resource);
    }

    if( !$this->isUserApprovalRequired() ) {
      parent::setUserApproved($resource, $user);
      
      if( $this->isReciprocal() ) {
        parent::setResourceApproved($user, $resource);
      }
    }

    return $this;
  }

  public function setUserApproved(Core_Model_Item_Abstract $resource, User_Model_User $user)
  {
    parent::setUserApproved($resource, $user);

    if( $this->isReciprocal() ) {
      parent::setResourceApproved($user, $resource);
    }

    if( !$this->isUserApprovalRequired() ) {
      parent::setResourceApproved($resource, $user);

      if( $this->isReciprocal() ) {
        parent::setUserApproved($user, $resource);
      }
    }
    
    return $this;
  }
  
  public function removeAllUserFriendship(User_Model_User $user)
  {
    // first get all cases where user_id == $user->getIdentity
    $select = $this->getTable()->select()
      ->where('user_id = ?', $user->getIdentity());
    
    $friendships = $this->getTable()->fetchAll($select);
    foreach( $friendships as $friendship ) {
      // if active == 1 get the user corresponding to resource_id and take away the member_count by 1
      if($friendship->active){
        $friend = Engine_Api::_()->getItem('user', $friendship->resource_id);
        if($friend && !empty($friend->member_count)){
          $friend->member_count--;
          $friend->save();
        }
      }
      $friendship->delete();
    }

    // get all cases where resource_id == $user->getIdentity
    // remove all   
    $this->getTable()->delete(array(
      'resource_id = ?' => $user->getIdentity()
    ));
  }
  
  public function getWidgetActions(Core_Model_Item_Abstract $resource, User_Model_User $user,  $memberInfo = null)
    {
        $view = Zend_Registry::get('Zend_View');
        if (!$memberInfo){
            $memberInfo = $this->getMemberInfo($resource, $user);
        }
        $actions = array();
        
        // two-way-frendship mode only (see User_View_Helper_UserFriendship for more)
        if( null === $memberInfo ) {
          $actions[] = $view->htmlLink(array('route' => 'user_extended', 'controller' => 'friends', 'action' => 'add', 'user_id' => $user->user_id),  '<i class="fa fagreater fa-check"></i> ', array(
            'class' => 'iconedlink obj-admin-controller__btn smoothbox', 'title' => $view->translate('Add Friend')
          ));
        } else if( $memberInfo->user_approved == 0 ) {
          $actions[] = $view->htmlLink(array('route' => 'user_extended', 'controller' => 'friends', 'action' => 'cancel', 'user_id' => $user->user_id), '<i class="fa fagreater fa-remove"></i> ', array(
            'class' => 'iconedlink obj-admin-controller__btn smoothbox', 'title' => $view->translate('Cancel Request')
          ));
        } else if( $memberInfo->resource_approved == 0 ) {
          $actions[] = $view->htmlLink(array('route' => 'user_extended', 'controller' => 'friends', 'action' => 'confirm', 'user_id' => $user->user_id), '<i class="fa fagreater fa-check"></i> ', array(
            'class' => 'iconedlink obj-admin-controller__btn smoothbox', 'title' => $view->translate('Accept Request')
          ));
        } else if( $memberInfo->active ) {
          $actions[] = $view->htmlLink(array('route' => 'user_extended', 'controller' => 'friends', 'action' => 'remove', 'user_id' => $user->user_id), '<i class="fa fagreater fa-remove"></i> ', array(
            'class' => 'iconedlink obj-admin-controller__btn smoothbox', 'title' => $view->translate('Remove Friend')
          ));
        }
      
        return $actions;
    }
    
    public function getWidgetCountLabel(Core_Model_Item_Abstract $resource, $memberCount, $search)
    {
        $view = Zend_Registry::get('Zend_View');
        if (!$search){
            return $view->translate(array('This user has %1$s friend.', 'This user has %1$s friends.', $memberCount), $memberCount );
        }else{
            return $view->translate(array('This user has %1$s friends that matched the query %2$s.', 'This user has %1$s friends that matched the query %2$s.', $memberCount), $memberCount, $view->escape($search));
        }
    }
}