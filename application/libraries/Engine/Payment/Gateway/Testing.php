<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Testing.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Payment_Gateway_Testing extends Engine_Payment_Gateway
{
  // Support

  protected $_supportedCurrencies = array(
    'ARS',
    'AUD',
    'BRL',
    'CAD',
    'CHF',
    'CZK',
    'DKK',
    'EUR',
    'GBP',
    'HKD',
    'HUF',
    'ILS',
    'INR',
    'JPY',
    'MXN',
    'MYR',
    'NOK',
    'NZD',
    'PHP',
    'PLN',
    'SEK',
    'SGD',
    'THB',
    'TWD',
    'USD',
    'RUB',
    'TRY',
    'ZAR',
  );

  protected $_supportedLanguages = array(
    'es', 'en', 'de', 'fr', 'nl', 'pt', 'zh', 'it', 'ja', 'pl', 
    // Full
    'es_AR', 'en_AU', 'de_AT', 'en_BE', 'fr_BE', 'nl_BE', 'pt_BR', 'en_CA',
    'fr_CA', 'zh_CN', 'zh_HK', 'fr_FR', 'de_DE', 'it_IT', 'ja_JP', 'es_MX',
    'nl_NL', 'pl_PL', 'en_SG', 'es_SP', 'fr_CH', 'de_CH', 'en_CH', 'en_GB',
    'en_US',
    // Not supported
    'de_BE', 'zh_SG', 'gsw_CH', 'it_CH', 
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

  protected $_transactionMap = array();



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
    return null;
  }

  public function getGatewayUrl()
  {
    return $this->_gatewayUrl;
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
    
    // HACK
    if( !empty($rawData['return_url']) ) {
      $this->_gatewayUrl = $rawData['return_url'];
    }
    
    $data = $rawData;
    return $data;
  }



  // Admin

  public function test()
  {
    return true;
  }
}
