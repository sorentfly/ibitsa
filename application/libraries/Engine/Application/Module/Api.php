<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Api.php 10218 2014-05-15 15:43:12Z mfeineman $
 */

/**
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Engine_Application_Module_Api
{
  protected $_moduleName;

  protected $_lastResource;

  protected $_items;

  protected $_supportedItemTypes;

  public function api()
  {
    return Engine_Api::_()->setCurrentModule($this->getModuleName());
  }

  public function getModuleName()
  {
    if( empty($this->_moduleName) )
    {
      $class = get_class($this);
      if (preg_match('/^([a-z][a-z0-9]*)_/i', $class, $matches)) {
        $prefix = $matches[1];
      } else {
        $prefix = $class;
      }
      // @todo sanity
      $this->_moduleName = strtolower($prefix);
    }
    return $this->_moduleName;
  }

  public function __call($type, $args)
  {
    if( substr($type, 0, 3) === 'get' )
    {
      $type = preg_replace('/([A-Z])/', '-\\1', $type);
      $parts = explode('-', $type);
      array_shift($parts);
      $itemType = strtolower(array_shift($parts));
      $suffix = join('', $parts);
      if( $suffix === '' )
      {
        return $this->getItem($itemType, $args[0]);
      }
      else if( $suffix === 'Multi' )
      {
        return $this->getItemMulti($itemType, $args[0]);
      }
      else if( $suffix === 'MultiQuery' )
      {
        return $this->getItemMulti($itemType, $args[0], $args[1]);
      }
    }

    // Backwards
    $resource = array_shift($args);

    // Experimental
    if( !$resource )
    {
      if( !$this->_lastResource )
      {
        $this->_lastResource = $type;
        return $this;
      }

      else
      {
        $resource = $type;
        $type = $this->_lastResource;
        $this->_lastResource = null;
      }
    }

    $method = 'get'.ucfirst($type);
    return $this->api()->$method($resource);
    //return Engine_Api::_()->load(strtolower($this->getModuleName()), $type, $resource);
  }

  public function getItem($type, $identity)
  {
    $method = 'get' . ucfirst($type);
    if( method_exists($this, $method) )
    {
      return $this->$method($identity);
    }

    $this->_isTypeSupported($type);
    
    $id = $this->_getIdentity($type, $identity);
    if( !isset($this->_items[$type][$id]) )
    {
      if( !isset($this->_items[$type]) )
      {
        $this->_items[$type] = array();
      }
      $class = $this->api()->getItemClass($type);
      $item = new $class($identity);
      return $this->_items[$type][$item->getIdentity()] = $item;
    }

    return $this->_items[$type][$id];
  }


  public function getItemMulti($type, array $ids) 
  {
    $this->_isTypeSupported($type);
    
    // Remove any non-numeric values and already retv rows
    $getIds = array();
    foreach( $ids as $index => $value )
    {
      if( !is_numeric($value) )
      {
        unset($ids[$index]);
      }
      else if( !isset($this->_items[$type][$value]) )
      {
        $getIds[] = $value;
      }
    }

    // Now get any remaining rows, if necessary
    if( !empty($getIds) )
    {
      $table = $this->api()->getItemTable($type);
      $class = $this->api()->getItemClass($type);
      //$class = $this->getModuleName() . '_Model_' . ucfirst($type);
      foreach( $table->find($getIds) as $row )
      {
        $item = new $class($row);
        $this->_items[$type][$item->getIdentity()] = $item;
      }
    }

    // Now build the return data
    $items = array();
    foreach( $ids as $id )
    {
      if( isset($this->_items[$type][$id]) )
      {
        $items[] = $this->_items[$type][$id];
      }
    }

    return $items;
  }

  /*
   * Get all items of type $type that satisfy the query specified by $query_string and $query_args.
   * The format of $query_string and $query_args should be the same as for Zend_Db's select method
   */
  public function getItemMultiQuery($type, $text, $values, $order = NULL)
  {
    $this->_isTypeSupported($type);

    $table = $this->api()->getItemTable($type);
    if (!is_array($values)) {
      $values = Array($values);
    }
    foreach ($values as $value) 
    {
      $text = $table->getAdapter()->quoteInto($text, $value, NULL, 1);
    }
    $id_field_name = $type . '_id';
    $items = array();
    $select = $table->select()->where($text);
    if (!is_null($order)) 
    {
      $select = $select->order($order);
    }
    $rows = $table->fetchAll($select);
    foreach( $rows as $row )
    {
      $items[] = $this->getItem($type, $row);
    }
    return $items;  
  }

  public function getItemCountQuery($type, $text, $values) 
  {
    $this->_isTypeSupported($type);

    $table = $this->api()->getItemTable($type);
    if (!is_array($values)) {
      $values = Array($values);
    }
    foreach ($values as $value){
      $text = $table->getAdapter()->quoteInto($text, $value, NULL, 1);
    }

    $id_field_name = $type . '_id';
    $row = $table->fetchRow($table->select()->from($table->info('name'), 'count(*)')->where($text));
    return $row['count(*)'];
  }

  public function getOwnedItems($type, $owner_id, $owner_type = "user")
  {
    return $this->getItemMultiQuery($type, "owner_id = ? AND owner_type = ?", Array($owner_id, $owner_type));
  }

  /*
   * Creates a new item of type $type and inserts it into the appropriate Db table.
   * Each $key=>$value pair in $params indicates the desired value of a single table column  
   */
  protected function _createItem($type, $params)
  {
    $table = Engine_Api::_()->getItemTable($type);
    $row = $table->createRow();
    if( !isset($params['creation_date']) )
    {
      $params['creation_date'] = new Zend_Db_Expr('NOW()'); 
    }

    if( !isset($params['modified_date']) )
    {
      $params['modified_date'] = new Zend_Db_Expr('NOW()'); 
    }
    foreach( $params as $key => $value )
    {
      if( isset($row->$key) )
      {
        $row->$key = $value;
      }
    }

    // Pre-create hook
    //Engine_Hooks_Dispatcher::getInstance()
    //  ->call('on'.ucfirst($type).'CreateBefore', $row);

    // Create item
    $row->save();
    $id_field = $type . '_id';
    $item = $this->api()->getItem($type, $row->$id_field);

    // Post create hook
    //Engine_Hooks_Dispatcher::getInstance()
    //  ->call('on'.ucfirst($type).'CreateAfter', $item);

    return $item;
  }

  protected function _isTypeSupported($type)
  {
    if( !is_array($this->_supportedItemTypes) || !in_array($type, $this->_supportedItemTypes) )
    {
      //throw new Engine_Exception(sprintf('Type "%s" is no supported', $type));
    }
  }
  
  protected function _getIdentity($type, $identity)
  {
    if( is_scalar($identity) )
    {
      return $identity;
    }

    else if( $identity instanceof Zend_Db_Table_Row_Abstract )
    {
      $prop = $type . '_id';
      return $identity->$prop;
    }

    else if( $identity instanceof Core_Model_Item_Abstract )
    {
      return $identity->getIdentity();
    }

    else
    {
      throw new Engine_Exception('Not an identity');
    }
  }
}
