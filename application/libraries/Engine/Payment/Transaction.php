<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Transaction.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Payment_Transaction
{
  // Constants

  // General
  const TYPE = 'type';                // The transaction type
  const PRICE = 'price';              // The total of the transactions
  const TANGIBLE = 'tangible';        // Does this contain physical items
  const LANGUAGE = 'language';        // The language of the customer
  const REGION = 'region';            // The region of the customer
  const CURRENCY = 'currency';        // The currency of the transaction

  // URLs
  const RETURN_URL = 'return_url';    // URL to return to on success
  const CANCEL_URL = 'cancel_url';    // URL to return to on failure
  const IPN_URL = 'ipn_url';          // URL to send IPN to

  // Vendor
  const VENDOR_ID = 'vendor_id';      // The identity of the vendor (us)
  const VENDOR_ORDER_ID = 'vendor_order_id'; // Custom order id # for this order

  // Transaction
  const TRANSACTION_AMOUNT = 'amount';
  const TRANSACTION_DESC = 'tranaction_desc';
  const TRANSACTION_VENDOR_ID = 'transaction_vendor_id';
  
  // Items
  const ITEM_COUNT = 'item_count';    // The number of items in the transaction
  const ITEMS = 'items';              // An array of items in this transaction

  // Products
  const PRODUCT_ID = 'product_id';    // The identity of the product
  const PRODUCT_TITLE = 'product_title';
  const PRODUCT_DESCRIPTION = 'product_description';
  const PRODUCT_QUANTITY = 'product_quantity';
  const PRODUCT_PRICE = 'product_price';
  
  // Recurring/Subscription
  const RECURRENCE = 'recurrence';    // How often is this charged?
  const DURATION = 'duration';        // For how long is this charged?

  // Extra Options
  const TEST_MODE = 'test_mode';      // Testing
  const UI_FIXED = 'ui_fixed';        // Customer can't change items
  const UI_SKIP_LANDING = 'ui_skip_landing';

  // Customer
  const CUSTOMER_NAME = 'customer_name';
  const CUSTOMER_NAME_FIRST = 'customer_name_first';
  const CUSTOMER_NAME_MIDDLE = 'customer_name_middle';
  const CUSTOMER_NAME_LAST = 'customer_name_last';
  const CUSTOMER_ADDRESS = 'customer_address';
  const CUSTOMER_ADDRESS2 = 'customer_address2';
  const CUSTOMER_CITY = 'customer_city';


  // Static Properties

  static protected $_supportedKeys;



  // Properties
  
  /**
   * @var string
   */
  protected $_driver;

  /**
   * @var array
   */
  protected $_rawData;

  /**
   * @var array
   */
  protected $_data;

  /**
   * @var boolean
   */
  protected $_isValid = false;

  /**
   * @var boolean
   */
  protected $_isProcessed = false;

  /**
   * @var integer
   */
  protected $_items = 0;



  // Static Methods

  static public function getSupportedKeys()
  {
    if( null === self::$_supportedKeys ) {
      $r = new Zend_Reflection_Class(__CLASS__);
      self::$_supportedKeys = array_values($r->getConstants());
    }

    return self::$_supportedKeys;
  }


  
  // Methods

  public function __construct(array $rawData = null)
  {
    if( is_array($rawData) ) {
      $this->setParams($rawData);
    }
  }

  public function __get($key)
  {
    if( isset($this->_data[$key]) ) {
      return $this->_data[$key];
    } else if( isset($this->_rawData[$key]) ) {
      return $this->_rawData[$key];
    } else {
      return null;
    }
  }

  public function __set($key, $value)
  {
    $this->setParam($key, $value);
  }

  public function  __isset($key)
  {
    return null !== $this->__get($key);
  }

  public function __unset($key)
  {
    unset($this->_rawData[$key]);
    unset($this->_data[$key]);
  }



  // Params

  public function setParams($params)
  {
    foreach( $params as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      } else {
        $this->setParam($key, $value);
      }
    }
  }

  public function setParam($key, $value)
  {
    $this->_rawData[$key] = $value;
    $this->_isProcessed = false;
    $this->_isValid = false;
    return $this;
  }

  public function getRawData()
  {
    return $this->_rawData;
  }

  public function getData()
  {
    return $this->_data;
  }



  // Items
  
  public function getItems()
  {
    return $this->_items;
  }

  public function setItems($items)
  {
    foreach( $items as $item ) {
      $this->addItem($item);
    }
    return $this;
  }

  public function addItem($item)
  {
    $this->_items[] = $item;
    return $this;
  }



  // Processing

  public function resetValid()
  {
    $this->_isValid = false;
    return $this;
  }

  public function isValid()
  {
    return (bool) $this->_isValid;
  }
  
  public function resetProcessed()
  {
    $this->_isProcessed = false;
    return $this;
  }
  
  public function isProcessed()
  {
    return (bool) $this->_isProcessed;
  }

  public function process(Engine_Payment_Gateway $gateway)
  {
    if( null !== $this->_driver && $this->_driver !== $gateway->getDriver() ) {
      throw new Engine_Payment_Exception('Already processed, cannot process with a different driver');
    }
    $this->_driver = $gateway->getDriver();
    $data = $gateway->processTransaction($this);

    $this->_isProcessed = true;
    if( is_array($data) ) {
      $this->_isValid = true;
      $this->_data = $data;
    } else {
      $this->_isValid = false;
      $this->_data = null;
    }
    
    return $this;
  }
}
