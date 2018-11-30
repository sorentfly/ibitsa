<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: MysqlEngine.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_MysqlEngine extends Engine_Sanity_Test_Abstract
{
  protected $_messageTemplates = array(
    'badAdapter' => 'Unable to check. No database adapter was provided.',
    'badResult' => 'Unable to check. The query could not be run. You may not have permission to run SHOW ENGINES.',
    'engineDisabled' => 'The MySQL storage engine has been disabled.',
    'engineMissing' => 'The MySQL storage engine is not installed.',
  );

  protected $_messageVariables = array(
    'engine' => '_engine',
  );

  protected $_adapter;

  protected $_engine;

  public function setAdapter($adapter)
  {
    if( $adapter instanceof Engine_Db_Adapter_Mysql ||
        $adapter instanceof Zend_Db_Adapter_Mysqli ||
        $adapter instanceof Zend_Db_Adapter_Pdo_Mysql ) {
      $this->_adapter = $adapter;
    }
    return $this;
  }

  public function getAdapter()
  {
    if( null === $this->_adapter ) {
      if( null !== ($defaultAdapter = Engine_Sanity::getDefaultDbAdapter()) ) {
        $this->_adapter = $defaultAdapter;
      }
    }
    return $this->_adapter;
  }

  public function setEngine($minVersion)
  {
    $this->_engine = $minVersion;
    return $this;
  }

  public function getEngine()
  {
    return $this->_engine;
  }

  public function execute()
  {
    $adapter = $this->getAdapter();
    $engine = $this->getEngine();

    // Check engine
    if( empty($engine) || (!is_string($engine) && !is_array($engine)) ) {
      return;
    }
    
    // Check adapter
    if( !$adapter ) {
      return $this->_error('badAdapter');
    }

    // Try to list engines
    if( $adapter instanceof Zend_Db_Adapter_Mysqli ){
      // Fixes MySQLI segfault in fetch_fields() with SHOW ENGINES
      $connection = $adapter->getConnection();
      $result = mysqli_query($connection, 'SHOW ENGINES');
      if ( !($result instanceof mysqli_result) ){
        return $this->_error('badResult');
      }
      
      $data = array();
      while ( $row = $result->fetch_array() ){
        $data[] = $row;
      }
      
      if( empty($data) ) {
        return $this->_error('badResult');
      }
    } else {
      try {
        $data = $adapter->query('SHOW ENGINES')->fetchAll();
        if( empty($data) ) {
          return $this->_error('badResult');
        }
      } catch( Exception $e ) {
        return $this->_error('badAdapter');
      }
    }

    // Format engines
    $engine = (array) $engine;
    $engine = array_map('strtoupper', $engine);

    // Process results
    $found = false;
    $foundDisabled = false;
    $foundMissing = false;
    foreach( $data as $row ) {
      if( in_array(strtoupper($row['Engine']), $engine) ) {
        switch( strtoupper($row['Support']) ) {
          case 'DEFAULT':
            $found = true;
            break;
          case 'YES':
            $found = true;
            break;
          case 'NO':
            $foundMissing = true;
            break;
          case 'DISABLED':
            $foundDisabled = true;
            break;
          default:
            break;
        }
      }
    }

    if( !$found ) {
      if( $foundDisabled ) {
        return $this->_error('engineDisabled');
      } else {
        return $this->_error('engineMissing');
      }
    }
  }
}