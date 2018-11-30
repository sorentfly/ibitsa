<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Rowset.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Db_Table_Rowset extends Zend_Db_Table_Rowset_Abstract
{
  protected $_rowClass = 'Engine_Db_Table_Row';
  protected $_rowsetClass = 'Engine_Db_Table_Rowset';

  public function __wakeup()
  {
    try {
      $table = Engine_Api::_()->loadClass($this->_tableClass);
      $this->_table = $table;
      $this->_connected = true;
    } catch( Exception $e ) {
      $this->_connected = false;
    }
  }

  /**
   * Creates a new row inside the rowset
   *
   * @return Engine_Db_Table_Row
   * @throws Zend_Db_Table_Rowset_Exception If $this->_table isn't a table object
   */
  public function createRow()
  {
    if( !($this->_table instanceof Zend_Db_Table_Abstract) ) {
      throw new Zend_Db_Table_Rowset_Exception("Unable to create new row in rowset without a table");
    }

    $index = count($this->_data);
    $row = $this->_table->createRow();
    $this->_data[$index] = $row->toArray();
    $this->_rows[$index] = $row;
    $this->_count = count($this->_data);
    return $row;
  }

  /**
   * See parent::current() Overridden to inject dynamic row class logic
   *
   * @return Engine_Db_Table_Row
   */
  public function current()
  {
    if( $this->valid() === false )
    {
      return null;
    }
    
    return $this->_getRow($this->_pointer);
  }

  /**
   * Returns or instantiates a row
   * 
   * @return Engine_Db_Table_Row
   */
  protected function _getRow($pointer)
  {
    if( empty($this->_rows[$pointer]) )
    {

      $rowClass = $this->_getRowClass();
      $this->_rows[$pointer] = new $rowClass(
        array(
          'table'    => $this->_table,
          'data'     => $this->_data[$pointer],
          'stored'   => $this->_stored,
          'readOnly' => $this->_readOnly
        )
      );
    }
    return $this->_rows[$pointer];
  }

  /**
   * Calculates the dynamic row class name based on the requested row's value,
   * or returns the default row class name if out of bounds
   *
   * @return string
   */
  protected function _getRowClass()
  {
    return $this->_rowClass;
  }


  // Associative/Primary/Lookup

  /**
   * Get a row matching specification. Accepts $key, $value or an array of
   * keys and values
   * 
   * @return Engine_Db_Table_Row
   * @throws Zend_Db_Table_Rowset_Exception On bad arguments
   */
  public function getRowMatching()
  {
    $num = func_num_args();
    $args = func_get_args();

    // We are passing in a single column/value
    if( $num == 2 )
    {
      $cols = array($args[0] => $args[1]);
    }

    // We are passing in an array
    else if( $num == 1 && is_array($args[0]) )
    {
      $cols = $args[0];
    }

    // Wth is this
    else
    {
      throw new Zend_Db_Table_Rowset_Exception("Malformed arguments");
    }

    for( $i = 0, $l = count($this->_data); $i < $l; $i++ )
    {
      $row = $this->_getRow($i);
      foreach( $cols as $colName => $colValue )
      {
        if( !isset($row->$colName) || $row->$colName != $colValue )
        {
          continue 2;
        }
      }
      return $row;
    }

    return null;
  }

  /**
   * Get all rows matching specification. Accepts $key, $value or an array of
   * keys and values
   *
   * @return array of Engine_Db_Table_Row
   * @throws Zend_Db_Table_Rowset_Exception On bad arguments
   */
  public function getRowsMatching()
  {
    $num = func_num_args();
    $args = func_get_args();

    // We are passing in a single column/value
    if( $num == 2 )
    {
      $cols = array($args[0] => $args[1]);
    }

    // We are passing in an array
    else if( $num == 1 && is_array($args[0]) )
    {
      $cols = $args[0];
    }

    // Wth is this
    else
    {
      throw new Zend_Db_Table_Rowset_Exception("Malformed arguments");
    }

    $data = array();
    for( $i = 0, $l = count($this->_data); $i < $l; $i++ )
    {
      $row = $this->_getRow($i);
      foreach( $cols as $colName => $colValue )
      {
        if( !isset($row->$colName) || $row->$colName != $colValue )
        {
          continue 2;
        }
      }
      $data[] = $row;
    }

    return $data;
  }

  /**
   * Convert to array
   * 
   * @return array[]
   */
  public function toArray()
  {
    foreach( $this->_rows as $i => $row ) {
      $this->_data[$i] = $row->toArray();
    }

    ksort($this->_data);

    return $this->_data;
  }
    /**
     * Convert to row array
     *
     * @return Engine_Db_Table_Row[]
     */
  public function rowArray() {
        $rowArray = array();
        foreach ($this as $row) {
            $rowArray[] = $row;
        }
        return $rowArray;
  }
}