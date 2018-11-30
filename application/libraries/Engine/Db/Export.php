<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Export.php 10184 2014-04-30 14:46:40Z andres $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
abstract class Engine_Db_Export
{
  /**
   * @var Zend_Db_Adapter_Abstract
   */
  protected $_adapter;

  /**
   * @var array
   */
  protected $_params = array(
    'dropTable' => true,
    'fetchStructure' => true,
    'fetchData' => true,
    'fullInserts' => false,
    'insertIgnore' => false,
    'insertComplete' => true,
    'insertExtended' => true,
    'comments' => true,
  );

  protected $_tables;

  protected $_handle;

  protected $_mode;

  protected $_data;

  protected $_tableIndex;

  protected $_listeners;
  
  public static function factory(Zend_Db_Adapter_Abstract $adapter, $options = array())
  {
    list($prefix, $type) = explode('_Db_Adapter_', get_class($adapter));
    $class = 'Engine' . '_Db_Export_' . $type;
    Engine_Loader::loadClass($class);
    $instance = new $class($adapter, $options);
    if( !($instance instanceof Engine_Db_Export) ) {
      throw new Engine_Exception('Must be an instance of Engine_Db_Export');
    }
    return $instance;
  }

  public function __construct(Zend_Db_Adapter_Abstract $adapter, $options = array())
  {
    if( !$adapter->isConnected() ) {
      throw new Engine_Exception('Adapter not connected');
    }
    
    $this->_adapter = $adapter;

    if( is_array($options) ) {
      $this->setOptions($options);
    }
  }

  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      } else {
        $this->_params[$key] = $value;
      }
    }
    return $this;
  }

  public function setTables(array $tables)
  {
    $this->_tables = $tables;
    return $this;
  }

  public function getTables()
  {
    if( null === $this->_tables ) {
      $this->_tables = $this->_fetchTables();
    }
    return $this->_tables;
  }

  public function setParams(array $params)
  {
    $this->_params = array_merge((array)$this->_params, $params);
    return $this;
  }

  public function getParams()
  {
    return $this->_params;
  }

  public function setParam($key, $value)
  {
    $this->_params[$key] = $value;
    return $this;
  }

  public function getParam($key, $default = null)
  {
    if( !isset($this->_params[$key]) ) {
      return $default;
    }
    return $this->_params[$key];
  }

  /**
   * Get the adapter
   * 
   * @return Zend_Db_Adapter_Abstract
   */
  public function getAdapter()
  {
    if( null === $this->_adapter ) {
      throw new Engine_Exception('No Db Adapter');
    }
    return $this->_adapter;
  }



  // Listeners

  public function getListerners()
  {
    return (array) $this->_listeners;
  }
  
  public function setListener(Engine_Observer_Interface $listener)
  {
    $this->_listeners[] = $listener;
    return $this;
  }

  public function setListeners(array $listeners)
  {
    $this->_listeners = array();
    foreach( $listeners as $listener ) {
      $this->_listeners[] = $listener;
    }
    return $this;
  }

  protected function _announce($event)
  {
    foreach( $this->getListerners() as $listener ) {
      $listener->notify($event);
    }
    return $this;
  }



  // Write

  public function write($file)
  {
    if( is_string($file) ) {
      if( !($handle = fopen($file, 'w')) ) {
        throw new Engine_Exception(sprintf('Unable to write to file: "%s"'), $file);
      }
      $this->_handle = $handle;
    } else if( is_resource($file) ) {
      $this->_handle = $handle = $file;
    } else {
      throw new Engine_Exception(sprintf('Unable to write to file, given: "%s"', gettype($file)));
    }

    $this->_mode = 'file';

    try {

      $this->_generate();
      
    } catch( Exception $e ) {
      fclose($handle);
      @unlink($file);
      $this->_mode = null;
      $this->_handle = null;
      throw $e;
    }

    fclose($handle);
    $this->_mode = null;
    $this->_handle = null;

    return $this;
  }

  public function toString()
  {
    $this->_mode = 'string';

    try {

      $this->_generate();

    } catch( Exception $e ) {
      $this->_data = null;
      $this->_mode = null;
      throw $e;
    }

    $data = $this->_data;
    $this->_data = null;
    $this->_mode = null;

    return $data;
  }

  final protected function _generate()
  {
    // Announce
    $this->_announce('onDatabaseExportStart');

    // Header
    $this->_write($this->_fetchHeader());

    // Tables
    $this->_tableIndex = 0;
    foreach( $this->getTables() as $table )
    {
      if( $this->getParam('fetchStructure', true) ) {
        // Table schema header
        $this->_write($this->_fetchTableSchemaHeader($table));

        // Table schema
        $this->_write($this->_fetchTableSchema($table));
      }

      if( $this->getParam('fetchData', true) ) {
        // Table data header
        $this->_write($this->_fetchTableDataHeader($table));

        // Table data
        $this->_write($this->_fetchTableData($table));
      }

      $this->_tableIndex++;

      // Announce
      $this->_announce('onDatabaseExportProgress');
    }

    // Footer
    $this->_write($this->_fetchFooter());
    
    // Announce
    $this->_announce('onDatabaseExportEnd');
  }

  final protected function _write($output)
  {
    if( is_string($output) ) {
      switch( $this->_mode ) {
        case 'file':
          fwrite($this->_handle, $output);
          break;
        case 'string':
          $this->_data .= $output;
          break;
      }
    }
  }



  // Progress

  public function getTableCount()
  {
    return count($this->getTables());
  }

  public function getTableIndex()
  {
    return $this->_tableIndex;
  }



  // Abstract
  
  abstract protected function _fetchHeader();

  abstract protected function _fetchFooter();

  abstract protected function _fetchComment($comment = '');

  abstract protected function _fetchTables();

  abstract protected function _fetchTableSchemaHeader($table);

  abstract protected function _fetchTableSchema($table);

  abstract protected function _fetchTableDataHeader($table);
  
  abstract protected function _fetchTableData($table);

  abstract protected function _queryRaw($sql);
}
