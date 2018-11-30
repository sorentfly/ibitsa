<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Gateway.php 10229 2014-05-16 22:43:44Z andres $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Engine_Payment_Gateway
{
  // General

  /**
   * @var string
   */
  protected $_driver;

  /**
   * @var Zend_Service_Abstract
   */
  protected $_service;
  
  /**
   * The gateway auth config
   * 
   * @var string
   */
  protected $_config;

  
  // Config

  /**
   * Are we in test mode?
   * 
   * @var boolean
   */
  protected $_testMode;

  /**
   * The url to the gateway page
   * @var string
   */
  protected $_gatewayUrl;

  /**
   * The request method to use with the gateway
   * 
   * @var string
   */
  protected $_gatewayMethod;

  /**
   * The version of the gateway
   * 
   * @var string
   */
  protected $_gatewayVersion;

  /**
   * The url to return to on cancel
   * 
   * @var string
   */
  protected $_cancelUrl;

  /**
   * The url to return to on completion
   * 
   * @var string
   */
  protected $_returnUrl;

  /**
   * The url to send IPNs to
   * 
   * @var string
   */
  protected $_ipnUrl;

  /**
   * Logger
   * 
   * @var Zend_Log
   */
  protected $_log;

  // Other

  /**
   * The default currency
   * 
   * @var string
   */
  protected $_currency;

  /**
   * The default language
   * 
   * @var string
   */
  protected $_language;

  /**
   * The default region
   * 
   * @var string
   */
  protected $_region;

  
  // Support

  /**
   * List of currencies supported by the gateway
   * 
   * @var array
   */
  protected $_supportedCurrencies;

  /**
   * List of languages supported by the gateway
   *
   * @var array
   */
  protected $_supportedLanguages;

  /**
   * List of regions supported by the gateway
   *
   * @var array
   */
  protected $_supportedRegions;

  /**
   * List of supported billing cycles
   * 
   * @var array
   */
  protected $_supportedBillingCycles;


  // Translation

  protected $_transactionMap;


  
  // General

  /**
   * Constructor
   * 
   * @param array $options
   */
  public function __construct(array $options = null)
  {
    if( is_array($options) ) {
      $this->setOptions($options);
    }
  }

  /**
   * Magic caller.
   * Forwards calls to service
   *
   * @param string $method
   * @param array $arguments
   * @return mixed
   */
  public function __call($method, array $arguments)
  {
    // Forward to service?
    $service = $this->getService();
    if( $service && method_exists($service, $method) ) {
      $r = new ReflectionMethod($service, $method);
      return $r->invokeArgs($service, $arguments);
    }

    // Whoops, throw exception
    throw new Engine_Exception(sprintf('Method %d does not exist in class %s',
        __METHOD__, __CLASS__));
  }

  /**
   * Sets options
   * 
   * @param array $options
   * @return Engine_Payment_Gateway
   */
  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }

    return $this;
  }

  /**
   * Sets authentication options
   * 
   * @param array $config
   */
  public function setConfig(array $config)
  {
    //if( empty($config['username']) ||
    //    empty($config['password']) ) {
    //  throw new Engine_Payment_Gateway_Exception('Missing username or password');
    //}
    $this->_config = $config;
    if( $this->_service && method_exists($this->_service, 'setOptions') ) {
      $this->_service->setOptions($this->_config);
    }
    return $this;
  }

  /**
   * Gets authentication options
   * 
   * @param string $key
   * @return mixed
   */
  public function getConfig($key = null)
  {
    if( null === $key ) {
      return $this->_config;
    } else if( null !== $key &&
        isset($this->_config[$key]) ) {
      return $this->_config[$key];
    } else {
      return null;
    }
  }

  /**
   * Get the service API
   *
   * @return Zend_Service_Abstract
   */
  abstract public function getService();



  // Gateway
  
  /**
   * Get the driver type
   *
   * @return string
   */
  public function getDriver()
  {
    if( null === $this->_driver ) {
      $this->_driver = array_pop(explode('_', get_class($this)));
    }
    return $this->_driver;
  }
  
  public function getGatewayUrl()
  {
    return $this->_gatewayUrl;
  }

  public function setGatewayUrl($gatewayUrl)
  {
    $this->_gatewayUrl = $gatewayUrl;
    return $this;
  }

  public function getGatewayMethod()
  {
    return $this->_gatewayMethod;
  }

  public function setGatewayMethod($gatewayMethod)
  {
    $this->_gatewayMethod = $gatewayMethod;
    return $this;
  }

  public function getTestMode()
  {
    return (bool) $this->_testMode;
  }

  public function setTestMode($flag)
  {
    $this->_testMode = (bool) $flag;
    return $this;
  }
  

  
  // Options
  
  public function getReturnUrl()
  {
    return $this->_returnUrl;
  }

  public function setReturnUrl($returnUrl)
  {
    $this->_returnUrl = $returnUrl;
    return $this;
  }

  public function getCancelUrl()
  {
    return $this->_cancelUrl;
  }

  public function setCancelUrl($returnUrl)
  {
    $this->_cancelUrl = $returnUrl;
    return $this;
  }

  public function getIpnUrl()
  {
    return $this->_ipnUrl;
  }

  public function setIpnUrl($ipnUrl)
  {
    $this->_ipnUrl = $ipnUrl;
    return $this;
  }



  // Other

  public function getCurrency()
  {
    return $this->_currency;
  }

  public function setCurrency($currency)
  {
    if( null === $currency ) {
      $this->_currency = null;
    } else if( ($currency = $this->isSupportedCurrency($currency)) ) {
      $this->_currency = $currency;
    }
    return $this;
  }

  public function getLanguage()
  {
    return $this->_language;
  }

  public function setLanguage($language)
  {
    if( null === $language ) {
      $this->_language = null;
    } else if( ($language = $this->isSupportedLanguage($language)) ) {
      $this->_language = $language;
    }
    return $this;
  }

  public function getRegion()
  {
    return $this->_region;
  }

  public function setRegion($region)
  {
    if( null === $region ) {
      $this->_region = null;
    } else if( ($region = $this->isSupportedRegion($region)) ) {
      $this->_region = $region;
    }
    return $this;
  }



  // Support

  public function getSupportedCurrencies()
  {
    return $this->_supportedCurrencies;
  }

  public function isSupportedCurrency($currency)
  {
    if( !is_string($currency) || '' == $currency ) {
      return false;
    }

    $currency = strtoupper($currency);
    
    if( isset($this->_supportedCurrencies[$currency]) ) {
      $currency = $this->_supportedCurrencies[$currency];
    } else if( !in_array($currency, $this->_supportedCurrencies) ) {
      $currency = false;
    }

    return $currency;
  }

  public function isSupportedLanguage($language)
  {
    if( !is_string($language) || '' == $language ) {
      return false;
    }
    
    // $language = strtolower($language);

    list($shortLanguage) = explode('_', str_replace('-', '_', $language));

    if( isset($this->_supportedLanguages[$language]) ) {
      $language = $this->_supportedLanguages[$language];
    } else if( in_array($language, $this->_supportedLanguages) ) {
      // Ok
    } else if( isset($this->_supportedLanguages[$shortLanguage]) ) {
      $language = $this->_supportedLanguages[$shortLanguage];
    } else if( in_array($shortLanguage, $this->_supportedLanguages) ) {
      // Ok
    } else {
      $language = false;
    }

    return $language;
  }
  
  public function isSupportedRegion($region)
  {
    if( !is_string($region) || '' == $region ) {
      return false;
    }

    $region = strtoupper($region); // do we need this?

    if( isset($this->_supportedRegions[$region]) ) {
      $region = $this->_supportedRegions[$region];
    } else if( !in_array($region, $this->_supportedRegions) ) {
      $region = false;
    }
    
    return $region;
  }

  public function getSupportedBillingCycles()
  {
    return $this->_supportedBillingCycles;
  }

  public function isSupportedBillingCycle($billingCycle)
  {
    $cycles = array_map('strtolower', $this->_supportedBillingCycles);
    $index = array_search(strtolower($billingCycle), $cycles);
    if( false !== $index ) {
      $billingCycle = $this->_supportedBillingCycles[$index];
    } else {
      $billingCycle = false;
    }

    return $billingCycle;
  }



  // Log

  /**
   * @return Zend_Log
   */
  public function getLog()
  {
    if( null === $this->_log ) {
      if( !defined('APPLICATION_PATH') ) {
        throw new Engine_Payment_Gateway_Exception('No log defined');
      } else {
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/payment.log');
        $this->_log = new Zend_Log($writer);
      }
    }
    return $this->_log;
  }

  public function setLog(Zend_Log $log)
  {
    $this->_log = $log;
    return $this;
  }



  // Processing
  
  abstract public function processIpn(Engine_Payment_Ipn $ipn);

  abstract public function processTransaction(Engine_Payment_Transaction $transaction);



  // API

  /**
   * Test the login info
   *
   * @throws Engine_Payment_Gateway_Exception on failure
   */
  abstract public function test();





  // Static utility

  static public function hmac($key, $data)
  {
    $b = 64;

    if( strlen($key) > $b ) {
      $key = pack("H*", md5($key));
    }

    $key = str_pad($key, $b, chr(0x00));
    $ipad = str_pad('', $b, chr(0x36));
    $opad = str_pad('', $b, chr(0x5c));
    $k_ipad = $key ^ $ipad;
    $k_opad = $key ^ $opad;

    return md5($k_opad . pack("H*", md5($k_ipad . $data)));
  }



  // Translation

  protected function _translateTransactionData($params, &$missingParams = null)
  {
    // Nothing in map
    if( !is_array($this->_transactionMap) ) {
      return array();
    }

    // Translate
    $data = array();
    foreach( $params as $key => $value ) {
      if( isset($this->_transactionMap[$key]) ) {
        // Special cases
        switch( $key ) {
          case Engine_Payment_Transaction::REGION:
            $value = $this->isSupportedRegion($value);
            if( !$value ) {
              continue;
            }
            break;
          case Engine_Payment_Transaction::LANGUAGE:
            $value = $this->isSupportedLanguage($value);
            if( !$value ) {
              continue;
            }
            break;
          case Engine_Payment_Transaction::CURRENCY:
            $value = $this->isSupportedCurrency($value);
            if( !$value ) {
              continue;
            }
            break;
        }
        // Save
        $data[$this->_transactionMap[$key]] = $params[$key];
      } else if( is_array($missingParams) ) {
        // Set as missing
        $missingParams[$key] = $value;
      }
    }

    return $data;
  }


  
  // Utility

  protected function _log($message, $code = Zend_Log::INFO)
  {
    $this->getLog()->log($message, $code);
  }

  protected function _throw($message, $code = Zend_Log::ERR)
  {
    $this->_log($message, $code);
    throw new Engine_Payment_Gateway_Exception($message, $code);
  }
}
