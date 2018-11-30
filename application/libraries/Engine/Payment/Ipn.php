<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Ipn.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Payment_Ipn
{
  // Constants

  // Types
  const TYPE_ORDER_CREATED = 'orderCreated';
  const TYPE_FRAUD_STATUS = 'fraudStatus';
  const TYPE_SHIPPING_STATUS = 'shippingStatus';
  const TYPE_INVOICE_STATUS = 'invoiceStatus';
  const TYPE_REFUND_ISSUED = 'refundIssued';


  
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



  // Methods

  /**
   * 
   * @param array $rawData
   */
  public function __construct(array $rawData)
  {
    $this->_rawData = $rawData;
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
    $data = $gateway->processIpn($this);

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
