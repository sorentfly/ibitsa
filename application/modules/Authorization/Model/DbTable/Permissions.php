<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Permissions.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Authorization_Model_DbTable_Permissions extends Engine_Db_Table
  implements Authorization_Model_AdapterInterface
{
  public function getAdapterName()
  {
    return 'levels';
  }

  public function getAdapterPriority()
  {
    return 150;
  }

  public function isAllowed($resource, $role, $action)
  {
    // This is intended for lists
    if( is_object($role) && !empty($role->ignorePermCheck) ) {
      return Authorization_Api_Core::LEVEL_IGNORE;
    }

    // Trigger warning for string roles?
    //if( is_string($role) ) {
    //  trigger_error('String role permissions checking should query Authorzation_Model_DbTable_Allow directly.', E_USER_WARNING);
    //  Zend_Registry::get('Zend_Log')->log('String role permissions checking should query Authorzation_Model_DbTable_Allow directly.', Zend_Log::WARN);
    //}

    // Get
    $row = $this->_getAllowed($resource, $role, $action);
    // Row was not found
    if( !is_object($row) ) {
      return Authorization_Api_Core::LEVEL_IGNORE;
    }

    // Params not correct
    if( null === Authorization_Api_Core::getConstantKey($row->value) )
    {
      return Authorization_Api_Core::LEVEL_DISALLOW;
    }
    
    return $row->value;
  }

  public function getAllowed($resource, $role, $action)
  {
    $data = $this->_getAllowed($resource, $role, $action);
    $isMulti = ( null === $action || is_array($action) );

    // No data
    if( null === $data || ($data instanceof Countable || is_array($data)) && count($data) === 0 ) {
      return ( $isMulti ? array() : null );
    }

    // Improper data returned
    if( ($isMulti && !($data instanceof Zend_Db_Table_Rowset_Abstract)) ||
        (!$isMulti && !($data instanceof Zend_Db_Table_Row_Abstract)) ) {
      throw new Authorization_Model_Exception('improper data retrieved from _getAllowed()');
    }

    // return
    if( !$isMulti )
    {
      // Row is a non-boolean
      if( $data->value == Authorization_Api_Core::LEVEL_NONBOOLEAN )
      {
        return $data->params;
      }
      else if( $data->value == Authorization_Api_Core::LEVEL_SERIALIZED )
      {
        return Zend_Json::decode($data->params);
      }

      // Row is a allow/disallow
      return $data->value;
    }

    else
    {
      $rawData = array();
      foreach( $data as $row ) {
        // Row is a non-boolean
        if( $row->value == Authorization_Api_Core::LEVEL_NONBOOLEAN )
        {
          $rawData[$row->name] = $row->params;
        }
        else if( $row->value == Authorization_Api_Core::LEVEL_SERIALIZED )
        {
          $rawData[$row->name] = Zend_Json::decode($row->params);
        }
        // Row is a allow/disallow
        else
        {
          $rawData[$row->name] = $row->value;
        }
      }

      return $rawData;
    }
  }

  public function setAllowed($resource, $role, $action, $value = null)
  {
    // Can set multiple actions
    if( is_array($action) )
    {
      foreach( $action as $key => $value )
      {
        $this->setAllowed($resource, $role, $key, $value);
      }

      return $this;
    }

    // Check type
    $type = $this->_getResourceType($resource);
    if( !$type )
    {
      throw new Authorization_Model_Exception("Resource must be an instance of Core_Model_Item_Abstact or a resource type string");
    }

    // Role must be a level id or a user object with a level id
    if( $role instanceof Core_Model_Item_Abstract )
    {
      $level_id = ( isset($role->level_id) ? $role->level_id : 0 );
    }

    else if( is_numeric($role) )
    {
      $level_id = $role;
    }

    if( !$level_id )
    {
      throw new Authorization_Model_Exception("Role must be an instance of Core_Model_Item_Abstact with a level_id or a level id");
    }

    // If value not specified, set to disallow
    if( $value === null )
    {
      $value = Authorization_Api_Core::LEVEL_DISALLOW;
    }

    // Set info
    // Check for existing row
    $row = $this->fetchRow(array(
      'level_id = ?' => $level_id,
      'type = ?' => $type,
      'name = ?' => $action
    ));

    if( is_null($row) )
    {
      $row = $this->createRow();
      $row->level_id = $level_id;
      $row->type = $type;
      $row->name = $action;
    }
    
    if( null !== Authorization_Api_Core::getConstantKey($value) )
    {
      $row->value = $value;
      $row->params = null;
    }
    else
    {
      if( is_scalar($value) ) {
        $row->value = Authorization_Api_Core::LEVEL_NONBOOLEAN;
        $row->params = $value;
      } else if( is_array($value) ) {
        $row->value = Authorization_Api_Core::LEVEL_SERIALIZED;
        $row->params = Zend_Json::encode($value);
      } else {
        throw new Authorization_Model_Exception('non-scalar, non-array value passed to setAllowed()');
      }
    }
    
    $row->save();

    return $this;
  }



  // Utility

  protected function _getAllowed($resource, $role, $action)
  {
    // Must get a user object and a resource type
    $type = $this->_getResourceType($resource);
    if( null === $type )
    {
      return null;
    }

    // Role must be a level id or a user object with a level id
    if( $role instanceof Core_Model_Item_Abstract )
    {
      $level_id = ( isset($role->level_id) ? $role->level_id : 0 );
    }

    else if( is_numeric($role) )
    {
      $level_id = $role;
    }

    if( empty($level_id) )
    {
      $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
    }

    // Lookup permission
    $select = $this->select()
      ->where('level_id = ?', $level_id)
      ->where('type = ?', $type)
      ;

    $return = null;
    if( null === $action ) {
      // get everything?
      $return = $this->fetchAll($select);
    } else if( is_array($action) ) {
      if( empty($action) ) {
        // get everything?
        $return = $this->fetchAll($select);
      } else {
        $select->where('name IN(?)', $action);
        $return = $this->fetchAll($select);
      }
    } else if( is_scalar($action) ) {
      $select->where('name = ?', $action);
      $return = $this->fetchRow($select);
    } else {
      throw new Authorization_Model_Exception('invalid action passed to _getAllowed()');
    }
    
    return $return;
  }
  
  protected function _getLevel($level)
  {
    if( is_numeric($level) ) {
      return $level;
    } else if( $level instanceof Zend_Db_Table_Row_Abstract && isset($level->level_id) && is_numeric($level->level_id) ) {
      return $level->level_id;
    } else {
      throw new Authorization_Model_Exception('Invalid level id');
    }
  }
  
  protected function _getResourceType($resource)
  {
    if( is_string($resource) )
    {
      return $resource;
    }

    else if( is_array($resource) && isset($resource[0]) )
    {
      return $resource[0];
    }

    else if( $resource instanceof Core_Model_Item_Abstract )
    {
      return $resource->getType();
    }

    else
    {
      return null;
    }
  }
  
  protected function _getResourceIdentity($resource)
  {
    if( is_numeric($resource) )
    {
      return $resource;
    }

    else if( is_array($resource) && isset($resource[1]) )
    {
      return $resource[1];
    }

    else if( $resource instanceof Core_Model_Item_Abstract )
    {
      return $resource->getIdentity();
    }

    else
    {
      return null;
    }
  }
}