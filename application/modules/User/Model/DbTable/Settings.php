<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Settings.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_Dbtable_Settings extends Engine_Db_Table
{
  public function getSetting(User_Model_User $user, $key)
  {
    return $this->select()
        ->from($this, 'value')
        ->where('user_id = ?', $user->getIdentity())
        ->where('name = ?', $key)
        ->query()
        ->fetchColumn();
  }

  public function getSettings(User_Model_User $user, $keys = null)
  {
    if( null === $keys ) {
      $data = $this->select()
          //->from($this)
          ->where('user_id = ?', $user->getIdentity())
          ->query()
          ->fetchAll();
    } else if( is_array($keys) && count($keys) > 1 ) {
      $data = $this->select()
          //->from($this)
          ->where('user_id = ?', $user->getIdentity())
          ->where('name IN(?)', (array) $keys)
          ->query()
          ->fetchAll();
    } else {
      return null;
    }

    $settings = array();
    foreach( $data as $row ) {
      $settings[$row['name']] = $row['value'];
    }

    return $settings;
  }
  
  public function setSetting(User_Model_User $user, $key, $value)
  {
    if( null === $value ) {
      $this->delete(array(
        'user_id = ?' => $user->getIdentity(),
        'name = ?' => $key,
      ));
    } else if( null === ($prev = $this->getSetting($user, $key)) ||
        false === $prev ) {
      $this->insert(array(
        'user_id' => $user->getIdentity(),
        'name' => $key,
        'value' => $value,
      ));
    } else {
      $this->update(array(
        'value' => $value,
      ), array(
        'user_id = ?' => $user->getIdentity(),
        'name = ?' => $key,
      ));
    }

    return $this;
  }

  public function setSettings(User_Model_User $user, $data)
  {
    $prev = $this->getSettings($user, array_keys($data));

    foreach( $data as $key => $value ) {
      if( null === $value ) {
        $this->delete(array(
          'user_id = ?' => $user->getIdentity(),
          'name = ?' => $key,
        ));
      } else if( isset($prev[$key]) && $prev[$key] !== $value ) {
        $this->update(array(
          'value' => $value,
        ), array(
          'user_id = ?' => $user->getIdentity(),
          'name = ?' => $key,
        ));
      } else if( !isset($prev[$key]) ) {
        $this->insert(array(
          'user_id' => $user->getIdentity(),
          'name' => $key,
          'value' => $value,
        ));
      }
    }

    return $this;
  }
}