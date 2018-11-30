<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: List.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_List extends Core_Model_List
{
  protected $_owner_type = 'user';

  protected $_child_type = 'user';

  public function getListItemTable()
  {
    return Engine_Api::_()->getItemTable('user_list_item');
  }

  public function getFriends(){
    $listTable = Engine_Api::_()->getItemTable('user_list');
    $this->view->lists = $lists = $listTable->fetchAll($listTable->select()->where('owner_id = ?', $viewer->getIdentity()));

    $listIds = array();
    foreach( $lists as $list ) {
      $listIds[] = $list->list_id;
    }

    // Build lists by user
    $listItems = array();
    $listsByUser = array();
    if( !empty($listIds) ) {
      $listItemTable = Engine_Api::_()->getItemTable('user_list_item');
      $listItemSelect = $listItemTable->select()
        ->where('list_id IN(?)', $listIds)
        ->where('child_id IN(?)', $ids);
      $listItems = $listItemTable->fetchAll($listItemSelect);
      foreach( $listItems as $listItem ) {
        //$list = $lists->getRowMatching('list_id', $listItem->list_id);
        //$listsByUser[$listItem->child_id][] = $list;
        $listsByUser[$listItem->child_id][] = $listItem->list_id;
      }
    }
  }


}