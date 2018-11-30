<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Level.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Authorization_Model_Level extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'user';
  
  protected $_parent_is_owner = true;
  
  protected $_searchTriggers = false;

  public function has($item)
  {
    if( $item instanceof Core_Model_Item_Abstract &&
        isset($item->level_id) &&
        !empty($item->level_id) &&
        $item->level_id > 0 ) {
      return ( $this->level_id == $item->level_id );
    } else {
      return ( 'public' == $this->type );
    }
  }

  public function getMembershipCount()
  {
    $userTable = Engine_Api::_()->getItemTable('user');
    $userTableName = $userTable->info('name');
    return (integer) $userTable->select()
      ->from($userTableName, new Zend_Db_Expr('COUNT(*)'))
      ->where('level_id = ?', $this->level_id)
      ->query()
      ->fetchColumn(0)
      ;
  }

  public function reassignMembers($level = null)
  {
    if( is_numeric($level) ) {
      $level = Engine_Api::_()->getItem('authorization_level', $level);
    } else if( is_object($level) && !empty($level->level_id) ) {
      // ok
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }

    if( !$level ) {
      throw new Authorization_Model_Exception('Unknown level');
    }

    $userTable = Engine_Api::_()->getItemTable('user');
    $userTable->update(array(
      'level_id' => $level->level_id,
    ), array(
      'level_id = ?' => $this->level_id,
    ));

    return $this;
  }

  public function removeAllPermissions()
  {
    Engine_Api::_()->getDbtable('permissions', 'authorization')->delete(array(
      'level_id = ?' => $this->level_id,
    ));

    return $this;
  }

}