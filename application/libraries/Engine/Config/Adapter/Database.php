<?php

class Engine_Config_Adapter_Database extends Engine_Config_Adapter_Abstract
{
  /**
   * @var Zend_Db_Adapter_Abstract
   */
  protected $_database;
  
  /**
   * @var array
   */
  protected $_map = array(
    'key' => 'name',
    'value' => 'value',
  );
  
  /**
   * @var string
   */
  protected $_table;
  
  
  
  // Main
  
  public function __construct(array $options = null)
  {
    parent::__construct($options);
    
    if( null === $this->_database ) {
      throw new Engine_Config_Adapter_Exception('No database configured');
    }
    if( null === $this->_table ) {
      throw new Engine_Config_Adapter_Exception('No table configured');
    }
    
    $this->_load();
  }
  
  
  
  // Options
  
  public function getDatabase()
  {
    return $this->_database;
  }
  
  public function setDatabase(Zend_Db_Adapter_Abstract $database)
  {
    $this->_database = $database;
    return $this;
  }
  
  public function getMap($key = null)
  {
    if( null === $key ) {
      return $this->_map;
    } else if( isset($this->_map[$key]) ) {
      return $this->_map[$key];
    } else {
      return null;
    }
  }
  
  public function setMap(array $map)
  {
    $defaults = array(
      'key' => 'name',
      'value' => 'value',
    );
    $this->_map = array_merge($defaults, array_intersect_key($map, $defaults));
    return $this;
  }
  
  public function getTable()
  {
    return $this->_table;
  }
  
  public function setTable($table)
  {
    if( is_string($table) ) {
      $this->_table = $table;
    } else if( $table instanceof Zend_Db_Table_Abstract ) {
      $this->_table = $table->info('name');
    } else {
      throw new Engine_Config_Adapter_Exception('Invalid table data type: ' . gettype($table));
    }
    
    return $this;
  }
  


  // Abstract
  
  public function get($key, $default = null)
  {
    if( null === $this->_data ) {
      $this->_load();
    }
    
    if( isset($this->_data[$key]) ) {
      return $this->_data[$key];
    } else {
      return null;
    }
  }
  
  public function has($key)
  {
    if( null === $this->_data ) {
      $this->_load();
    }
    
    return isset($this->_data[$key]);
  }
  
  public function set($key, $value)
  {
    if( null === $this->_data ) {
      $this->_load();
    }
    
    // Set in local cache
    $noInsert = isset($this->_data[$key]) && $value == $this->_data[$key];
    $this->_data[$key] = $value;
    
    // Set in database also
    $count = $this->_database->update($this->_table, array(
      $this->_map['value'] => $value,
    ), array(
      $this->_map['key'] . ' = ?' => $key,
    ));
    
    if( $count <= 0 && !$noInsert ) {
      $this->_database->insert($this->_table, array(
        $this->_map['key'] => $key,
        $this->_map['value'] => $value,
      ));
    }
    
    // Set in cache also -_-
    $this->_saveCache();
    
    return $this;
  }
  
  public function remove($key)
  {
    if( null === $this->_data ) {
      $this->_load();
    }
    
    // Unset in local cache
    unset($this->_data[$key]);
    
    // Unset in database also
    $this->_database->delete($this->_table, array(
      $this->_map['value'] . ' = ?' => $value,
    ));
    
    // Unset in cache also -_-
    $this->_saveCache();
    
    return $this;
  }
  
  
  
  // Utility
  
  protected function _load()
  {
    if( null !== $this->_cache ) {
      $data = $this->_cache->load($this->_cacheKey);
      if( is_string($data) ) {
        $data = unserialize($data);
        if( !is_array($data) ) {
          $data = null;
        }
      } else {
        $data = null;
      }
      $this->_data = $data;
    }
    
    if( null === $this->_data ) {
      $data = $this->_database->select()
          ->from($this->_table)
          ->query()
          ->fetchAll();
      $this->_data = array();
      if( is_array($data) ) {
        foreach( $data as $row ) {
          $this->_data[$row[$this->_map['key']]] = $row[$this->_map['value']];
        }
      }
      
      $this->_saveCache();
    }
  }
  
  protected function _saveCache()
  {
    if( null !== $this->_cache ) {
      $this->_cache->save(serialize($this->_data), $this->_cacheKey);
    }
  }
}