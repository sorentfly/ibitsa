<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: 2Checkout.php 9958 2013-03-11 21:26:32Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Payment_Gateway_2Checkout extends Engine_Payment_Gateway
{
  // Support

  protected $_supportedCurrencies = array(
    'ARS', // Not supported by PayPal
    'AUD',
    'BRL',
    'CAD',
    'CHF',
    //'CZK', // Supported by PayPal, but not by 2Checkout
    'DKK',
    'EUR',
    'GBP',
    //'HUF', // Supported by PayPal, but not by 2Checkout
    'HKD',
    //'MYR', // Supported by PayPal, but not by 2Checkout
    //'ILS', // Supported by PayPal, but not by 2Checkout
    'INR', // Not supported by PayPal
    'JPY',
    'MXN',
    'NOK',
    'NZD',
    //'PHP', // Supported by PayPal, but not by 2Checkout
    //'PLN', // Supported by PayPal, but not by 2Checkout
    'SEK',
    //'SGD', // Supported by PayPal, but not by 2Checkout
    //'TWD', // Supported by PayPal, but not by 2Checkout
    //'THB', // Supported by PayPal, but not by 2Checkout
    'USD',
    'RUB',
    'TRY',
    'ZAR', // Not supported by PayPal
  );

  protected $_supportedLanguages = array(
    'zh' => 'zh',
    'da' => 'da',
    'nl' => 'nl',
    'de' => 'gr',
    'el' => 'el',
    'it' => 'it',
    'ja' => 'jp',
    'nb' => 'no',
    'pt' => 'pt',
    'sl' => 'sl',
    'sv' => 'sv',
    'en' => 'en',
    'es' => 'es_ib',
    'es_419' => 'es_la', // Latin America
    'es_ES' => 'es_ib', // Iberia
  );

  protected $_supportedRegions = array(
    'AF' => 'AFG', 'AL' => 'ALB', 'DZ' => 'DZA', 'AS' => 'ASM', 'AD' => 'AND',
    'AO' => 'AGO', 'AI' => 'AIA', 'AQ' => 'ATA', 'AG' => 'ATG', 'AR' => 'ARG',
    'AM' => 'ARM', 'AW' => 'ABW', 'AU' => 'AUS', 'AT' => 'AUT', 'AZ' => 'AZE',
    'BS' => 'BHS', 'BH' => 'BHR', 'BD' => 'BGD', 'BB' => 'BRB', 'BY' => 'BLR',
    'BE' => 'BEL', 'BZ' => 'BLZ', 'BJ' => 'BEN', 'BM' => 'BMU', 'BT' => 'BTN',
    'BO' => 'BOL', 'BA' => 'BIH', 'BW' => 'BWA', 'BV' => 'BVT', 'BR' => 'BRA',
    'IO' => 'IOT', 'BN' => 'BRN', 'BG' => 'BGR', 'BF' => 'BFA', 'BI' => 'BDI',
    'KH' => 'KHM', 'CM' => 'CMR', 'CA' => 'CAN', 'CV' => 'CPV', 'KY' => 'CYM',
    'CF' => 'CAF', 'TD' => 'TCD', 'CL' => 'CHL', 'CN' => 'CHN', 'CX' => 'CXR',
    'CC' => 'CCK', 'CO' => 'COL', 'KM' => 'COM', 'CG' => 'COG', 'CD' => 'COD',
    'CK' => 'COK', 'CR' => 'CRI', 'CI' => 'CIV', 'HR' => 'HRV', 'CY' => 'CYP',
    'CZ' => 'CZE', 'DK' => 'DNK', 'DJ' => 'DJI', 'DM' => 'DMA', 'DO' => 'DOM',
    'EC' => 'ECU', 'EG' => 'EGY', 'SV' => 'SLV', 'GQ' => 'GNQ', 'ER' => 'ERI',
    'EE' => 'EST', 'ET' => 'ETH', 'FK' => 'FLK', 'FO' => 'FRO', 'FJ' => 'FJI',
    'FI' => 'FIN', 'FR' => 'FRA', 'FX' => 'FXX', 'GF' => 'GUF', 'PF' => 'PYF',
    'TF' => 'ATF', 'GA' => 'GAB', 'GM' => 'GMB', 'GE' => 'GEO', 'DE' => 'DEU',
    'GH' => 'GHA', 'GI' => 'GIB', 'GR' => 'GRC', 'GL' => 'GRL', 'GD' => 'GRD',
    'GP' => 'GLP', 'GU' => 'GUM', 'GT' => 'GTM', 'GN' => 'GIN', 'GW' => 'GNB',
    'GY' => 'GUY', 'HT' => 'HTI', 'HM' => 'HMD', 'HN' => 'HND', 'HK' => 'HKG',
    'HU' => 'HUN', 'IS' => 'ISL', 'IN' => 'IND', 'ID' => 'IDN', 'IQ' => 'IRQ',
    'IE' => 'IRL', 'IL' => 'ISR', 'IT' => 'ITA', 'JM' => 'JAM', 'JP' => 'JPN',
    'JO' => 'JOR', 'KZ' => 'KAZ', 'KE' => 'KEN', 'KI' => 'KIR', 'KR' => 'KOR',
    'KW' => 'KWT', 'KG' => 'KGZ', 'LA' => 'LAO', 'LV' => 'LVA', 'LB' => 'LBN',
    'LS' => 'LSO', 'LR' => 'LBR', 'LY' => 'LBY', 'LI' => 'LIE', 'LT' => 'LTU',
    'LU' => 'LUX', 'MO' => 'MAC', 'MK' => 'MKD', 'MG' => 'MDG', 'MW' => 'MWI',
    'MY' => 'MYS', 'MV' => 'MDV', 'ML' => 'MLI', 'MT' => 'MLT', 'MH' => 'MHL',
    'MQ' => 'MTQ', 'MR' => 'MRT', 'MU' => 'MUS', 'YT' => 'MYT', 'MX' => 'MEX',
    'FM' => 'FSM', 'MD' => 'MDA', 'MC' => 'MCO', 'MN' => 'MNG', 'ME' => 'MNE',
    'MS' => 'MSR', 'MA' => 'MAR', 'MZ' => 'MOZ', 'NA' => 'NAM', 'NR' => 'NRU',
    'NP' => 'NPL', 'NL' => 'NLD', 'AN' => 'ANT', 'NC' => 'NCL', 'NZ' => 'NZL',
    'NI' => 'NIC', 'NE' => 'NER', 'NG' => 'NGA', 'NU' => 'NIU', 'NF' => 'NFK',
    'MP' => 'MNP', 'NO' => 'NOR', 'OM' => 'OMN', 'PK' => 'PAK', 'PW' => 'PLW',
    'PS' => 'PSE', 'PA' => 'PAN', 'PG' => 'PNG', 'PY' => 'PRY', 'PE' => 'PER',
    'PH' => 'PHL', 'PN' => 'PCN', 'PL' => 'POL', 'PT' => 'PRT', 'PR' => 'PRI',
    'QA' => 'QAT', 'RE' => 'REU', 'RO' => 'ROU', 'RU' => 'RUS', 'RW' => 'RWA',
    'SH' => 'SHN', 'KN' => 'KNA', 'LC' => 'LCA', 'PM' => 'SPM', 'VC' => 'VCT',
    'WS' => 'WSM', 'SM' => 'SMR', 'ST' => 'STP', 'SA' => 'SAU', 'SN' => 'SEN',
    'RS' => 'SRB', 'CS' => 'SCG', 'SC' => 'SYC', 'SL' => 'SLE', 'SG' => 'SGP',
    'SK' => 'SVK', 'SI' => 'SVN', 'SB' => 'SLB', 'SO' => 'SOM', 'ZA' => 'ZAF',
    'GS' => 'SGS', 'ES' => 'ESP', 'LK' => 'LKA', 'SR' => 'SUR', 'SJ' => 'SJM',
    'SZ' => 'SWZ', 'SE' => 'SWE', 'CH' => 'CHE', 'TW' => 'TWN', 'TJ' => 'TJK',
    'TZ' => 'TZA', 'TH' => 'THA', 'TL' => 'TLS', 'TG' => 'TGO', 'TK' => 'TKL',
    'TO' => 'TON', 'TT' => 'TTO', 'TN' => 'TUN', 'TR' => 'TUR', 'TM' => 'TKM',
    'TC' => 'TCA', 'TV' => 'TUV', 'UG' => 'UGA', 'UA' => 'UKR', 'AE' => 'ARE',
    'GB' => 'GBR', 'US' => 'USA', 'UM' => 'UMI', 'UY' => 'URY', 'UZ' => 'UZB',
    'VU' => 'VUT', 'VA' => 'VAT', 'VE' => 'VEN', 'VN' => 'VNM', 'VG' => 'VGB',
    'VI' => 'VIR', 'WF' => 'WLF', 'EH' => 'ESH', 'YE' => 'YEM', 'ZM' => 'ZMB',
    'ZW' => 'ZWE',

    // Supported, but no longer exist
    //'' => 'ZAR', // Zaire
    //'' => 'YUG', // Yugoslavia
    
  );

  protected $_supportedBillingCycles = array(
    'Week', 'Month', 'Year',
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
      $this->setGatewayMethod('GET');
    }
  }

  /**
   * Get the service API
   * 
   * @return Engine_Service_2Checkout
   */
  public function getService()
  {
    if( null === $this->_service ) {
      $this->_service = new Engine_Service_2Checkout(array_merge(
        $this->getConfig(),
        array(
          'testMode' => $this->getTestMode(),
          'log' => ( true ? $this->getLog() : null ),
        )
      ));
    }

    return $this->_service;
  }

  public function  getGatewayUrl()
  {
    if( null !== $this->_gatewayUrl ) {
      return $this->_gatewayUrl;
    }

    return 'https://www.2checkout.com/checkout/purchase';
    //return 'https://www.2checkout.com/checkout/spurchase';
  }



  // Misc

  public function getVendorIdentity()
  {
    if( null !== ($id = $this->getConfig('vendor_id')) ) {
      return $id;
    } else {
      $info = $this->getService()->detailCompanyInfo();
      $this->_config['vendor_id'] = $info['vendor_id'];
      $this->_config['secret'] = $info['secret'];
      return $info['vendor_id'];
    }
  }

  public function getVendorSecret()
  {
    if( null !== ($secret = $this->getConfig('secret')) ) {
      return $secret;
    } else {
      $info = $this->getService()->detailCompanyInfo();
      $this->_config['vendor_id'] = $info['vendor_id'];
      $this->_config['secret'] = $info['secret'];
      return $info['secret'];
    }
  }


  
  // Processing
  
  public function processIpn(Engine_Payment_Ipn $ipn)
  {
    // Validate ----------------------------------------------------------------

    // Get raw data
    $rawData = $ipn->getRawData();

    // Log raw data
    //if( 'development' === APPLICATION_ENV ) {
      $this->_log(print_r($rawData, true), Zend_Log::DEBUG);
    //}

    // Check gateway for info
    if( null == ($vendorIdentity = $this->getVendorIdentity()) ) {
      $this->_throw('Unable to validate IPN: vendor identity is missing.');
      return false;
    }
    if( null == ($vendorSecret = $this->getVendorSecret()) ) {
      $this->_throw('Unable to validate IPN: vendor secret is missing.');
      return false;
    }

    // Check for empty parameters
    if( !isset($rawData['sale_id']) ) {
      $this->_throw('Unable to validate IPN: sale_id is missing.');
      return false;
    }
    if( !isset($rawData['vendor_id']) ) {
      $this->_throw('Unable to validate IPN: vendor_id is missing.');
      return false;
    }
    if( !isset($rawData['invoice_id']) ) {
      $this->_throw('Unable to validate IPN: invoice_id is missing.');
      return false;
    }
    if( !isset($rawData['md5_hash']) ) {
      $this->_throw('Unable to validate IPN: md5_hash is missing.');
      return false;
    }

    // Check vendor identity matches
    if( $vendorIdentity !== $rawData['vendor_id'] ) {
      $this->_throw(sprintf('Unable to validate IPN: vendor identities do not match - given %s, expected %s',
        $rawData['vendor_id'], $vendorIdentity));
      return false;
    }

    // Validate hash
    $givenHash = strtoupper($rawData['md5_hash']);
    $expectedHash = strtoupper(md5($rawData['sale_id'] .
        $rawData['vendor_id'] . $rawData['invoice_id'] .
        $vendorSecret));

    if( $givenHash !== $expectedHash ) {
      $this->_throw(sprintf('Unable to validate IPN: hashes do not match - given %s, expected %s',
          $givenHash, $expectedHash));
      return false;
    }


    // Process data ------------------------------------------------------------
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
    

    // Process data ------------------------------------------------------------
    
    // Add vendor identity
    // Note: we can get this from the product info
    $data['sid'] = $this->getVendorIdentity();

    // Add product_id
    if( isset($rawData['vendor_product_id']) ) {
      $productInfo = $this->detailVendorProduct($rawData['vendor_product_id']);
      $data['product_id'] = $productInfo['assigned_product_id'];
      //$data['sid'] = $productInfo['vendor_id'];
    } else if( isset($rawData['product_id']) ) {
      $productInfo = $this->detailProduct($rawData['product_id']);
      $data['product_id'] = $productInfo['assigned_product_id'];
      //$data['sid'] = $productInfo['vendor_id'];
    }
    
    // Add quantity
    if( !isset($rawData['quantity']) || !is_numeric($rawData['quantity']) || $rawData['quantity'] <= 0 ) {
      $data['quantity'] = 1;
    } else {
      if( isset($data['quantity']) && $data['quantity'] > 99 ) {
        throw new Engine_Payment_Gateway_Exception('Quantity must be less than or equal to 99.');
      }
      $data['quantity'] = $rawData['quantity'];
    }

    // Add test mode
    if( $this->getTestMode() ) {
      $data['demo'] = 'Y';
    }

    // Add fixed
    if( !empty($rawData['fixed']) ) {
      $data['fixed'] = 'Y';
    }

    // Add language
    if( (isset($rawData['language']) && ($language = $this->isSupportedLanguage($rawData['language']))) ||
        ($language = $this->getLanguage()) ) {
      $data['language'] = $language;
    }
    
    // Add currency - oh nevermind they don't support it
//    if( !empty($rawData['currency']) &&
//        ($currency = $this->isSupportedCurrency($rawData['currency'])) ||
//        ($currency = $this->getCurrency()) ) {
//
//    }
    
    // Add return_url
    if( isset($rawData['return_url']) ) {
      $data['x_receipt_link_url'] = $rawData['return_url'];
      //$data['return_url'] = $rawData['return_url'];
    }

    // Add merchant_order_id
    if( isset($rawData['merchant_order_id']) ) {
      if( strlen($rawData['merchant_order_id']) > 50 ) {
        throw new Engine_Payment_Gateway_Exception('Merchant Order ID cannot be longer than 50 character.');
      }
      $data['merchant_order_id'] = $rawData['merchant_order_id'];
    } else if( isset($rawData['vendor_order_id']) ) {
      if( strlen($rawData['vendor_order_id']) > 50 ) {
        throw new Engine_Payment_Gateway_Exception('Merchant Order ID cannot be longer than 50 character.');
      }
      $data['merchant_order_id'] = $rawData['vendor_order_id'];
    }

    // Add pay_method
    if( isset($rawData['pay_method']) ) {
      if( in_array($rawData['pay_method'], array('CC', 'AL', 'PPI')) ) {
        $data['pay_method'] = $rawData['pay_method'];
      }
    }

    // Add skip_landing
    if( !empty($rawData['skip_landing']) ) {
      $data['skip_landing'] = 1;
    }

    // @todo add x_receipt_link_url ?

    // @todo process the rest of $rawData
    



    // Validate data -----------------------------------------------------------

    if( empty($data['product_id']) ) {
      $this->_throw(sprintf('Missing parameter: %1$s', 'product_id'));
      $this->_throw('No product identity provided');
      return false;
    }
    
    if( empty($data['sid']) ) {
      $this->_throw(sprintf('Missing parameter: %1$s', 'sid'));
      return false;
    }

    if( empty($data['quantity']) ) {
      $this->_throw(sprintf('Missing parameter: %1$s', 'quantity'));
      return false;
    }
    
    return $data;
  }

  public function validateReturn(array $params)
  {
    $givenHash = strtolower($params['key']);
    $expectedHash = strtolower(md5(
      $this->getVendorSecret() .
      $this->getVendorIdentity() . // $params['sid']
      $params['order_number'] .
      $params['total']
    ));

    if( $givenHash !== $expectedHash ) {
      $this->_throw('Hashes don\'t match.');
    }

    return $this;
  }



  // Admin

  public function test()
  {
    // Will throw exception on failure
    $this->getService()->detailCompanyInfo();
    return $this;
  }

  public function createProduct($params = array())
  {
    return $this->getService()->createProduct($params);
  }

  public function editProduct($productId, $params = array())
  {
    return $this->getService()->updateProduct($productId, $params);
  }

  public function deleteProduct($productId)
  {
    return $this->getService()->deleteProduct($productId);
  }

  public function detailProduct($productId)
  {
    return $this->getService()->detailProduct($productId);
  }

  public function detailVendorProduct($productId)
  {
    return $this->getService()->detailVendorProduct($productId);
  }
  
  public function listProducts($params = array())
  {
    return $this->getService()->listProducts($params);
  }
  
  public function vendorInfo($returnKey = null)
  {
    return $this->getService()->detailCompanyInfo();
  }
}
