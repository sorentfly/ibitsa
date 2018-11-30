<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Levels.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Authorization_Model_DbTable_Levels extends Engine_Db_Table
{
  protected $_rowClass = 'Authorization_Model_Level';

  protected $_publicLevel;

  protected $_defaultLevel;

  public function getPublicLevel()
  {
    if( null === $this->_publicLevel ) {
      $select = $this->select()
        ->where('type = ?', 'public')
        ->limit(1);
      $this->_publicLevel = $this->fetchRow($select);

      if( null === $this->_publicLevel ) {
        throw new Authorization_Model_Exception('No public level found');
      }
    }

    return $this->_publicLevel;
  }

  public function getDefaultLevel()
  {
    if( null === $this->_defaultLevel ) {
      $select = $this->select()
        ->where('flag = ?', 'default')
        ->limit(1);
      $this->_defaultLevel = $this->fetchRow($select);

      if( null === $this->_defaultLevel ) {
        throw new Authorization_Model_Exception('No default level found');
      }
    }

    return $this->_defaultLevel;
  }
  
  public function getLevelsAssoc()
  {
    $levels = $this->select()
        ->from($this, array('level_id', 'title'))
        ->order('level_id ASC')
        ->query()
        ->fetchAll();
    
    $data = array();
    foreach( $levels as $level ) {
      $data[$level['level_id']] = $level['title'];
    }
    
    return $data;
  }
}