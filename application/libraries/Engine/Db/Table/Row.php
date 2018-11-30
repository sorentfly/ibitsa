<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Row.php 10126 2014-01-15 20:42:09Z guido $
 */

/**
 * @category   Engine
 * @package    Engine_Db
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Db_Table_Row extends Zend_Db_Table_Row_Abstract 
{
  public function __construct(array $config)
  {
    // @todo Technically, we should have this run after serialization because
    // init will get called before unserialization
    parent::__construct($config);
      
    // Unserialize rows
    if( $this->_table instanceof Engine_Db_Table &&
        null !== ($cols = $this->_table->getSerializedColumns()) )
    {
      foreach( $cols as $colName ) {
        if( !empty($this->_data[$colName]) &&
            is_scalar($this->_data[$colName]) &&
            false != ($val = Zend_Json::decode($this->_data[$colName])) &&
            $val != $this->_data[$colName] ) {
          $this->_data[$colName] = $val;
        }
      }
    }
  }
  
  
  public function getModifiedFieldsCleanData(){
  	$cleanData = array();
  	foreach($this->_modifiedFields as $key=>$__){
  		$cleanData[$key] = isset($this->_cleanData[$key]) ? $this->_cleanData[$key] : null;
  	};
  	return $cleanData;
  }
  
}
