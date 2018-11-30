<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PayPal.php 10122 2013-12-11 17:29:07Z andres $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Payment_Gateway_PayPal extends Engine_Payment_Gateway
{
  // Support

  protected $_supportedCurrencies = array(
    // 'ARS', // Supported by 2Checkout, but not by PayPal
    'AUD',
    'BRL', // This currency is supported as a payment currency and a currency balance for in-country PayPal accounts only.
    'CAD',
    'CHF',
    'CZK', // Not supported by 2Checkout
    'DKK',
    'EUR',
    'GBP',
    'HKD',
    'HUF', // Not supported by 2Checkout
    'ILS', // Not supported by 2Checkout
    //'INR', // Supported by 2Checkout
    'JPY',
    'MXN',
    'MYR', // Not supported by 2Checkout - This currency is supported as a payment currency and a currency balance for in-country PayPal accounts only.
    'NOK',
    'NZD',
    'PHP', // Not supported by 2Checkout
    'PLN', // Not supported by 2Checkout
    'SEK',
    'SGD', // Not supported by 2Checkout
    'THB', // Not supported by 2Checkout
    'TWD', // Not supported by 2Checkout
    'USD',
    'RUB',
    'TRY',
    //'ZAR', // Supported by 2Checkout
  );

  protected $_supportedLanguages = array(
    'es', 'en', 'de', 'fr', 'nl', 'pt', 'zh', 'it', 'ja', 'pl', 
    // Full
    //'es_AR', 'en_AU', 'de_AT', 'en_BE', 'fr_BE', 'nl_BE', 'pt_BR', 'en_CA',
    //'fr_CA', 'zh_CN', 'zh_HK', 'fr_FR', 'de_DE', 'it_IT', 'ja_JP', 'es_MX',
    //'nl_NL', 'pl_PL', 'en_SG', 'es_SP', 'fr_CH', 'de_CH', 'en_CH', 'en_GB',
    //'en_US',
    // Not supported
    //'de_BE', 'zh_SG', 'gsw_CH', 'it_CH', 
  );

  protected $_supportedRegions = array(
    'AF', 'AX', 'AL', 'DZ', 'AS', 'AD', 'AO', 'AI', 'AQ', 'AG', 'AR', 'AM',
    'AW', 'AU', 'AT', 'AZ', 'BS', 'BH', 'BD', 'BB', 'BY', 'BE', 'BZ', 'BJ',
    'BM', 'BT', 'BO', 'BA', 'BW', 'BV', 'BR', 'IO', 'BN', 'BG', 'BF', 'BI',
    'KH', 'CM', 'CA', 'CV', 'KY', 'CF', 'TD', 'CL', 'CN', 'CX', 'CC', 'CO',
    'KM', 'CG', 'CD', 'CK', 'CR', 'CI', 'HR', 'CU', 'CY', 'CZ', 'DK', 'DJ',
    'DM', 'DO', 'EC', 'EG', 'SV', 'GQ', 'ER', 'EE', 'ET', 'FK', 'FO', 'FJ',
    'FI', 'FR', 'GF', 'PF', 'TF', 'GA', 'GM', 'GE', 'DE', 'GH', 'GI', 'GR',
    'GL', 'GD', 'GP', 'GU', 'GT', 'GG', 'GN', 'GW', 'GY', 'HT', 'HM', 'VA',
    'HN', 'HK', 'HU', 'IS', 'IN', 'ID', 'IR', 'IQ', 'IE', 'IM', 'IL', 'IT',
    'JM', 'JP', 'JE', 'JO', 'KZ', 'KE', 'KI', 'KP', 'KR', 'KW', 'KG', 'LA',
    'LV', 'LB', 'LS', 'LR', 'LY', 'LI', 'LT', 'LU', 'MO', 'MK', 'MG', 'MW',
    'MY', 'MV', 'ML', 'MT', 'MH', 'MQ', 'MR', 'MU', 'YT', 'MX', 'FM', 'MD',
    'MC', 'MN', 'MS', 'MA', 'MZ', 'MM', 'NA', 'NR', 'NP', 'NL', 'AN', 'NC',
    'NZ', 'NI', 'NE', 'NG', 'NU', 'NF', 'MP', 'NO', 'OM', 'PK', 'PW', 'PS',
    'PA', 'PG', 'PY', 'PE', 'PH', 'PN', 'PL', 'PT', 'PR', 'QA', 'RE', 'RO',
    'RU', 'RW', 'SH', 'KN', 'LC', 'PM', 'VC', 'WS', 'SM', 'ST', 'SA', 'SN',
    'CS', 'SC', 'SL', 'SG', 'SK', 'SI', 'SB', 'SO', 'ZA', 'GS', 'ES', 'LK',
    'SD', 'SR', 'SJ', 'SZ', 'SE', 'CH', 'SY', 'TW', 'TJ', 'TZ', 'TH', 'TL',
    'TG', 'TK', 'TO', 'TT', 'TN', 'TR', 'TM', 'TC', 'TV', 'UG', 'UA', 'AE',
    'GB', 'US', 'UM', 'UY', 'UZ', 'VU', 'VE', 'VN', 'VG', 'VI', 'WF', 'EH',
    'YE', 'ZM', 
  );

  protected $_supportedBillingCycles = array(
    /* 'Day', */ 'Week', /* 'SemiMonth',*/ 'Month', 'Year',
  );


  // Translation

  protected $_transactionMap = array(
    Engine_Payment_Transaction::REGION => 'LOCALECODE',
    Engine_Payment_Transaction::RETURN_URL => 'RETURNURL',
    Engine_Payment_Transaction::CANCEL_URL => 'CANCELURL',

    // Deprecated?
    Engine_Payment_Transaction::IPN_URL => 'NOTIFYURL',
    Engine_Payment_Transaction::VENDOR_ORDER_ID => 'INVNUM',
    Engine_Payment_Transaction::CURRENCY => 'CURRENCYCODE',
    Engine_Payment_Transaction::REGION => 'LOCALECODE',
  );



  // General
  
  /**
   * Constructor
   *
   * @param array $options
   */
  public function  __construct(array $options = null)
  {
    parent::__construct($options);
    
    if( null === $this->getGatewayMethod() ) {
      $this->setGatewayMethod('POST');
    }
  }

  /**
   * Get the service API
   *
   * @return Engine_Service_PayPal
   */
  public function getService()
  {
    if( null === $this->_service ) {
      $this->_service = new Engine_Service_PayPal(array_merge(
        $this->getConfig(),
        array(
          'testMode' => $this->getTestMode(),
          'log' => ( true ? $this->getLog() : null ),
        )
      ));
    }

    return $this->_service;
  }

  public function getGatewayUrl()
  {
    // Manual
    if( null !== $this->_gatewayUrl ) {
      return $this->_gatewayUrl;
    }

    // Auto
    if( $this->getTestMode() ) {
      return 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    } else {
      return 'https://www.paypal.com/cgi-bin/webscr';
    }
  }



  // IPN

  public function processIpn(Engine_Payment_Ipn $ipn)
  {
    // Validate ----------------------------------------------------------------

    // Get raw data
    $rawData = $ipn->getRawData();

    // Log raw data
    //if( 'development' === APPLICATION_ENV ) {
      $this->_log(print_r($rawData, true), Zend_Log::DEBUG);
    //}

    // Check a couple things in advance
    if( !empty($rawData['test_ipn']) && !$this->getTestMode() ) {
      throw new Engine_Payment_Gateway_Exception('Test IPN sent in non-test mode');
    }

    // @todo check the email address of the account?

    // Build url and post data
    $parsedUrl = parse_url($this->getGatewayUrl());
    $rawData = array_merge(array(
      'cmd' => '_notify-validate',
    ), $rawData);
    foreach ($rawData as $key => $value) {
      $rawData[$key] = stripslashes($value);
    }
    $postString = http_build_query($rawData, '', '&');

    if( empty($parsedUrl['host']) ) {
      $this->_throw(sprintf('Invalid host in gateway url: %s', $this->getGatewayUrl()));
      return false;
    }
    if( empty($parsedUrl['path']) ) {
      $parsedUrl['path'] = '/';
    }

    // Open socket
    $fp = fsockopen('ssl://' . $parsedUrl['host'], 443, $errNum, $errStr, 30);
    if( !$fp ) {
      $this->_throw(sprintf('Unable to open socket: [%d] %s', $errNum, $errStr));
    }
    stream_set_blocking($fp, true);

    fputs($fp, "POST {$parsedUrl['path']} HTTP/1.1\r\n");
    fputs($fp, "Host: {$parsedUrl['host']}\r\n");
    fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
    fputs($fp, "Content-length: " . strlen($postString) . "\r\n");
    fputs($fp, "Connection: close\r\n\r\n");
    fputs($fp, $postString . "\r\n\r\n");

    $response = '';
    while( !feof($fp) ) {
      $response .= fgets($fp, 1024);
    }

    fclose($fp);

    if( !stripos($response, 'VERIFIED') ) {
      $this->_log($response);
      $this->_throw(sprintf('IPN Validation Failed: %s %s', $parsedUrl['host'], $parsedUrl['path']));
      return false;
    }

    // Success!
    $this->_log('IPN Validation Succeeded');



    // Process -----------------------------------------------------------------
    $rawData = $ipn->getRawData();

    $data = $rawData;

    return $data;
  }



  // Transaction

  public function processTransaction(Engine_Payment_Transaction $transaction)
  {
    $data = array();
    $rawData = $transaction->getRawData();
    
    // Driver-specific params
    if( isset($rawData['driverSpecificParams']) ) {
      if( isset($rawData['driverSpecificParams'][$this->getDriver()]) ) {
        $data = array_merge($data, $rawData['driverSpecificParams'][$this->getDriver()]);
      }
      unset($rawData['driverSpecificParams']);
    }

    // Add default region?
    if( empty($rawData['region']) && ($region = $this->getRegion()) ) {
      $rawData['region'] = $region;
    }

    // Add default currency
    if( empty($rawData['currency']) && ($currency = $this->getCurrency()) ) {
      $rawData['currency'] = $currency;
    }


    // Process abtract translation map
    $tmp = array();
    $data = array_merge($data, $this->_translateTransactionData($rawData, $tmp));
    $rawData = $tmp;
    

    // Call setExpressCheckout
    $token = $this->getService()->setExpressCheckout($data);
    
    $data = array();
    $data['cmd'] = '_express-checkout';
    $data['token'] = $token;

    return $data;
  }



  // Admin

  public function test()
  {
    try {
      $this->getService()->searchButtons(date('Y-m-d H:i:s', time()));
    } catch( Engine_Service_PayPal_Exception $e ) {
      if( in_array((int) $e->getCode(), array(10002, 10008, 10101) ) ) {
        throw new Engine_Payment_Gateway_Exception(sprintf('Gateway login ' .
            'failed. Please double-check ' .
            'your connection information. ' .
            'The message was: %1$s', $e->getMessage()));
      }
    }
    
    return true;
  }
}
