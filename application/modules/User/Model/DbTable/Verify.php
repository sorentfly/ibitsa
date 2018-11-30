<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Verify.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_DbTable_Verify extends Engine_Db_Table
{
  public function gc()
  {
    // Delete rows for users that are verified or the user does not exist
    $verifyTableName = $this->info('name');
    $userTable = Engine_Api::_()->getItemTable('user');
    $userTableName = $userTable->info('name');
    $select = new Zend_Db_Select($this->getAdapter());
    $select
      ->from($verifyTableName, null)
      ->joinLeft($userTableName, $userTableName . '.user_id=' . $verifyTableName . '.user_id', array('verified', 'user_id'))
      ->where($userTableName . '.verified = ?', 1);

    $gcIds = array();
    foreach( $select->query()->fetchAll() as $row ) {
      if( !empty($row['verified']) || empty($row['user_id']) ) {
        $gcIds[] = $row['user_id'];
      }
    }

    // Delete them
    if( !empty($gcIds) ) {
      $this->delete(array(
        'user_id IN(?)' => $gcIds,
      ));
    }

    return $this;
  }
}