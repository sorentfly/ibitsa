<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Mysql.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Db_Export_Mysql extends Engine_Db_Export
{
  const EOQ = ';';
  
  protected function _fetchHeader()
  {
    $adapter = $this->getAdapter();
    return
      $this->_fetchComment() . PHP_EOL .
      $this->_fetchComment() . PHP_EOL .
      $this->_fetchComment('SocialEngine v4 Backup') . PHP_EOL .
      $this->_fetchComment('http://www.socialengine.com') . PHP_EOL .
      $this->_fetchComment() . PHP_EOL .
      $this->_fetchComment() . PHP_EOL .
      PHP_EOL . PHP_EOL .
      '/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */' . self::EOQ . PHP_EOL .
      '/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */' . self::EOQ . PHP_EOL .
      '/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */' . self::EOQ . PHP_EOL .
      PHP_EOL . PHP_EOL .
      $adapter->quoteInto('SET NAMES ?', 'utf8') . self::EOQ . PHP_EOL .
      $adapter->quoteInto('SET foreign_key_checks = ?', 0) . '0;' . self::EOQ . PHP_EOL .
      $adapter->quoteInto('SET time_zone = ?', '+0:00') . self::EOQ . PHP_EOL .
      $adapter->quoteInto('SET sql_mode = ?', 'NO_AUTO_VALUE_ON_ZERO') . self::EOQ . PHP_EOL .
      PHP_EOL . PHP_EOL
    ;
  }

  protected function _fetchFooter()
  {
    return PHP_EOL . PHP_EOL .
      '/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;' . PHP_EOL .
      '/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;' . PHP_EOL .
      '/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;'
    ;
  }
  
  protected function _fetchComment($comment = '')
  {
    if( empty($comment) ) {
      return '--';
    }

    if( strpos($comment, "\n") === false && strpos($comment, "\r") === false ) {
      return '-- ' . $comment;
    }

    if( strpos($comment, '/*') !== false ) {
      $comment = str_replace('/*', '', $comment);
    }

    if( strpos($comment, '*/') !== false ) {
      $comment = str_replace('*/', '', $comment);
    }
    
    return '/*' . PHP_EOL . $comment . PHP_EOL . '*/';
  }

  protected function _fetchTableSchemaHeader($table)
  {
    return
      $this->_fetchComment('--------------------------------------------------------') . PHP_EOL .
      PHP_EOL .
      $this->_fetchComment() . PHP_EOL .
      $this->_fetchComment('Table structure for ' . $this->getAdapter()->quoteIdentifier($table)) . PHP_EOL .
      $this->_fetchComment() . PHP_EOL .
      PHP_EOL
      ;
  }

  protected function _fetchTableSchema($table)
  {
    $adapter = $this->getAdapter();

    $quotedTable = $this->getAdapter()->quoteIdentifier($table);
    $result = $this->_queryRaw('SHOW CREATE TABLE ' . $quotedTable);
    $result = $result[0]['Create Table'];

    $output = '';
    
    if( $this->getParam('dropTable', true) ) {
      $output .= 'DROP TABLE IF EXISTS ' . $quotedTable . self::EOQ . PHP_EOL;
    }

    $output .= $result;
    $output .= self::EOQ . PHP_EOL . PHP_EOL;
    
    return $output;
  }

  protected function _fetchTableDataHeader($table)
  {
    return
      $this->_fetchComment() . PHP_EOL .
      $this->_fetchComment('Dumping data for table ' . $this->getAdapter()->quoteIdentifier($table)) . PHP_EOL .
      $this->_fetchComment() . PHP_EOL .
      PHP_EOL
      ;
  }

  protected function _fetchTableData($table)
  {
    $adapter = $this->getAdapter();
    $quotedTable = $this->getAdapter()->quoteIdentifier($table);

    $output = '';
    
    // Get data
    $sql = 'SELECT * FROM ' . $quotedTable;
    $stmt = $adapter->query($sql);
    $first = true;
    $columns = null;
    $written = 0;
    
    while( false != ($row = $stmt->fetch()) ) {

      // Add insert
      if( !$this->getParam('insertExtended', true) || $first ) {
        $output .= 'INSERT ';
        if( $this->getParam('insertIgnore', false) ) {
          $output .= 'IGNORE ';
        }
        $output .= 'INTO ' . $quotedTable . ' ';
        // Complete
        if( $this->getParam('insertComplete', true) ) {
          if( empty($columns) ) {
            $columns = implode(', ', array_map(array($adapter, 'quoteIdentifier'), array_keys($row)));
          }
          $output .= '(' . $columns . ') ';
          $output .= 'VALUES ';
        }
        $output .= PHP_EOL;
      }
      // Other wise we are continuing a previous query
      else {
        $output .= ',';
        $output .= PHP_EOL;
      }

      // Add data
      $data = array();
      foreach( $row as $key => $value ) {
        if( null === $value ) {
          $data[$key] = 'NULL';
        } else {
          $data[$key] = $adapter->quote($value);
        }
      }
      $output .= '(' . implode(', ', $data) . ')';

      // Save to file
      if( !empty($output) ) {
        $written++;
        $this->_write($output);
        $output = '';
      }

      $first = false;
    }
    
    // Finish up
    if( $written ) {
      $output .= self::EOQ . PHP_EOL . PHP_EOL;
    }
    $output .= PHP_EOL;

    if( !empty($output) ) {
      $this->_write($output);
      $output = '';
    }
  }

  protected function _fetchTables()
  {
    return array_values($this->getAdapter()->fetchAll('SHOW TABLES'));
  }

  protected function _queryRaw($sql)
  {
    return $this->getAdapter()->fetchAll($sql);
  }
}