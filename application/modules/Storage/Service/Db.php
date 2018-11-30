<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Db.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Service_Db extends Storage_Service_Abstract
{
  // General

  protected $_type = 'db';

  protected $_adapter;

  protected $_table;

  protected $_chunkSize = 32768;

  public function __construct(array $config)
  {
    // Use specified database
    if( /* !empty($config['adapter']) && */
        !empty($config['host']) &&
        !empty($config['username']) &&
        !empty($config['password']) ) {
      $defaultAdapter = Engine_Api::_()->getDbtable('chunks', 'storage')->getAdapter();
      $adapterName = strtolower( !empty($config['adapter']) ? $config['adapter'] : array_pop(explode('_', get_class($defaultAdapter))) );
      $adapterNamespace = ( $adapterName == 'mysql' ? 'Engine_Db_Adapter' : 'Zend_Db_Adapter' );
      $this->_adapter = Zend_Db::factory($adapterName, array(
        'host' => $config['host'],
        'username' => $config['username'],
        'password' => $config['password'],
        'dbname' => $config['dbname'],
        'adapterNamespace' => $adapterNamespace,
      ));
      $this->_table = new Storage_Model_DbTable_Chunks(array(
        'adapter' => $this->_adapter,
        //'name' => 'engine4_storage_chunks',
      ));
    }
    
    // Use local database
    else {
      $this->_table = Engine_Api::_()->getDbtable('chunks', 'storage');
      $this->_adapter = $this->_table->getAdapter();
    }
    
    parent::__construct($config);
  }

  public function getType()
  {
    return $this->_type;
  }

  public function getTable()
  {
    if( null === $this->_table ) {
      $this->_table = Engine_Api::_()->getDbtable('chunks', 'storage');
    }

    return $this->_table;
  }

  public function getChunkSize()
  {
    return $this->_chunkSize;
  }


  // Accessors

  public function map(Storage_Model_File $model)
  {
    return Zend_Controller_Front::getInstance()->getRouter()
            ->assemble(array('module' => 'storage', 'controller' => 'index', 'action' => 'serve', 'file' => $model->getIdentity()), 'default', true);
  }

  public function store(Storage_Model_File $model, $file)
  {
    $table = $this->getTable();
    //$db = $table->getAdapter();
    //$db->beginTransaction();

    try
    {
      // Delete existing chunks
      $this->_remove($model);

      // Begin writing new chunks
      if( !($handle = fopen($file, 'rb')) ) {
        throw new Storage_Service_Exception('Unable to open file for storage');
      }

      $chunkLength = $this->getChunkSize();
      while( $data = fread($handle, $chunkLength) ) {
        $this->_write($model, $data);
      }
      
      //$db->commit();
    }

    catch( Exception $e )
    {
      //$db->rollBack();
      $this->_remove($model);
      throw $e;
    }

    return $model->getIdentity();
  }

  public function read(Storage_Model_File $model)
  {
    return $this->_read($model);
  }

  public function write(Storage_Model_File $model, $data)
  {
    $table = $this->getTable();
    //$db = $table->getAdapter();
    //$db->beginTransaction();

    try
    {
      // Delete existing chunks
      $this->_remove($model);

      // Begin writing new chunks
      $length = strlen($data);
      $chunkLength = $this->getChunkSize();
      $chunks = ceil($length / $chunkLength);

      for( $i = 0; $i < $chunks; $i++ )
      {
        $segment = substr($data, $i * $chunkLength, $chunkLength);
        $this->_write($model, $data);
      }
      
      //$db->commit();
    }

    catch( Exception $e )
    {
      //$db->rollBack();
      $this->_remove($model);
      throw $e;
    }

    return $model->getIdentity();
  }

  public function remove(Storage_Model_File $model)
  {
    $this->_remove($model);
  }

  public function temporary(Storage_Model_File $model)
  {
    $tmp_file = APPLICATION_PATH . '/public/temporary/storage/'.$model->getIdentity().'.'.$model->extension;
    $this->_mkdir(dirname($tmp_file));
    if( !$handle = fopen($tmp_file, 'wb') )
    {
      throw new Storage_Service_Exception('Unable to write to temporary file');
    }
    
    $i = 0;
    while( $string = $this->_read($model, $i++) )
    {
      fwrite($handle, $string);
    }
    
    return $tmp_file;
  }


  public function removeFile($path)
  {
    $this->_delete($path);
  }



  // Utilities
  
  protected function _remove($file)
  {
    if( $file instanceof Storage_Model_File )
    {
      $file = $file->getIdentity();
    }
    if( !is_numeric($file) )
    {
      throw new Storage_Service_Exception('Invalid argument passed to remove');
    }
    $this->getTable()->delete(array(
      'file_id = ?' => $file,
    ));
  }

  protected function _write($file, $data)
  {
    if( $file instanceof Storage_Model_File )
    {
      $file = $file->getIdentity();
    }
    if( !is_numeric($file) )
    {
      throw new Storage_Service_Exception('Invalid argument passed to remove');
    }
    if( strlen($data) > $this->getChunkSize() )
    {
      throw new Storage_Service_Exception('Data specified is greater than chunk length');
    }
    $this->getTable()->insert(array(
      'file_id' => $file,
      'data' => $data,
    ));
  }

  protected function _read($file, $index = null)
  {
    if( $file instanceof Storage_Model_File )
    {
      $file = $file->getIdentity();
    }
    if( !is_numeric($file) )
    {
      throw new Storage_Service_Exception('Invalid argument passed to remove');
    }

    $buf = '';
    $table = $this->getTable();
    $select = $table->select()
      ->where('file_id = ?', $file)
      ->order('chunk_id ASC');

    if( null !== $index ) {
      $select->limit(1, $index);
    }

    foreach( $table->fetchAll($select) as $chunk )
    {
      $buf .= $chunk->data;
    }

    return $buf;
  }
}