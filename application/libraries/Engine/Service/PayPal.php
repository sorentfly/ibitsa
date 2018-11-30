<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Service_PayPal
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PayPal.php 10231 2014-05-16 22:43:54Z andres $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Service_PayPal
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Service_PayPal extends Zend_Service_Abstract
{
  /**
   * The username to login as
   *
   * @var string
   */
  protected $_username;

  /**
   * The password to use to login
   *
   * @var string
   */
  protected $_password;

  /**
   * The signature
   *
   * @var string
   */
  protected $_signature;

  /**
   * The certificate
   *
   * @var string
   */
  protected $_certificate;

  /**
   * Are we in test mode
   * 
   * @var boolean
   */
  protected $_testMode;

  /**
   * The protocol version to use
   * 
   * @var string
   */
  protected $_version = '65.0';

  /**
   * Preprocess parameters that are arrays?
   * 
   * @var boolean
   */
  protected $_preProcessRequest = true;
  
  /**
   * Process response lists into arrays?
   * 
   * @var boolean
   */
  protected $_postProcessResponse = true;

  /**
   * The log to send debug messages to
   * 
   * @var Zend_Log
   */
  protected $_log;


  
  /**
   * Constructor
   *
   * @param array $options
   */
  public function __construct(array $options)
  {
    $this->setOptions($options);
    
    // Force the curl adapter if it's available
    if( extension_loaded('curl') ) {
      $adapter = new Zend_Http_Client_Adapter_Curl();
      $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
      $adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
      //$adapter->setCurlOption(CURLOPT_VERBOSE, false);
      $this->getHttpClient()->setAdapter($adapter);
    }
    $this->getHttpClient()->setConfig(array('timeout' => 15));
  }

  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $property = '_' . $key;
      if( property_exists($this, $property) ) {
        $this->$property = $value;
      }
    }

    // Check options
    if( empty($this->_username) || empty($this->_password) ||
        (empty($this->_signature) && empty($this->_certificate) ) ) {
      throw new Engine_Service_PayPal_Exception('Not all connection ' .
          'options were specified.', 'MISSING_LOGIN');
      throw new Zend_Service_Exception('Not all connection options were specified.');
    }
  }



  // Website Payments Standard API Operations

  /**
   * Creates a Website Payments Standard button. You can create either a button
   * that is hosted on PayPal or a non-hosted button.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_BMCreateButton
   * @param string|array $type
   * @param array $options
   * @return array
   */
  public function createButton($type, $options = null)
  {
    // Build params
    if( is_array($type) ) {
      $params = $type;
    } else {
      if( is_array($options) && !empty($options) ) {
        $params = $options;
      } else {
        $params = array();
      }
      $params['BUTTONTYPE'] = $type;
    }

    // Preprocess params
    if( $this->_preProcessRequest ) {
      $params = $this->_preProcessRequestData($params, array(
        'BUTTONVARS' => 'L_BUTTONVAR%d',
        'OPTIONS' => array(
          'NAME' => 'OPTION%dNAME',
          'TYPE' => 'OPTION%dTYPE',
          'SELECT' => 'L_OPTION%dSELECT%d',
          'BILLINGPERIOD' => 'L_OPTION%dBILLINGPERIOD%d',
          'BILLINGPFREQUENCY' => 'L_OPTION%dBILLINGPFREQUENCY%d',
          'TOTALBILLINGCYCLES' => 'L_OPTION%dTOTALBILLINGCYCLES%d',
          'AMOUNT' => 'L_OPTION%dAMOUNT%d',
          'SHIPPINGAMOUNT' => 'L_OPTION%dSHIPPINGAMOUNT%d',
          'TAXAMOUNT' => 'L_OPTION%dTAXAMOUNT%d',
        ),
      ));
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'BUTTONTYPE',
    ), array(
      'L_BUTTONVAR%d', 'OPTION%dNAME', 'L_OPTION0PRICE%d', 'OPTION%dTYPE',
      'L_OPTION%dSELECT%d', 
      'L_OPTION%dBILLINGPERIOD%d', 'L_OPTION%dBILLINGPFREQUENCY%d',
      'L_OPTION%dTOTALBILLINGCYCLES%d', 'L_OPTION%dAMOUNT%d',
      'L_OPTION%dSHIPPINGAMOUNT%d', 'L_OPTION%dTAXAMOUNT%d',
    ));
    
    // Send request
    $client = $this->_prepareHttpClient('BMCreateButton');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Changes the status of a hosted button. Currently, you can only delete a
   * button.
   * 
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_BMManageButtonStatus
   * @param string $buttonId
   * @return Engine_Service_PayPal 
   */
  public function deleteButton($buttonId)
  {
    // Build params
    if( is_array($buttonId) ) {
      $params = $buttonId;
    } else {
      $params = array();
      $params['HOSTEDBUTTONID'] = $buttonId;
    }

    // Force some params
    $params['BUTTONSTATUS'] = 'DELETE';

    // Check params
    $params = $this->_checkParams($params, array(
      'HOSTEDBUTTONID', 'BUTTONSTATUS',
    ));

    // Send request
    $client = $this->_prepareHttpClient('BMManageButtonStatus');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Obtains information about a hosted Website Payments Standard button. You
   * can use this information to set the fields that have not changed when
   * updating a button.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_BMGetButtonDetails
   * @param string $buttonId
   * @return array
   */
  public function detailButton($buttonId)
  {
    // Build params
    if( is_array($buttonId) ) {
      $params = $buttonId;
    } else {
      $params = array();
      $params['HOSTEDBUTTONID'] = $buttonId;
    }

    // Check params
    $params = $this->_checkParams($params, 'HOSTEDBUTTONID');

    // Send request
    $client = $this->_prepareHttpClient('BMGetButtonDetails');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    // Post process response data?
    if( $this->_postProcessResponse ) {
      $responseData = $this->_postProcessResponseData($responseData, array(
        'L_BUTTONVAR%d' => array('BUTTONVARS'),
        'OPTION%dNAME' => array('OPTIONS', 'NAME'),
        'OPTION%dTYPE' => array('OPTIONS', 'TYPE'),
        'L_OPTION%dSELECT%d' => array('OPTIONS', 'SELECT'),
        'L_OPTION%dPRICE%d' => array('OPTIONS', 'PRICE'),
        'L_OPTION%dBILLINGPERIOD%d' => array('OPTIONS', 'BILLINGPERIOD'),
        'L_OPTION%dBILLINGPFREQUENCY%d' => array('OPTIONS', 'BILLINGPFREQUENCY'),
        'L_OPTION%dTOTALBILLINGCYCLES%d' => array('OPTIONS', 'TOTALBILLINGCYCLES'),
        'L_OPTION%dAMOUNT%d' => array('OPTIONS', 'AMOUNT'),
        'L_OPTION%dSHIPPINGAMOUNT%d' => array('OPTIONS', 'SHIPPINGAMOUNT'),
        'L_OPTION%dTAXAMOUNT%d' => array('OPTIONS', 'TAXAMOUNT'),
      ), array(
        'BUTTONTYPE', 'BUTTONCODE', 'BUTTONSUBTYPE', 'HOSTEDBUTTONID',
        'WEBSITECODE', 'EMAILLINK', 
      ));
    }

    return $responseData;
  }

  /**
   * Determines the inventory levels and other inventory-related information for
   * a button and menu items associated with the button. Typically, you call
   * BMGetInventory to obtain field values before calling BMSetInventory
   * to change the inventory levels.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_BMGetInventory
   * @param string $buttonId
   * @param array $downloadKeys
   * @return array
   */
  public function getInventory($buttonId, $downloadKeys = null)
  {
    // Build params
    if( is_array($buttonId) ) {
      $params = $buttonId;
    } else {
      $params = array();
      $params['HOSTEDBUTTONID'] = $buttonId;
      if( is_array($downloadKeys) && !empty($downloadKeys) ) {
        foreach( $downloadKeys as $i => $v ) {
          $params['L_DIGITALDOWNLOADKEYS' . sprintf('%1$d', $i)] = $v;
        }
      }
    }
    
    // Check params
    $params = $this->_checkParams($params, array(
      'HOSTEDBUTTONID'
    ), array(
      'L_DIGITALDOWNLOADKEYS%d',
    ));

    // Send request
    $client = $this->_prepareHttpClient('BMGetInventory');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    // Post process response data?
    if( $this->_postProcessResponse ) {
      $responseData = $this->_postProcessResponseData($responseData, array(
        'L_OPTIONNUMBER%d' => array('OPTIONS', 'NUMBER'),
        'L_OPTIONQTY%d' => array('OPTIONS', 'QTY'),
        'L_OPTIONSELECT%d' => array('OPTIONS', 'SELECT'),
        'L_OPTIONQTYDELTA%d' => array('OPTIONS', 'QTYDELTA'),
        'L_OPTIONALERT%d' => array('OPTIONS', 'ALERT'),
        'L_OPTIONCOST%d' => array('OPTIONS', 'COST'),
      ), array(
        'HOSTEDBUTTONID', 'TRACKINV', 'TRACKPNL', 'OPTIONINDEX', 'SOLDOUTURL',
        'ITEMNUMBER', 'ITEMQTY', 'ITEMQTYDELTA', 'ITEMALERT', 'ITEMCOST', 
      ));
    }

    return $responseData;
  }

  /**
   * Obtains a list of your hosted Website Payments Standard buttons.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_BMButtonSearch
   * @param string $startDate
   * @param string $endDate
   * @return array
   */
  public function searchButtons($startDate, $endDate = null)
  {
    // Build params
    if( is_array($startDate) ) {
      $params = $startDate;
    } else {
      $params = array();
      $params['STARTDATE'] = $startDate;
      if( null !== $endDate ) {
        $params['ENDDATE'] = $endDate;
      }
    }
    
    // Check params
    $params = $this->_checkParams($params, 'STARTDATE', 'ENDDATE');

    // Send request
    $client = $this->_prepareHttpClient('BMButtonSearch');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    // Post process response data?
    if( $this->_postProcessResponse ) {
      $responseData = $this->_postProcessResponseData($responseData, array(
        'L_HOSTEDBUTTONID%d' => array('BUTTONS', 'HOSTEDBUTTONID'),
        'L_TYPE%d' => array('BUTTONS', 'TYPE'),
        'L_ITEMNAME%d' => array('BUTTONS', 'ITEMNAME'),
        'L_MODIFYDATE%d' => array('BUTTONS', 'MODIFYDATE'),
      ));
    }

    return $responseData;
  }

  /**
   * Sets the inventory level and inventory management features for the
   * specified button. When you set the inventory level for a button, PayPal
   * can track inventory, calculate the gross profit associated with sales,
   * send you an alert when inventory drops below a specified quantity, and
   * manage sold out conditions.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_BMSetInventory
   * @param string $buttonId
   * @param array $options
   * @return array
   */
  public function setInventory($buttonId, $options = null)
  {
    // Build params
    if( is_array($buttonId) ) {
      $params = $buttonId;
    } else {
      if( is_array($options) ) {
        $params = $options;
      } else {
        $params = array();
      }
      $params['HOSTEDBUTTONID'] = $buttonId;
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'HOSTEDBUTTONID', 'TRACKINV', 'TRACKPNL', 
    ), array(
      'OPTIONINDEX', 'REUSEDIGITALDOWNLOADKEYS', 'APPENDDIGITALDOWNLOADKEYS',
      'L_DIGITALDOWNLOADKEYS%d', 'ITEMNUMBER', 'ITEMQTY', 'ITEMQTYDELTA',
      'ITEMALERT', 'ITEMCOST', 'L_OPTIONNUMBER%d', 'L_OPTIONQTY%d',
      'L_OPTIONSELECT%d', 'L_OPTIONQTYDELTA%d', 'L_OPTIONALERT%d',
      'L_OPTIONCOST%d',
    ));

    // Send request
    $client = $this->_prepareHttpClient('BMSetInventory');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Modify a Website Payments Standard button that is hosted on PayPal. This
   * operation replaces all fields in the specified button; therefore, you must
   * specify a value for each field you want to include for the button, whether
   * or not it changed.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_BMUpdateButton
   * @param string $buttonId
   * @param array $options
   * @return array
   */
  public function updateButton($buttonId, $options = null)
  {
    // Build params
    if( is_array($buttonId) ) {
      $params = $buttonId;
    } else {
      if( is_array($options) && !empty($options) ) {
        $params = $options;
      } else {
        $params = array();
      }
      $params['HOSTEDBUTTONID'] = $buttonId;
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'HOSTEDBUTTONID'
    ), array(
      'OPTION%dNAME',
      'L_OPTION%dSELECT%d', 'L_OPTION0PRICE%d', 'OPTION%dTYPE',
      'L_OPTION%dBILLINGPERIOD%d', 'L_OPTION%dBILLINGPFREQUENCY%d',
      'L_OPTION%dTOTALBILLINGCYCLES%d', 'L_OPTION%dAMOUNT%d',
      'L_OPTION%dSHIPPINGAMOUNT%d', 'L_OPTION%dTAXAMOUNT%d',
    ));

    // Send request
    $client = $this->_prepareHttpClient('BMUpdateButton');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }



  // Website Payments Pro and Express Checkout API Operations

  // Miscellaneous
  
  /**
   * Confirms whether a postal address and postal code match those of the
   * specified PayPal account holder.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_AddressVerify
   * @param string $email
   * @param string $street
   * @param string $zip
   * @return array 
   */
  public function addressVerify($email, $street = null, $zip = null)
  {
    if( is_array($email) ) {
      $params = $email;
    } else {
      $params = array();
      $params['EMAIL'] = $email;
      if( null !== $street ) {
        $params['STREET'] = $street;
      }
      if( null !== $zip ) {
        $params['ZIP'] = $zip;
      }
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'EMAIL', 'STREET', 'ZIP',
    ));

    // Send request
    $client = $this->_prepareHttpClient('AddressVerify');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Obtain the available balance for a PayPal account.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_GetBalance
   * @param boolean $allCurrencies
   * @return array
   */
  public function getBalance($allCurrencies = null)
  {
    // Build params
    if( is_array($allCurrencies) ) {
      $params = $allCurrencies;
    } else {
      $params = array();
      if( null !== $allCurrencies ) {
        $params['RETURNALLCURRENCIES'] = $allCurrencies;
      }
    }

    // Check params
    $params = $this->_checkParams($params, 'RETURNALLCURRENCIES');

    // Send request
    $client = $this->_prepareHttpClient('GetBalance');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    // Post process response data?
    if( $this->_postProcessResponse ) {
      $responseData = $this->_postProcessResponseData($responseData, array(
        'L_AMT%d' => array('BALANCES', 'AMT'),
        'L_CURRENCYCODE%d' => array('BALANCES', 'CURRENCYCODE'),
      ));
    }

    return $responseData;
  }



  // Direct payments

  /**
   * Process a credit card payment.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoDirectPayment
   * @param array $params
   * @return array
   */
  public function directPayment(array $params)
  {
    // Check params
    $this->_checkParams($params, array(
      'IPADDRESS', 'CREDITCARDTYPE', 'ACCT', 'FIRSTNAME', 'LASTNAME', 'STREET',
      'CITY', 'STATE', 'COUNTRYCODE', 'ZIP', 'AMT',
    ), array(
      'PAYMENTACTION', 'RETURNFMFDETAILS', 'EXPDATE', 'CVV2', 'STARTDATE',
      'ISSUENUMBER', 'EMAIL', 'STREET2', 'SHIPTOPHONENUM', 'CURRENCYCODE',
      'ITEMAMT', 'SHIPPINGAMT', 'INSURANCEAMT', 'SHIPDISCAMT', 'HANDLINGAMT',
      'TAXAMT', 'DESC', 'CUSTOM', 'INVNUM', 'BUTTONSOURCE', 'NOTIFYURL',
      'SHIPTONAME', 'SHIPTOSTREET', 'SHIPTOSTREET2', 'SHIPTOCITY',
      'SHIPTOSTATE', 'SHIPTOZIP', 'SHIPTOCOUNTRY', 'SHIPTOPHONENUM',
      'AUTHSTATUS3D', 'MPIVENDOR3DS', 'CAVV', 'ECI3DS', 'XID',

      'L_NAME%d', 'L_DESC%d', 'L_AMT%d', 'L_NUMBER%d', 'L_QTY%d', 'L_TAXAMT%d',
      'L_EBAYITEMNUMBER%d', 'L_EBAYITEMAUCTIONTXNID%d', 'L_EBAYITEMORDERID%d',
    ));

    // Send request
    $client = $this->_prepareHttpClient('DoDirectPayment');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    // Post process response data?
    if( $this->_postProcessResponse ) {
      $responseData = $this->_postProcessResponseData($responseData, array(
        'L_FMF%sID%d' => array('FMF', 'ID'),
        'L_FMF%sNAME%d' => array('FMF', 'NAME'),
      ), array(
        'TRANSACTIONID', 'AMT', 'AVSCODE', 'CVV2MATCH', 'VPAS',
        'ECISUBMITTED3DS',
      ));
    }

    return $responseData;
  }

  /**
   * Issue a credit to a card not referenced by the original transaction.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoNonReferencedCredit
   * @param array $params
   * @return array
   */
  public function nonReferencedCredit($params)
  {
    // Check params
    $this->_checkParams($params, array(
      'AMT', 'CURRENCYCODE', 'CREDITCARDTYPE', 'ACCT', 'FIRSTNAME', 'LASTNAME',
      'STREET', 'CITY', 'STATE', 'COUNTRYCODE', 'ZIP',
    ), array(
      'NETAMT', 'TAXAMT', 'SHIPPINGAMT', 'NOTE', 'EXPDATE', 'CVV2', 'STARTDATE',
      'ISSUENUMBER', 'SALUTATION', 'FIRSTNAME', 'MIDDLENAME', 'LASTNAME',
      'SUFFIX', 'EMAIL', 'STREET2', 'SHIPTOPHONENUM',
    ));

    // Send request
    $client = $this->_prepareHttpClient('DoNonReferencedCredit');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Make a payment to one or more PayPal account holders.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_MassPay
   * @param string $emailSubject
   * @param string $currencyCode
   * @param string $receiverType
   * @return Engine_Service_PayPal
   */
  public function massPayment($emailSubject = null, $currencyCode = null,
      $receiverType = null)
  {
    // Build params
    if( is_array($emailSubject) ) {
      $params = $emailSubject;
    } else {
      $params = array();
      if( null !== $emailSubject ) {
        $params['EMAILSUBJECT'] = $emailSubject;
      }
      if( null !== $currencyCode ) {
        $params['CURRENCYCODE'] = $currencyCode;
      }
      if( null !== $receiverType ) {
        $params['RECEIVERTYPE'] = $receiverType;
      }
    }

    // Send request
    $client = $this->_prepareHttpClient('MassPay');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }



  // Payment Authorization

  /**
   * Capture an authorized payment.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoCapture
   * @param string $authorizationId
   * @param string $amt
   * @param string $type
   * @param array $options
   */
  public function capturePayment($authorizationId, $amt = null, $type = null,
      $options = null)
  {
    if( is_array($authorizationId) ) {
      $params = $authorizationId;
    } else {
      if( is_array($options) ) {
        $params = $options;
      } else {
        $params = array();
      }
      $params['AUTHORIZATIONID'] = $authorizationId;
      if( null !== $amt ) {
        $params['AMT'] = $amt;
      }
      if( null !== $type ) {
        $params['COMPLETETYPE'] = $type;
      }
    }

    // Check params
    $this->_checkParams($params, array(
      'AUTHORIZATIONID', 'AMT', 'COMPLETETYPE',
    ), array(
      'CURRENCYCODE', 'INVNUM', 'NOTE', 'SOFTDESCRIPTOR',
    ));

    // Send request
    $client = $this->_prepareHttpClient('DoCapture');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }
  
  /**
   * Reauthorize
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoReauthorization
   * @param string $authorizationId
   * @param string $amount
   * @param string $currencyCode
   * @return array
   */
  public function reauthorize($authorizationId, $amount = null,
      $currencyCode = null)
  {
    // Build params
    if( is_array($authorizationId) ) {
      $params = $authorizationId;
    } else {
      $params = array();
      $params[''] = $authorizationId;
      if( null !== $amount ) {
        $params['AMT'] = $amount;
      }
      if( null !== $currencyCode ) {
        $params['CURRENCYCODE'] = $currencyCode;
      }
    }

    // Check params
    $this->_checkParams($params, array(
      'AUTHORIZATIONID', 'AMT'
    ), array(
      'CURRENCYCODE'
    ));

    // Send request
    $client = $this->_prepareHttpClient('DoDirectPayment');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Void an order or an authorization.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoVoid
   * @param string $authorizationId
   * @param string $note
   * @return array
   */
  public function voidAuthorization($authorizationId, $note = null)
  {
    // Build params
    if( is_array($authorizationId) ) {
      $params = $authorizationId;
    } else {
      $params = array();
      $params['AUTHORIZATIONID'] = $authorizationId;
      if( null !== $note ) {
        $params['NOTE'] = $note;
      }
    }

    // Check params
    $this->_checkParams($params, 'AUTHORIZATIONID', 'NOTE');

    // Send request
    $client = $this->_prepareHttpClient('DoVoid');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }



  // Customer Billing Agreement

  /**
   * Obtain information about a billing agreement’s PayPal account holder.
   * Note: The token expires after three hours.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_GetBillingAgreementCustomerDetails
   * @param string $token
   * @return array
   */
  public function detailCustomerBillingAgreement($token)
  {
    // Build params
    if( is_array($token) ) {
      $params = $token;
    } else {
      $params = array();
      $params['TOKEN'] = $token;
    }

    // Check params
    $this->_checkParams($params, 'TOKEN');

    // Send request
    $client = $this->_prepareHttpClient('GetBillingAgreementCustomerDetails');
    $client
      ->setParameterPost($params)
      ;
    
    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Initiates the creation of a billing agreement.
   * 
   * Note: If you are using Express Checkout with version 54.0 or later of the API,
   * do not use the SetCustomerBillingAgreement API operation to set up a
   * billing agreement. Use the SetExpressCheckout API operation instead.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_SetCustomerBillingAgreement
   * @param string $returnUrl
   * @param string $cancelUrl
   * @param array $options
   * @return array
   */
  public function setCustomerBillingAgreement($returnUrl, $cancelUrl = null,
      $options = null)
  {
    // Build params
    if( is_array($returnUrl) ) {
      $params = $returnUrl;
    } else {
      if( is_array($options) ) {
        $params = $options;
      } else {
        $params = array();
      }
      $params['RETURNURL'] = $returnUrl;
      if( null !== $cancelUrl ) {
        $params['CANCELURL'] = $cancelUrl;
      }
    }

    // Preprocess params
    if( $this->_preProcessRequest ) {
      $params = $this->_preProcessRequestData($params, array(
        'BILLINGAGREEMENTDESCRIPTION' => 'L_BILLINGAGREEMENTDESCRIPTION0',
        'BILLINGAGREEMENTDESCRIPTIONS' => 'L_BILLINGAGREEMENTDESCRIPTION%d',
      ));
    }

    // Check params
    $this->_checkParams($params, array(
      'RETURNURL', 'CANCELURL',
    ), array(
      'LOCALECODE', 'PAGESTYLE', 'HDRIMG', 'HDRBORDERCOLOR', 'HDRBACKCOLOR',
      'PAYFLOWCOLOR', 'EMAIL', 
      'L_BILLINGAGREEMENTDESCRIPTION%d', 'L_PAYMENTTYPE%d',
      'L_BILLINGAGREEMENTCUSTOM%d', 
    ));
    
    // Send request
    $client = $this->_prepareHttpClient('SetCustomerBillingAgreement');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }



  // Transactions

  /**
   * Accept a pending transaction held by Fraud Management Filters.
   * 
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_ManagePendingTransactionStatus
   * @param string $transactionId
   * @return array
   */
  public function acceptPendingTransaction($transactionId)
  {
    return $this->managePendingTransaction($transactionId, 'Accept');
  }

  /**
   * Authorize a payment.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoAuthorization
   * @param string $transactionId
   * @param string $amount
   * @param string $transactionEntity
   * @param string $currencyCode
   * @return array
   */
  public function authorizeTransaction($transactionId, $amount = null,
      $transactionEntity = null, $currencyCode = null)
  {
    if( is_array($transactionId) ) {
      $params = $transactionId;
    } else {
      $params = array();
      $params['TRANSACTIONID'] = $transactionId;
      if( null !== $amount ) {
        $params['AMT'] = $amount;
      }
      if( null !== $transactionEntity ) {
        $params['TRANSACTIONENTITY'] = $transactionEntity;
      }
      if( null !== $currencyCode ) {
        $params['CURRENCYCODE'] = $currencyCode;
      }
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'TRANSACTIONID', 'AMT',
    ), array(
      'TRANSACTIONENTITY', 'CURRENCYCODE',
    ));

    // Send request
    $client = $this->_prepareHttpClient('DoAuthorization');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Deny a pending transaction held by Fraud Management Filters.
   * 
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_ManagePendingTransactionStatus
   * @param string $transactionId
   * @return array
   */
  public function denyPendingTransaction($transactionId)
  {
    return $this->managePendingTransaction($transactionId, 'Deny');
  }

  /**
   * Obtain information about a specific transaction.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_GetTransactionDetails
   * @param string $transactionId
   * @return array
   */
  public function detailTransaction($transactionId)
  {
    // Build params
    if( is_array($transactionId) ) {
      $params = $transactionId;
    } else {
      $params = array();
      $params['TRANSACTIONID'] = $transactionId;
    }

    // Check params
    $params = $this->_checkParams($params, 'TRANSACTIONID');

    // Send request
    $client = $this->_prepareHttpClient('GetTransactionDetails');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    // Post process response data?
    if( $this->_postProcessResponse ) {
      $this->_postProcessResponseData($responseData, array(
        'L_DESC%d' => array('ITEMS', 'DESC'),
        'L_NUMBER%d' => array('ITEMS', 'NUMBER'),
        'L_QTY%d' => array('ITEMS', 'QTY'),
        'L_AMT%d' => array('ITEMS', 'AMT'),
        'L_OPTIONSNAME%d' => array('ITEMS', 'OPTIONSNAME'),
        'L_OPTIONSVALUE%d' => array('ITEMS', 'OPTIONSVALUE'),
      ), array(
        'RECEIVEREMAIL', 'RECEIVERID', 'EMAIL', 'PAYERID', 'PAYERSTATUS',
        'SHIPTOCOUNTRYCODE', 'PAYERBUSINESS', 'SALUTATION', 'FIRSTNAME',
        'MIDDLENAME', 'LASTNAME', 'SUFFIX', 'ADDRESSOWNER', 'ADDRESSSTATUS',
        'SHIPTONAME', 'SHIPTOSTREET', 'SHIPTOSTREET2', 'SHIPTOCITY',
        'SHIPTOSTATE', 'SHIPTOZIP', 'SHIPTOCOUNTRYCODE', 'SHIPTOPHONENUM',
        'TRANSACTIONID', 'PARENTTRANSACTIONID', 'RECEIPTID',
        'TRANSACTIONTYPE', 'PAYMENTTYPE', 'ORDERTIME', 'AMT', 'CURRENCYCODE',
        'FEEAMT', 'SETTLEAMT', 'TAXAMT', 'EXCHANGERATE', 'PAYMENTSTATUS',
        'PENDINGREASON', 'REASONCODE', 'PROTECTIONELIGIBILITY',
        'PROTECTIONELIGIBILITYTYPE', 'INVNUM', 'CUSTOM', 'NOTE', 'SALESTAX',
      ));
    }

    return $responseData;
  }

  /**
   * Accept or deny a pending transaction held by Fraud Management Filters.
   * 
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_ManagePendingTransactionStatus
   * @param string $transactionId
   * @param string $action
   * @return array
   */
  public function managePendingTransaction($transactionId, $action = null)
  {
    // Build params
    if( is_array($transactionId) ) {
      $params = $transactionId;
    } else {
      $params = array();
      $params['TRANSACTIONID'] = $transactionId;
    }
    if( null !== $action ) {
      $params['ACTION'] = $action;
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'TRANSACTIONID', 'ACTION',
    ));

    // Send request
    $client = $this->_prepareHttpClient('ManagePendingTransactionStatus');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Process a payment from a buyer’s account, which is identified by a
   * previous transaction.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoReferenceTransaction
   * @param string $transactionId
   * @param string $paymentAction
   * @param boolean $returnFraudDetails
   * @param mixed $softDescriptor
   * @return array
   */
  public function referenceTransaction($transactionId, $paymentAction = null,
      $returnFraudDetails = null, $softDescriptor = null)
  {
    // Build params
    if( is_array($transactionId) ) {
      $params = $transactionId;
    } else {
      $params = array();
      $params['TRANSACTIONID'] = $transactionId;
      if( null !== $paymentAction ) {
        $params['PAYMENTACTION'] = $paymentAction;
      }
      if( null !== $returnFraudDetails ) {
        $params['RETURNFMFDETAILS'] = $returnFraudDetails;
      }
      if( null !== $softDescriptor ) {
        $params['SOFTDESCRIPTOR'] = $softDescriptor;
      }
    }

    // Check params
    $params = $this->_checkParams($params, 'TRANSACTIONID', array(
      'PAYMENTACTION', 'RETURNFMFDETAILS', 'SOFTDESCRIPTOR',
    ));

    // Send request
    $client = $this->_prepareHttpClient('DoReferenceTransaction');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    // Post process response data?
    if( $this->_postProcessResponse ) {
      $responseData = $this->_postProcessResponseData($responseData, array(
        'L_FMF%sID%d' => array('FMF', 'ID'),
        'L_FMF%sNAME%d' => array('FMF', 'NAME'),
      ), array(
        'AVSCODE', 'CVV2MATCH', 'BILLINGAGREEMENTID', 'TRANSACTIONID',
        'PARENTTRANSACTIONID', 'RECEIPTID', 'TRANSACTIONTYPE', 'PAYMENTTYPE',
        'ORDERTIME', 'AMT', 'CURRENCYCODE', 'FEEAMT', 'SETTLEAMT', 'TAXAMT',
        'EXCHANGERATE', 'PAYMENTSTATUS', 'PENDINGREASON', 'REASONCODE',
        'PROTECTIONELIGIBILITY', 'PROTECTIONELIGIBILITYTYPE', 
      ));
    }

    return $responseData;
  }

  /**
   * Issue a refund to the PayPal account holder associated with a transaction.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_RefundTransaction
   * @param string $transactionId
   * @param string $invoiceId
   * @param string $refundType
   * @param string $amount
   * @param string $currencyCode
   * @param string $note
   * @return array
   */
  public function refundTransaction($transactionId, $invoiceId = null,
      $refundType = null, $amount = null, $currencyCode = null, $note = null)
  {
    // Build params
    if( is_array($transactionId) ) {
      $params = $transactionId;
    } else {
      if( is_array($invoiceId) && func_num_args() == 2 ) {
        $params = $invoiceId;
      } else {
        $params = array();
        if( null !== $invoiceId ) {
          $params['INVOICEID'] = $invoiceId;
        }
      }
      if( null !== $refundType ) {
        $params['REFUNDTYPE'] = $refundType;
      }
      if( null !== $amount ) {
        $params['AMT'] = $amount;
      }
      if( null !== $currencyCode ) {
        $params['CURRENCYCODE'] = $currencyCode;
      }
      if( null !== $note ) {
        $params['NOTE'] = $note;
      }
      $params['TRANSACTIONID'] = $transactionId;
    }

    // Check params
    $params = $this->_checkParams($params, 'TRANSACTIONID', array(
      'INVOICEID', 'REFUNDTYPE', 'AMT', 'CURRENCYCODE', 'NOTE',
    ));

    // Send request
    $client = $this->_prepareHttpClient('RefundTransaction');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Search transaction history for transactions that meet the specified criteria.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_TransactionSearch
   * @param array $params
   * @return array
   */
  public function searchTransactions($params)
  {
    // Check params
    $params = $this->_checkParams($params, 'STARTDATE', array(
      'ENDDATE', 'EMAIL', 'RECEIVER', 'RECEIPTID', 'TRANSACTIONID',
      'INVNUM', 'ACCT', 'AUCTIONITEMNUMBER', 'TRANSACTIONCLASS', 'AMT',
      'CURRENCYCODE', 'STATUS', 
    ));

    // Send request
    $client = $this->_prepareHttpClient('TransactionSearch');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    // Post process response data?
    if( $this->_postProcessResponse ) {
      $responseData = $this->_postProcessResponseData($responseData, array(
        'L_TIMESTAMP%d' => array('TRANSACTIONS', 'TIMESTAMP'),
        'L_TIMEZONE%d' => array('TRANSACTIONS', 'TIMEZONE'),
        'L_TYPE%d' => array('TRANSACTIONS', 'TYPE'),
        'L_EMAIL%d' => array('TRANSACTIONS', 'EMAIL'),
        'L_NAME%d' => array('TRANSACTIONS', 'NAME'),
        'L_TRANSACTIONID%d' => array('TRANSACTIONS', 'TRANSACTIONID'),
        'L_STATUS%d' => array('TRANSACTIONS', 'STATUS'),
        'L_AMT%d' => array('TRANSACTIONS', 'AMT'),
        'L_FEEAMT%d' => array('TRANSACTIONS', 'FEEAMT'),
        'L_NETAMT%d' => array('TRANSACTIONS', 'NETAMT'),
      ));
    }

    return $responseData;
  }

  
  
  
  // Express Checkout
  
  /**
   * Updates the PayPal Review page with shipping options, insurance, and tax
   * information.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_CallbackAPI
   * @param array $params
   * @return array
   */
  public function callback($params)
  {
    // Build params
    if( !is_array($params) ) {
      $params = array();
    }

    // Preprocess params
    if( $this->_preProcessRequest ) {
      $params = $this->_preProcessRequestData($params, array(
        // Standard method
        'ITEMS' => array(
          'NAME' => 'L_NAME%d',
          'NUMBER' => 'L_NUMBER%d',
          'DESC' => 'L_DESC%d',
          'AMT' => 'L_AMT%d',
          'QTY' => 'L_QTY%d',
          'ITEMWEIGHTVALUE' => 'L_ITEMWEIGHTVALUE%d',
          'ITEMWEIGHTUNIT' => 'L_ITEMWEIGHTUNIT%d',
          'ITEMHEIGHTVALUE' => 'L_ITEMHEIGHTVALUE%d',
          'ITEMHEIGHTUNIT' => 'L_ITEMHEIGHTUNIT%d',
          'ITEMWIDTHVALUE' => 'L_ITEMWIDTHVALUE%d',
          'ITEMWIDTHUNIT' => 'L_ITEMWIDTHUNIT%d',
          'ITEMLENGTHVALUE' => 'L_ITEMLENGTHVALUE%d',
          'ITEMLENGTHUNIT' => 'L_ITEMLENGTHUNIT%d',
        ),

        // Alternate method
        'L_NAME' => 'L_NAME%d',
        'L_NUMBER' => 'L_NUMBER%d',
        'L_DESC' => 'L_DESC%d',
        'L_AMT' => 'L_AMT%d',
        'L_QTY' => 'L_QTY%d',
        'L_ITEMWEIGHTVALUE' => 'L_ITEMWEIGHTVALUE%d',
        'L_ITEMWEIGHTUNIT' => 'L_ITEMWEIGHTUNIT%d',
        'L_ITEMHEIGHTVALUE' => 'L_ITEMHEIGHTVALUE%d',
        'L_ITEMHEIGHTUNIT' => 'L_ITEMHEIGHTUNIT%d',
        'L_ITEMWIDTHVALUE' => 'L_ITEMWIDTHVALUE%d',
        'L_ITEMWIDTHUNIT' => 'L_ITEMWIDTHUNIT%d',
        'L_ITEMLENGTHVALUE' => 'L_ITEMLENGTHVALUE%d',
        'L_ITEMLENGTHUNIT' => 'L_ITEMLENGTHUNIT%d',
      ));
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'CURRENCYCODE', 
    ), array(
      'LOCALECODE', 'L_NAME%d', 'L_NUMBER%d', 'L_DESC%d', 'L_AMT%d', 'L_QTY%d',
      'L_ITEMWEIGHTVALUE%d', 'L_ITEMWEIGHTUNIT%d', 'L_ITEMHEIGHTVALUE%d',
      'L_ITEMHEIGHTUNIT%d', 'L_ITEMWIDTHVALUE%d', 'L_ITEMWIDTHUNIT%d',
      'L_ITEMLENGTHVALUE%d', 'L_ITEMLENGTHUNIT%d', 'SHIPTOSTREET',
      'SHIPTOSTREET2', 'SHIPTOCITY', 'SHIPTOSTATE', 'SHIPTOZIP',
      'SHIPTOCOUNTRY', 
    ));

    // Send request
    $client = $this->_prepareHttpClient('Callback');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    // Post process response data?
    if( $this->_postProcessResponse ) {
      $responseData = $this->_postProcessResponseData($responseData, array(
        'L_SHIPPINGOPTIONNAME%d' => array('ITEMS', 'SHIPPINGOPTIONNAME'),
        'L_SHIPPINGOPTIONLABEL%d' => array('ITEMS', 'SHIPPINGOPTIONLABEL'),
        'L_SHIPPINGOPTIONAMOUNT%d' => array('ITEMS', 'SHIPPINGOPTIONAMOUNT'),
        'L_TAXAMT%d' => array('ITEMS', 'TAXAMT'),
        'L_INSURANCEAMOUNT%d' => array('ITEMS', 'INSURANCEAMOUNT'),
      ), array(
        'OFFERINSURANCEOPTION', 'L_SHIPPINGOPTIONISDEFAULT',
        'NO_SHIPPING_OPTION_DETAILS',
      ));
    }

    return $responseData;
  }

  /**
   * Completes an Express Checkout transaction.
   * If you set up a billing agreement in your SetExpressCheckout API call,
   * the billing agreement is created when you call the
   * DoExpressCheckoutPayment API operation.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoExpressCheckoutPayment
   * @param string $token
   * @param string $payerId
   * @param array $options
   * @return array
   */
  public function doExpressCheckoutPayment($token, $payerId = null, $options = null)
  {
    // Build params
    if( is_array($token) ) {
      $params = $token;
    } else {
      if( is_array($options) ) {
        $params = $options;
      } else {
        $params = array();
      }
      $params['TOKEN'] = $token;
      if( null !== $payerId ) {
        $params['PAYERID'] = $payerId;
      }
    }

    // Check params
    if( version_compare($this->_version, '63.0', '>=') ) {
      $params = $this->_checkParams($params, array(
        'TOKEN', 'PAYERID',
      ), array(
        'RETURNFMFDETAILS', 'GIFTMESSAGE', 'GIFTRECEIPTENABLE',
        'GIFTWRAPNAME', 'GIFTWRAPAMOUNT', 'BUYERMARKETINGEMAIL', 'SURVEYQUESTION',
        'SURVEYCHOICESELECTED', 'BUTTONSOURCE', 'INSURANCEOPTIONSELECTED',
        'SHIPPINGOPTIONISDEFAULT', 'SHIPPINGOPTIONAMOUNT', 'SHIPPINGOPTIONNAME',

        // New
        'PAYMENTREQUEST_%d_AMT', 'PAYMENTREQUEST_%d_CURRENCYCODE',
        'PAYMENTREQUEST_%d_ITEMAMT', 'PAYMENTREQUEST_%d_SHIPPINGAMT',
        'PAYMENTREQUEST_%d_INSURANCEAMT', 'PAYMENTREQUEST_%d_SHIPDISCAMT',
        'PAYMENTREQUEST_%d_INSURANCEOPTIONOFFERED',
        'PAYMENTREQUEST_%d_HANDLINGAMT', 'PAYMENTREQUEST_%d_TAXAMT',
        'PAYMENTREQUEST_%d_DESC', 'PAYMENTREQUEST_%d_CUSTOM',
        'PAYMENTREQUEST_%d_INVNUM', 'PAYMENTREQUEST_%d_NOTIFYURL',
        'PAYMENTREQUEST_%d_NOTETEXT', 'PAYMENTREQUEST_%d_SOFTDESCRIPTOR',
        'PAYMENTREQUEST_%d_TRANSACTIONID',
        'PAYMENTREQUEST_%d_ALLOWEDPAYMENTMETHOD',
        'PAYMENTREQUEST_%d_PAYMENTACTION', 'PAYMENTREQUEST_%d_PAYMENTREQUESTID',
        'PAYMENTREQUEST_%d_SHIPTONAME', 'PAYMENTREQUEST_%d_SHIPTOSTREET',
        'PAYMENTREQUEST_%d_SHIPTOSTREET2', 'PAYMENTREQUEST_%d_SHIPTOCITY',
        'PAYMENTREQUEST_%d_SHIPTOSTATE', 'PAYMENTREQUEST_%d_SHIPTOZIP',
        'PAYMENTREQUEST_%d_SHIPTOCOUNTRYCODE',
        'PAYMENTREQUEST_%d_SHIPTOPHONENUM',
        'L_PAYMENTREQUEST_%d_NAME%d', 'L_PAYMENTREQUEST_%d_DESC%d',
        'L_PAYMENTREQUEST_%d_AMT%d', 'L_PAYMENTREQUEST_%d_NUMBER%d',
        'L_PAYMENTREQUEST_%d_QTY%d', 'L_PAYMENTREQUEST_%d_TAXAMT%d',
        'L_PAYMENTREQUEST_%d_ITEMWEIGHTVALUE%d',
        'L_PAYMENTREQUEST_%d_ITEMWEIGHTUNIT%d',
        'L_PAYMENTREQUEST_%d_ITEMLENGTHVALUE%d',
        'L_PAYMENTREQUEST_%d_ITEMLENGTHUNIT%d',
        'L_PAYMENTREQUEST_%d_ITEMWIDTHVALUE%d',
        'L_PAYMENTREQUEST_%d_ITEMWIDTHUNIT%d',
        'L_PAYMENTREQUEST_%d_ITEMHEIGHTVALUE%d',
        'L_PAYMENTREQUEST_%d_ITEMHEIGHTUNIT%d',
        'L_PAYMENTREQUEST_%d_ITEMURL%d', 'L_PAYMENTREQUEST_%d_EBAYITEMNUMBER%d',
        'L_PAYMENTREQUESST_%d_EBAYITEMAUCTIONTXNID%d',
        'L_PAYMENTREQUEST_%d_EBAYITEMORDERID%d',
        'L_PAYMENTREQUEST_%d_EBAYCARTID%d', 'PAYMENTREQUEST_%d_SELLERID',
        'PAYMENTREQUEST_%d_SELLERUSERNAME',
        'PAYMENTREQUEST_%d_SELLERREGISTRATIONDATE',
      ));
    } else {
      $params = $this->_checkParams($params, array(
        'TOKEN', 'PAYERID',
      ), array(
        'RETURNFMFDETAILS', 'GIFTMESSAGE', 'GIFTRECEIPTENABLE',
        'GIFTWRAPNAME', 'GIFTWRAPAMOUNT', 'BUYERMARKETINGEMAIL', 'SURVEYQUESTION',
        'SURVEYCHOICESELECTED', 'BUTTONSOURCE', 'INSURANCEOPTIONSELECTED',
        'SHIPPINGOPTIONISDEFAULT', 'SHIPPINGOPTIONAMOUNT', 'SHIPPINGOPTIONNAME',
        
        // Deprecated
        'PAYMENTACTION', 'AMT', 'CURRENCYCODE', 'ITEMAMT', 'SHIPPINGAMT',
        'INSURANCEAMT', 'SHIPPINGDISCAMT', 'INSURANCEOPTIONOFFERED',
        'HANDLINGAMT', 'TAXAMT', 'DESC', 'CUSTOM', 'INVNUM', 'NOTIFYURL',
        'NOTETEXT', 'SOFTDESCRIPTOR', 'TRANSACTIONID', 'ALLOWEDPAYMENTMETHOD',
        'PAYMENTREQUESTID', 'SHIPTONAME', 'SHIPTOSTREET', 'SHIPTOSTREET2',
        'SHIPTOCITY', 'SHIPTOSTATE', 'SHIPTOZIP', 'SHIPTOCOUNTRY',
        'SHIPTOPHONENUM', 'L_NAME%d', 'L_DESC%d', 'L_AMT%d', 'L_NUMBER%d',
        'L_QTY%d', 'L_TAXAMT%d', 'L_ITEMWEIGHTVALUE%d', 'L_ITEMWEIGHTUNIT%d',
        'L_ITEMLENGTHVALUE%d', 'L_ITEMLENGHTUNIT%d', 'L_ITEMWIDTHVALUE%d',
        'L_ITEMWIDTHUNIT%d', 'L_ITEMHEIGHTVALUE%d', 'L_ITEMHEIGHTUNIT%d',
        'L_ITEMURL%d', 'L_EBAYITEMNUMBER%d', 'L_EBAYITEMAUCTIONTXNID%d',
        'L_EBAYITEMORDERID%d', 'L_EBAYITEMCARTID%d', 'SELLERID', 'SELLERUSERNAME',
        'SELLERREGISTRATIONDATE',
      ));
    }

    // Send request
    $client = $this->_prepareHttpClient('DoExpressCheckoutPayment');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    // Post process response data?
    if( $this->_postProcessResponse ) {
      if( version_compare($this->_version, '63.0', '>=') ) {
        $responseData = $this->_postProcessResponseData($responseData, array(
          // New
          'PAYMENTINFO_%d_TRANSACTIONID' => array('PAYMENTINFO', 'TRANSACTIONID'),
          'PAYMENTINFO_%d_TRANSACTIONTYPE' => array('PAYMENTINFO', 'TRANSACTIONTYPE'),
          'PAYMENTINFO_%d_PAYMENTTYPE' => array('PAYMENTINFO', 'PAYMENTTYPE'),
          'PAYMENTINFO_%d_ORDERTIME' => array('PAYMENTINFO', 'ORDERTIME'),
          'PAYMENTINFO_%d_AMT' => array('PAYMENTINFO', 'AMT'),
          'PAYMENTINFO_%d_CURRENCYCODE' => array('PAYMENTINFO', 'CURRENCYCODE'),
          'PAYMENTINFO_%d_FEEAMT' => array('PAYMENTINFO', 'FEEAMT'),
          'PAYMENTINFO_%d_SETTLEAMT' => array('PAYMENTINFO', 'SETTLEAMT'),
          'PAYMENTINFO_%d_TAXAMT' => array('PAYMENTINFO', 'TAXAMT'),
          'PAYMENTINFO_%d_EXCHANGERATE' => array('PAYMENTINFO', 'EXCHANGERATE'),
          'PAYMENTINFO_%d_PAYMENTSTATUS' => array('PAYMENTINFO', 'PAYMENTSTATUS'),
          'PAYMENTINFO_%d_PENDINGREASON' => array('PAYMENTINFO', 'PENDINGREASON'),
          'PAYMENTINFO_%d_REASONCODE' => array('PAYMENTINFO', 'REASONCODE'),
          'PAYMENTINFO_%d_PROTECTIONELIGIBILITY' => array('PAYMENTINFO', 'PROTECTIONELIGIBILITY'),
          'PAYMENTINFO_%d_PROTECTIONELIGIBILITYTYPE' => array('PAYMENTINFO', 'PROTECTIONELIGIBILITYTYPE'),
          'PAYMENTINFO_%d_PAYMENTREQUESTID' => array('PAYMENTINFO', 'PAYMENTREQUESTID'),
          'PAYMENTINFO_%d_FMF%sID%d' => array('PAYMENTINFO', 'FMFILTER', 'ID'),
          'PAYMENTINFO_%d_FMF%sNAME%d' => array('PAYMENTINFO', 'FMFILTER', 'NAME'),

          // Very new (no <63.0 equivalent?)
          'PAYMENTREQUEST_%d_SHORTMESSAGE' => array('PAYMENTREQUEST', 'SHORTMESSAGE'),
          'PAYMENTREQUEST_%d_LONGMESSAGE' => array('PAYMENTREQUEST', 'LONGMESSAGE'),
          'PAYMENTREQUEST_%d_ERRORCODE' => array('PAYMENTREQUEST', 'ERRORCODE'),
          'PAYMENTREQUEST_%d_SEVERITYCODE' => array('PAYMENTREQUEST', 'SEVERITYCODE'),
          'PAYMENTREQUEST_%d_ACK' => array('PAYMENTREQUEST', 'ACK'),
          'PAYMENTREQUEST_%d_SELLERPAYPALACCOUNTID' => array('PAYMENTREQUEST', 'SELLERPAYPALACCOUNTID'),

          // These were what was being sent back, not the above set?
          'PAYMENTINFO_%d_ERRORCODE' => array('PAYMENTINFO', 'ERRORCODE'),
          'PAYMENTINFO_%d_ACK' => array('PAYMENTINFO', 'ACK'),
          
        ), array(
          'TOKEN', 'PAYMENTTYPE', 'NOTE', 'REDIRECTREQUIRED',
          'SUCCESSPAGEREDIRECTREQUESTED',

          'SHIPPINGCALCULATIONMODE', 'INSURANCEOPTIONSELECTED',
          'SHIPPINGOPTIONISDEFAULT', 'SHIPPINGOPTIONAMOUNT',
          'SHIPPINGOPTIONNAME', 
        ));
      } else {
        $responseData = $this->_postProcessResponseData($responseData, array(
          // Deprecated
          'L_FMF%sID%d' => array('FMFILTER', 'ID'),
          'L_FMF%sNAME%d' => array('FMFILTER', 'NAME'),
        ), array(
          'TOKEN', 'PAYMENTTYPE', 'NOTE', 'REDIRECTREQUIRED',
          'SUCCESSPAGEREDIRECTREQUESTED',

          'SHIPPINGCALCULATIONMODE', 'INSURANCEOPTIONSELECTED',
          'SHIPPINGOPTIONISDEFAULT', 'SHIPPINGOPTIONAMOUNT',
          'SHIPPINGOPTIONNAME', 

          // Deprecated
          'TRANSACTIONID', 'TRANSACTIONTYPE', 'PAYMENTTYPE', 'ORDERTIME', 'AMT',
          'CURRENCYCODE', 'FEEAMT', 'SETTLEAMT', 'TAXAMT', 'EXCHANGERATE',
          'PAYMENTSTATUS', 'PENDINGREASON', 'REASONCODE', 'PAYMENTREQUESTID', 
        ));
      }
    }
    
    return $responseData;
  }

  /**
   * Obtain information about an Express Checkout transaction.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_GetExpressCheckoutDetails
   * @param string $token
   * @return array
   */
  public function detailExpressCheckout($token)
  {
    // Build params
    if( is_array($token) ) {
      $params = $token;
    } else {
      $params = array();
      $params['TOKEN'] = $token;
    }

    // Check params
    $params = $this->_checkParams($params, 'TOKEN');
    
    // Send request
    $client = $this->_prepareHttpClient('GetExpressCheckoutDetails');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    // Post process response data?
    if( $this->_postProcessResponse ) {
      if( version_compare($this->_version, '63.0', '>=') ) {
        $responseData = $this->_postProcessResponseData($responseData, array(
          // New
          'PAYMENTREQUEST_%d_SHIPTONAME' => array('PAYMENTREQUEST', 'SHIPTONAME'),
          'PAYMENTREQUEST_%d_SHIPTOSTREET' => array('PAYMENTREQUEST', 'SHIPTOSTREET'),
          'PAYMENTREQUEST_%d_SHIPTOSTREET2' => array('PAYMENTREQUEST', 'SHIPTOSTREET2'),
          'PAYMENTREQUEST_%d_SHIPTOCITY' => array('PAYMENTREQUEST', 'SHIPTOCITY'),
          'PAYMENTREQUEST_%d_SHIPTOSTATE' => array('PAYMENTREQUEST', 'SHIPTOSTATE'),
          'PAYMENTREQUEST_%d_SHIPTOZIP' => array('PAYMENTREQUEST', 'SHIPTOZIP'),
          'PAYMENTREQUEST_%d_SHIPTOCOUNTRYCODE' => array('PAYMENTREQUEST', 'SHIPTOCOUNTRYCODE'),
          'PAYMENTREQUEST_%d_SHIPTOCOUNTRYNAME' => array('PAYMENTREQUEST', 'SHIPTOCOUNTRYNAME'),
          'PAYMENTREQUEST_%d_SHIPTOPHONENUM' => array('PAYMENTREQUEST', 'SHIPTOPHONENUM'),
          'PAYMENTREQUEST_%d_ADDRESSSTATUS' => array('PAYMENTREQUEST', 'ADDRESSSTATUS'),
          
          'PAYMENTREQUEST_%d_AMT' => array('PAYMENTREQUEST', 'AMT'),
          'PAYMENTREQUEST_%d_CURRENCYCODE' => array('PAYMENTREQUEST', 'CURRENCYCODE'),
          'PAYMENTREQUEST_%d_ITEMAMT' => array('PAYMENTREQUEST', 'ITEMAMT'),
          'PAYMENTREQUEST_%d_SHIPPINGAMT' => array('PAYMENTREQUEST', 'SHIPPINGAMT'),
          'PAYMENTREQUEST_%d_INSURANCEAMT' => array('PAYMENTREQUEST', 'INSURANCEAMT'),
          'PAYMENTREQUEST_%d_SHIPDISCAMT' => array('PAYMENTREQUEST', 'SHIPDISCAMT'),
          'PAYMENTREQUEST_%d_INSURANCEOPTIONOFFERED' => array('PAYMENTREQUEST', 'INSURANCEOPTIONOFFERED'),
          'PAYMENTREQUEST_%d_HANDLINGAMT' => array('PAYMENTREQUEST', 'HANDLINGAMT'),
          'PAYMENTREQUEST_%d_TAXAMT' => array('PAYMENTREQUEST', 'TAXAMT'),
          'PAYMENTREQUEST_%d_DESC' => array('PAYMENTREQUEST', 'DESC'),
          'PAYMENTREQUEST_%d_CUSTOM' => array('PAYMENTREQUEST', 'CUSTOM'),
          'PAYMENTREQUEST_%d_INVNUM' => array('PAYMENTREQUEST', 'INVNUM'),
          'PAYMENTREQUEST_%d_NOTIFYURL' => array('PAYMENTREQUEST', 'NOTIFYURL'),
          'PAYMENTREQUEST_%d_NOTETEXT' => array('PAYMENTREQUEST', 'NOTETEXT'),
          'PAYMENTREQUEST_%d_TRANSACTIONID' => array('PAYMENTREQUEST', 'TRANSACTIONID'),
          'PAYMENTREQUEST_%d_ALLOWEDPAYMENTMETHOD' => array('PAYMENTREQUEST', 'ALLOWEDPAYMENTMETHOD'),
          'PAYMENTREQUEST_%d_PAYMENTREQUESTID' => array('PAYMENTREQUEST', 'PAYMENTREQUESTID'),

          'L_PAYMENTREQUEST_%d_NAME%d' => array('PAYMENTREQUEST', 'ITEMS', 'NAME'),
          'L_PAYMENTREQUEST_%d_DESC%d' => array('PAYMENTREQUEST', 'ITEMS', 'DESC'),
          'L_PAYMENTREQUEST_%d_AMT%d' => array('PAYMENTREQUEST', 'ITEMS', 'AMT'),
          'L_PAYMENTREQUEST_%d_NUMBER%d' => array('PAYMENTREQUEST', 'ITEMS', 'NUMBER'),
          'L_PAYMENTREQUEST_%d_QTY%d' => array('PAYMENTREQUEST', 'ITEMS', 'QTY'),
          'L_PAYMENTREQUEST_%d_TAXAMT%d' => array('PAYMENTREQUEST', 'ITEMS', 'TAXAMT'),
          'L_PAYMENTREQUEST_%d_ITEMWEIGHTVALUE%d' => array('PAYMENTREQUEST', 'ITEMS', 'ITEMWEIGHTVALUE'),
          'L_PAYMENTREQUEST_%d_ITEMWEIGHTUNIT%d' => array('PAYMENTREQUEST', 'ITEMS', 'ITEMWEIGHTUNIT'),
          'L_PAYMENTREQUEST_%d_ITEMLENGTHVALUE%d' => array('PAYMENTREQUEST', 'ITEMS', 'ITEMLENGTHVALUE'),
          'L_PAYMENTREQUEST_%d_ITEMLENGTHUNIT%d' => array('PAYMENTREQUEST', 'ITEMS', 'ITEMLENGTHUNIT'),
          'L_PAYMENTREQUEST_%d_ITEMWIDTHVALUE%d' => array('PAYMENTREQUEST', 'ITEMS', 'ITEMWIDTHVALUE'),
          'L_PAYMENTREQUEST_%d_ITEMWIDTHUNIT%d' => array('PAYMENTREQUEST', 'ITEMS', 'ITEMWIDTHUNIT'),
          'L_PAYMENTREQUEST_%d_ITEMHEIGHTVALUE%d' => array('PAYMENTREQUEST', 'ITEMS', 'ITEMHEIGHTVALUE'),
          'L_PAYMENTREQUEST_%d_ITEMHEIGHTUNIT%d' => array('PAYMENTREQUEST', 'ITEMS', 'ITEMHEIGHTUNIT'),
          'L_PAYMENTREQUEST_%d_EBAYITEMNUMBER%d' => array('PAYMENTREQUEST', 'ITEMS', 'EBAYITEMNUMBER'),
          'L_PAYMENTREQUEST_%d_EBAYITEMAUCTIONTXNID%d' => array('PAYMENTREQUEST', 'ITEMS', 'EBAYITEMAUCTIONTXNID'),
          'L_PAYMENTREQUEST_%d_EBAYITEMORDERID%d' => array('PAYMENTREQUEST', 'ITEMS', 'EBAYITEMORDERID'),
          'L_PAYMENTREQUEST_%d_EBAYITEMCARTID%d' => array('PAYMENTREQUEST', 'ITEMS', 'EBAYITEMCARTID'),

          // Very new (no <63.0 equivalent?)
          'PAYMENTREQUEST_%d_SELLERPAYPALACCOUNTID' => array('PAYMENTREQUEST', 'SELLERPAYPALACCOUNTID'),
          'PAYMENTREQUEST_%d_TRANSACTIONID' => array('PAYMENTREQUEST', 'TRANSACTIONID'),
          'PAYMENTREQUEST_%d_PAYMENTREQUESTID' => array('PAYMENTREQUEST', 'PAYMENTREQUESTID'),
          'PAYMENTREQUEST_%d_SHORTMESSAGE' => array('PAYMENTREQUEST', 'SHORTMESSAGE'),
          'PAYMENTREQUEST_%d_LONGMESSAGE' => array('PAYMENTREQUEST', 'LONGMESSAGE'),
          'PAYMENTREQUEST_%d_ERRORCODE' => array('PAYMENTREQUEST', 'ERRORCODE'),
          'PAYMENTREQUEST_%d_SEVERITYCODE' => array('PAYMENTREQUEST', 'SEVERITYCODE'),
          'PAYMENTREQUEST_%d_ACK' => array('PAYMENTREQUEST', 'ACK'),
          
          // These were what was being sent back, not the above set?
          'PAYMENTREQUESTINFO_%d_SELLERPAYPALACCOUNTID' => array('PAYMENTREQUESTINFO', 'SELLERPAYPALACCOUNTID'),
          'PAYMENTREQUESTINFO_%d_TRANSACTIONID' => array('PAYMENTREQUESTINFO', 'TRANSACTIONID'),
          'PAYMENTREQUESTINFO_%d_SHORTMESSAGE' => array('PAYMENTREQUESTINFO', 'SHORTMESSAGE'),
          'PAYMENTREQUESTINFO_%d_LONGMESSAGE' => array('PAYMENTREQUESTINFO', 'LONGMESSAGE'),
          'PAYMENTREQUESTINFO_%d_ERRORCODE' => array('PAYMENTREQUESTINFO', 'ERRORCODE'),
          'PAYMENTREQUESTINFO_%d_SEVERITYCODE' => array('PAYMENTREQUESTINFO', 'SEVERITYCODE'),
          'PAYMENTREQUESTINFO_%d_ACK' => array('PAYMENTREQUESTINFO', 'ACK'),
        ), array(
          'TOKEN', 'CUSTOM', 'INVNUM', 'PHONENUM', 'PAYPALADJUSTMENT',
          'NOTE', 'REDIRECTREQUIRED', 'CHECKOUTSTATUS', 'GIFTMESSAGE',
          'GIFTRECEIPTENABLE', 'GIFTWRAPNAME', 'GIFTWRAPAMOUNT',
          'BUYERMARKETINGEMAIL', 'SURVEYQUESTION', 'SURVEYCHOICESELECTED',
          'EMAIL', 'PAYERID', 'PAYERSTATUS', 'COUNTRYCODE', 'BUSINESS',
          'SALUTATION', 'FIRSTNAME', 'MIDDLENAME', 'LASTNAME', 'SUFFIX',

          'SHIPPINGCALCULATIONMODE', 'INSURANCEOPTIONSELECTED',
          'SHIPPINGOPTIONISDEFAULT', 'SHIPPINGOPTIONAMOUNT',
          'SHIPPINGOPTIONNAME', 
        ));
      } else {
        $responseData = $this->_postProcessResponseData($responseData, array(
          // Deprecated
          'L_NAME%d' => array('ITEMS', 'NAME'),
          'L_DESC%d' => array('ITEMS', 'DESC'),
          'L_AMT%d' => array('ITEMS', 'AMT'),
          'L_NUMBER%d' => array('ITEMS', 'NUMBER'),
          'L_QTY%d' => array('ITEMS', 'QTY'),
          'L_TAXAMT%d' => array('ITEMS', 'TAXAMT'),
          'L_ITEMWEIGHTVALUE%d' => array('ITEMS', 'ITEMWEIGHTVALUE'),
          'L_ITEMWEIGHTUNIT%d' => array('ITEMS', 'ITEMWEIGHTUNIT'),
          'L_ITEMLENGTHVALUE%d' => array('ITEMS', 'ITEMLENGTHVALUE'),
          'L_ITEMLENGTHUNIT%d' => array('ITEMS', 'ITEMLENGTHUNIT'),
          'L_ITEMWIDTHVALUE%d' => array('ITEMS', 'ITEMWIDTHVALUE'),
          'L_ITEMWIDTHUNIT%d' => array('ITEMS', 'ITEMWIDTHUNIT'),
          'L_ITEMHEIGHTVALUE%d' => array('ITEMS', 'ITEMHEIGHTVALUE'),
          'L_ITEMHEIGHTUNIT%d' => array('ITEMS', 'ITEMHEIGHTUNIT'),
          'EBAYITEMNUMBER%d' => array('ITEMS', 'EBAYITEMNUMBER'),
          'EBAYITEMAUCTIONTXNID%d' => array('ITEMS', 'EBAYITEMAUCTIONTXNID'),
          'EBAYITEMORDERID%d' => array('ITEMS', 'EBAYITEMORDERID'),
          'EBAYITEMCARTID%d' => array('ITEMS', 'EBAYITEMCARTID'),
        ), array(
          'TOKEN', 'CUSTOM', 'INVNUM', 'PHONENUM', 'PAYPALADJUSTMENT',
          'NOTE', 'REDIRECTREQUIRED', 'CHECKOUTSTATUS', 'GIFTMESSAGE',
          'GIFTRECEIPTENABLE', 'GIFTWRAPNAME', 'GIFTWRAPAMOUNT',
          'BUYERMARKETINGEMAIL', 'SURVEYQUESTION', 'SURVEYCHOICESELECTED',
          'EMAIL', 'PAYERID', 'PAYERSTATUS', 'COUNTRYCODE', 'BUSINESS',
          'SALUTATION', 'FIRSTNAME', 'MIDDLENAME', 'LASTNAME', 'SUFFIX',

          'SHIPPINGCALCULATIONMODE', 'INSURANCEOPTIONSELECTED',
          'SHIPPINGOPTIONISDEFAULT', 'SHIPPINGOPTIONAMOUNT',
          'SHIPPINGOPTIONNAME', 

          // Deprecated
          'SHIPTONAME', 'SHIPTOSTREET', 'SHIPTOSTREET2', 'SHIPTOCITY',
          'SHIPTOSTATE', 'SHIPTOZIP', 'SHIPTOCOUNTRY', 'SHIPTOPHONENUM',
          'ADDRESSSTATUS',
          'AMT', 'CURRENCYCODE', 'ITEMAMT', 'SHIPPINGAMT', 'INSURANCEAMT',
          'SHIPPINGDISCAMT', 'INSURANCEOPTIONOFFERED', 'HANDLINGAMT',
          'TAXAMT', 'DESC', 'CUSTOM', 'INVNUM', 'NOTIFYURL', 'NOTETEXT',
          'TRANSACTIONID', 'ALLOWEDPAYMENTMETHOD', 'PAYMENTREQUESTID',
        ));
      }
    }

    return $responseData;
  }

  /**
   * Initiates an Express Checkout transaction.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_SetExpressCheckout
   * @param array $params
   * @return array
   */
  public function setExpressCheckout(array $params)
  {
    // Check params
    if( version_compare($this->_version, '63.0', '>=') ) {
      // Preprocess params
      if( $this->_preProcessRequest ) {
        // Translate deprecated params into their equivalent
        $deprecatedMap = array(
          'AMT', 'CURRENCYCODE', 'ITEMAMT', 'SHIPPINGAMT', 'INSURANCEAMT',
          'SHIPDISCAMT', 'INSURANCEOPTIONOFFERED', 'HANDLINGAMT', 'TAXAMT',
          'DESC', 'CUSTOM', 'INVNUM', 'NOTIFYURL', 'NOTETEXT', 'SOFTDESCRIPTOR',
          'TRANSACTIONID', 'ALLOWEDPAYMENTMETHOD', 'PAYMENTACTION',
          'PAYMENTREQUESTID', 'SHIPTONAME', 'SHIPTOSTREET', 'SHIPTOSTREET2',
          'SHIPTOCITY', 'SHIPTOSTATE', 'SHIPTOZIP', 'SHIPTOCOUNTRYCODE',
          'SHIPTOPHONENUM', 'SELLERID', 'SELLERUSERNAME',
          'SELLERREGISTRATIONDATE', 'ITEMS',
          'SHIPTOCOUNTRY',
        );
        if( !isset($params['PAYMENTREQUESTS']) ) {
          $params['PAYMENTREQUESTS'] = array();
        }
        foreach( $deprecatedMap as $key ) {
          if( isset($params[$key]) && !isset($params['PAYMENTREQUESTS'][0][$key]) ) {
            $params['PAYMENTREQUESTS'][0][$key] = $params[$key];
          }
        }
        if( !empty($params['PAYMENTREQUESTS'][0]) ) {
          $params = array_diff_key($params, $params['PAYMENTREQUESTS'][0]);
        }

        // Preprocess params
        $params = $this->_preProcessRequestData($params, array(
          // Standard method
          'PAYMENTREQUESTS' => array(
            'AMT' => 'PAYMENTREQUEST_%d_AMT',
            'CURRENCYCODE' => 'PAYMENTREQUEST_%d_CURRENCYCODE',
            'ITEMAMT' => 'PAYMENTREQUEST_%d_ITEMAMT',
            'SHIPPINGAMT' => 'PAYMENTREQUEST_%d_SHIPPINGAMT',
            'INSURANCEAMT' => 'PAYMENTREQUEST_%d_INSURANCEAMT',
            'SHIPDISCAMT' => 'PAYMENTREQUEST_%d_SHIPDISCAMT',
            'INSURANCEOPTIONOFFERED' => 'PAYMENTREQUEST_%d_INSURANCEOPTIONOFFERED',
            'HANDLINGAMT' => 'PAYMENTREQUEST_%d_HANDLINGAMT',
            'TAXAMT' => 'PAYMENTREQUEST_%d_TAXAMT',
            'DESC' => 'PAYMENTREQUEST_%d_DESC',
            'CUSTOM' => 'PAYMENTREQUEST_%d_CUSTOM',
            'INVNUM' => 'PAYMENTREQUEST_%d_INVNUM',
            'NOTIFYURL' => 'PAYMENTREQUEST_%d_NOTIFYURL',
            'NOTETEXT' => 'PAYMENTREQUEST_%d_NOTETEXT',
            'SOFTDESCRIPTOR' => 'PAYMENTREQUEST_%d_SOFTDESCRIPTOR',
            'TRANSACTIONID' => 'PAYMENTREQUEST_%d_TRANSACTIONID',
            'ALLOWEDPAYMENTMETHOD' => 'PAYMENTREQUEST_%d_ALLOWEDPAYMENTMETHOD',
            'PAYMENTACTION' => 'PAYMENTREQUEST_%d_PAYMENTACTION',
            'PAYMENTREQUESTID' => 'PAYMENTREQUEST_%d_PAYMENTREQUESTID',
            'SHIPTONAME' => 'PAYMENTREQUEST_%d_SHIPTONAME',
            'SHIPTOSTREET' => 'PAYMENTREQUEST_%d_SHIPTOSTREET',
            'SHIPTOSTREET2' => 'PAYMENTREQUEST_%d_SHIPTOSTREET2',
            'SHIPTOCITY' => 'PAYMENTREQUEST_%d_SHIPTOCITY',
            'SHIPTOSTATE' => 'PAYMENTREQUEST_%d_SHIPTOSTATE',
            'SHIPTOZIP' => 'PAYMENTREQUEST_%d_SHIPTOZIP',
            'SHIPTOCOUNTRYCODE' => 'PAYMENTREQUEST_%d_SHIPTOCOUNTRYCODE',
            'SHIPTOCOUNTRY' => 'PAYMENTREQUEST_%d_SHIPTOCOUNTRYCODE',
            'SHIPTOPHONENUM' => 'PAYMENTREQUEST_%d_SHIPTOPHONENUM',
            'SELLERID' => 'PAYMENTREQUEST_%d_SELLERID',
            'SELLERUSERNAME' => 'PAYMENTREQUEST_%d_SELLERUSERNAME',
            'SELLERREGISTRATIONDATE' => 'PAYMENTREQUEST_%d_SELLERREGISTRATIONDATE',
            'ITEMS' => array(
              'NAME' => 'L_PAYMENTREQUEST_%d_NAME%d',
              'DESC' => 'L_PAYMENTREQUEST_%d_DESC%d',
              'AMT' => 'L_PAYMENTREQUEST_%d_AMT%d',
              'NUMBER' => 'L_PAYMENTREQUEST_%d_NUMBER%d',
              'QTY' => 'L_PAYMENTREQUEST_%d_QTY%d',
              'TAXAMT' => 'L_PAYMENTREQUEST_%d_TAXAMT%d',
              'ITEMWEIGHTVALUE' => 'L_PAYMENTREQUEST_%d_ITEMWEIGHTVALUE%d',
              'ITEMWEIGHTUNIT' => 'L_PAYMENTREQUEST_%d_ITEMWEIGHTUNIT%d',
              'ITEMHEIGHTVALUE' => 'L_PAYMENTREQUEST_%d_ITEMHEIGHTVALUE%d',
              'ITEMHEIGHTUNIT' => 'L_PAYMENTREQUEST_%d_ITEMHEIGHTUNIT%d',
              'ITEMWIDTHVALUE' => 'L_PAYMENTREQUEST_%d_ITEMWIDTHVALUE%d',
              'ITEMWIDTHUNIT' => 'L_PAYMENTREQUEST_%d_ITEMWIDTHUNIT%d',
              'ITEMLENGTHVALUE' => 'L_PAYMENTREQUEST_%d_ITEMLENGTHVALUE%d',
              'ITEMLENGTHUNIT' => 'L_PAYMENTREQUEST_%d_ITEMLENGTHUNIT%d',
              'ITEMURL' => 'L_PAYMENTREQUEST_%d_ITEMURL%d',
              'EBAYITEMNUMBER' => 'L_PAYMENTREQUEST_%d_EBAYITEMNUMBER%d',
              'EBAYITEMAUCTIONTXNID' => 'L_PAYMENTREQUESST_%d_EBAYITEMAUCTIONTXNID%d',
              'EBAYITEMORDERID' => 'L_PAYMENTREQUEST_%d_EBAYITEMORDERID%d',
              'EBAYCARTID' => 'L_PAYMENTREQUEST_%d_EBAYCARTID%d',
            ),
          ),

          // Alternative methods
          'BILLINGAGREEMENTDESCRIPTION' => 'L_BILLINGAGREEMENTDESCRIPTION0',
          'BILLINGAGREEMENTDESCRIPTIONS' => 'L_BILLINGAGREEMENTDESCRIPTION%d',
          'BILLINGTYPE' => 'L_BILLINGTYPE0',
          'BILLINGTYPES' => 'L_BILLINGTYPE%d',
        ));
      }

      // Check params
      $params = $this->_checkParams($params, array(
        'RETURNURL', 'CANCELURL',
      ), array(
        'TOKEN', 'MAXAMT', 'CALLBACK', 'CALLBACKTIMEOUT', 'REQCONFIRMSHIPPING',
        'NOSHIPPING', 'ALLOWNOTE', 'ADDROVERRIDE', 'LOCALECODE', 'PAGESTYLE',
        'HDRIMG', 'HDRBORDERCOLOR', 'HDRBACKCOLOR', 'PAYFLOWCOLOR', 'EMAIL',
        'SOLUTIONTYPE', 'LANDINGPAGE', 'CHANNELTYPE', 'GIROPAYSUCCESSURL',
        'GIROPAYCANCELURL', 'BANKTXNPENDINGURL', 'BRANDNAME',
        'CUSTOMERSERVICENUMBER', 'GIFTMESSAGEENABLE', 'GIFTRECEIPTENABLE',
        'GIFTWRAPENABLE', 'GIFTWRAPNAME', 'GIFTWRAPAMOUNT',
        'BUYEREMAILOPTINENABLE', 'SURVEYQUESTION', 'CALLBACKVERSION',
        'SURVEYENABLE', 'L_SURVEYCHOICE%d',
        
        'BUYERID', 'BUYERUSERNAME', 'BUYERREGISTRATIONDATE', 'ALLOWPUSHFUNDING',
        'L_SHIPPINGOPTIONISDEFAULT%d', 'L_SHIPPINGOPTIONNAME%d',
        'L_SHIPPINGOPTIONAMOUNT%d', 'L_BILLINGAGREEMENTDESCRIPTION%d',
        'L_PAYMENTTYPE%d', 'L_BILLINGAGREEMENTCUSTOM%d',

        // New
        'PAYMENTREQUEST_%d_AMT', 'PAYMENTREQUEST_%d_CURRENCYCODE',
        'PAYMENTREQUEST_%d_ITEMAMT', 'PAYMENTREQUEST_%d_SHIPPINGAMT',
        'PAYMENTREQUEST_%d_INSURANCEAMT', 'PAYMENTREQUEST_%d_SHIPDISCAMT',
        'PAYMENTREQUEST_%d_INSURANCEOPTIONOFFERED',
        'PAYMENTREQUEST_%d_HANDLINGAMT', 'PAYMENTREQUEST_%d_TAXAMT',
        'PAYMENTREQUEST_%d_DESC', 'PAYMENTREQUEST_%d_CUSTOM',
        'PAYMENTREQUEST_%d_INVNUM', 'PAYMENTREQUEST_%d_NOTIFYURL',
        'PAYMENTREQUEST_%d_NOTETEXT', 'PAYMENTREQUEST_%d_SOFTDESCRIPTOR',
        'PAYMENTREQUEST_%d_TRANSACTIONID',
        'PAYMENTREQUEST_%d_ALLOWEDPAYMENTMETHOD',
        'PAYMENTREQUEST_%d_PAYMENTACTION', 'PAYMENTREQUEST_%d_PAYMENTREQUESTID',
        'PAYMENTREQUEST_%d_SHIPTONAME', 'PAYMENTREQUEST_%d_SHIPTOSTREET',
        'PAYMENTREQUEST_%d_SHIPTOSTREET2', 'PAYMENTREQUEST_%d_SHIPTOCITY',
        'PAYMENTREQUEST_%d_SHIPTOSTATE', 'PAYMENTREQUEST_%d_SHIPTOZIP',
        'PAYMENTREQUEST_%d_SHIPTOCOUNTRYCODE',
        'PAYMENTREQUEST_%d_SHIPTOPHONENUM',
        'L_PAYMENTREQUEST_%d_NAME%d', 'L_PAYMENTREQUEST_%d_DESC%d',
        'L_PAYMENTREQUEST_%d_AMT%d', 'L_PAYMENTREQUEST_%d_NUMBER%d',
        'L_PAYMENTREQUEST_%d_QTY%d', 'L_PAYMENTREQUEST_%d_TAXAMT%d',
        'L_PAYMENTREQUEST_%d_ITEMWEIGHTVALUE%d',
        'L_PAYMENTREQUEST_%d_ITEMWEIGHTUNIT%d',
        'L_PAYMENTREQUEST_%d_ITEMLENGTHVALUE%d',
        'L_PAYMENTREQUEST_%d_ITEMLENGTHUNIT%d',
        'L_PAYMENTREQUEST_%d_ITEMWIDTHVALUE%d',
        'L_PAYMENTREQUEST_%d_ITEMWIDTHUNIT%d',
        'L_PAYMENTREQUEST_%d_ITEMHEIGHTVALUE%d',
        'L_PAYMENTREQUEST_%d_ITEMHEIGHTUNIT%d',
        'L_PAYMENTREQUEST_%d_ITEMURL%d', 'L_PAYMENTREQUEST_%d_EBAYITEMNUMBER%d',
        'L_PAYMENTREQUESST_%d_EBAYITEMAUCTIONTXNID%d',
        'L_PAYMENTREQUEST_%d_EBAYITEMORDERID%d',
        'L_PAYMENTREQUEST_%d_EBAYCARTID%d', 'PAYMENTREQUEST_%d_SELLERID',
        'PAYMENTREQUEST_%d_SELLERUSERNAME',
        'PAYMENTREQUEST_%d_SELLERREGISTRATIONDATE',
      ));
    } else {
      // Preprocess params
      if( $this->_preProcessRequest ) {
        $params = $this->_preProcessRequestData($params, array(
          // Standard method
          'ITEMS' => array(
            'NAME' => 'L_NAME%d',
            'DESC' => 'L_DESC%d',
            'AMT' => 'L_AMT%d',
            'NUMBER' => 'L_NUMBER%d',
            'QTY' => 'L_QTY%d',
            'TAXAMT' => 'L_TAXAMT%d',
            'ITEMWEIGHTVALUE' => 'L_ITEMWEIGHTVALUE%d',
            'ITEMWEIGHTUNIT' => 'L_ITEMWEIGHTUNIT%d',
            'ITEMHEIGHTVALUE' => 'L_ITEMHEIGHTVALUE%d',
            'ITEMHEIGHTUNIT' => 'L_ITEMHEIGHTUNIT%d',
            'ITEMWIDTHVALUE' => 'L_ITEMWIDTHVALUE%d',
            'ITEMWIDTHUNIT' => 'L_ITEMWIDTHUNIT%d',
            'ITEMLENGTHVALUE' => 'L_ITEMLENGTHVALUE%d',
            'ITEMLENGTHUNIT' => 'L_ITEMLENGTHUNIT%d',
            'ITEMURL' => 'L_ITEMURL%d',
          ),

          // Alternate method
          'L_NAME' => 'L_NAME%d',
          'L_DESC' => 'L_DESC%d',
          'L_AMT' => 'L_AMT%d',
          'L_NUMBER' => 'L_NUMBER%d',
          'L_QTY' => 'L_QTY%d',
          'L_TAXAMT' => 'L_TAXAMT%d',
          'L_ITEMWEIGHTVALUE' => 'L_ITEMWEIGHTVALUE%d',
          'L_ITEMWEIGHTUNIT' => 'L_ITEMWEIGHTUNIT%d',
          'L_ITEMHEIGHTVALUE' => 'L_ITEMHEIGHTVALUE%d',
          'L_ITEMHEIGHTUNIT' => 'L_ITEMHEIGHTUNIT%d',
          'L_ITEMWIDTHVALUE' => 'L_ITEMWIDTHVALUE%d',
          'L_ITEMWIDTHUNIT' => 'L_ITEMWIDTHUNIT%d',
          'L_ITEMLENGTHVALUE' => 'L_ITEMLENGTHVALUE%d',
          'L_ITEMLENGTHUNIT' => 'L_ITEMLENGTHUNIT%d',
          'L_ITEMURL' => 'L_ITEMURL%d',
        ));
      }

      // Check params
      $params = $this->_checkParams($params, array(
        'RETURNURL', 'CANCELURL',
      ), array(
        'TOKEN', 'MAXAMT', 'CALLBACK', 'CALLBACKTIMEOUT', 'REQCONFIRMSHIPPING',
        'NOSHIPPING', 'ALLOWNOTE', 'ADDROVERRIDE', 'LOCALECODE', 'PAGESTYLE',
        'HDRIMG', 'HDRBORDERCOLOR', 'HDRBACKCOLOR', 'PAYFLOWCOLOR', 'EMAIL',
        'SOLUTIONTYPE', 'LANDINGPAGE', 'CHANNELTYPE', 'GIROPAYSUCCESSURL',
        'GIROPAYCANCELURL', 'BANKTXNPENDINGURL', 'BRANDNAME',
        'CUSTOMERSERVICENUMBER', 'GIFTMESSAGEENABLE', 'GIFTRECEIPTENABLE',
        'GIFTWRAPENABLE', 'GIFTWRAPNAME', 'GIFTWRAPAMOUNT',
        'BUYEREMAILOPTINENABLE', 'SURVEYQUESTION', 'CALLBACKVERSION',
        'SURVEYENABLE', 'L_SURVEYCHOICE%d',

        'BUYERID', 'BUYERUSERNAME', 'BUYERREGISTRATIONDATE', 'ALLOWPUSHFUNDING',
        'L_SHIPPINGOPTIONISDEFAULT%d', 'L_SHIPPINGOPTIONNAME%d',
        'L_SHIPPINGOPTIONAMOUNT%d', 'L_BILLINGAGREEMENTDESCRIPTION%d',
        'L_PAYMENTTYPE%d', 'L_BILLINGAGREEMENTCUSTOM%d',

        // Deprecated
        'PAYMENTACTION', 'AMT', 'CURRENCYCODE', 'ITEMAMT', 'SHIPPINGAMT',
        'INSURANCEAMT', 'SHIPPINGDISCAMT', 'INSURANCEOPTIONOFFERED',
        'HANDLINGAMT', 'TAXAMT', 'DESC', 'CUSTOM', 'INVNUM', 'NOTIFYURL',
        'NOTETEXT', 'SOFTDESCRIPTOR', 'TRANSACTIONID', 'ALLOWEDPAYMENTMETHOD',
        'PAYMENTREQUESTID', 'SHIPTONAME', 'SHIPTOSTREET', 'SHIPTOSTREET2',
        'SHIPTOCITY', 'SHIPTOSTATE', 'SHIPTOZIP', 'SHIPTOCOUNTRY',
        'SHIPTOPHONENUM', 'L_NAME%d', 'L_DESC%d', 'L_AMT%d', 'L_NUMBER%d',
        'L_QTY%d', 'L_TAXAMT%d', 'L_ITEMWEIGHTVALUE%d', 'L_ITEMWEIGHTUNIT%d',
        'L_ITEMLENGTHVALUE%d', 'L_ITEMLENGHTUNIT%d', 'L_ITEMWIDTHVALUE%d',
        'L_ITEMWIDTHUNIT%d', 'L_ITEMHEIGHTVALUE%d', 'L_ITEMHEIGHTUNIT%d',
        'L_ITEMURL%d', 'L_EBAYITEMNUMBER%d', 'L_EBAYITEMAUCTIONTXNID%d',
        'L_EBAYITEMORDERID%d', 'L_EBAYITEMCARTID%d', 'SELLERID',
        'SELLERUSERNAME', 'SELLERREGISTRATIONDATE',
      ));
    }
    
    // Send request
    $client = $this->_prepareHttpClient('SetExpressCheckout');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData['TOKEN'];
  }



  // Recurring Payments

  /**
   * Bills the buyer for the outstanding balance associated with a recurring
   * payments profile.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_BillOutstandingAmount
   * @param string $profileId
   * @param string $amount
   * @param string $note
   * @return array
   */
  public function billOutstandingAmount($profileId, $amount = null, $note = null)
  {
    if( is_array($profileId) ) {
      $params = $profileId;
    } else {
      $params = array();
      $params['PROFILEID'] = $profileId;
      if( null !== $amount ) {
        $params['AMT'] = $amount;
      }
      if( null !== $note ) {
        $params['NOTE'] = $note;
      }
    }

    // Check params
    $params = $this->_checkParams($params, 'PROFILEID', array(
      'AMT', 'NOTE',
    ));

    // Send request
    $client = $this->_prepareHttpClient('BillOutstandingAmount');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Cancel a recurring payments profile.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_ManageRecurringPaymentsProfileStatus
   * @param string $profileId
   * @param string $note
   * @return array
   */
  public function cancelRecurringPaymentsProfile($profileId, $note = null)
  {
    return $this->manageRecurringPaymentsProfile($profileId, 'Cancel', $note);
  }

  /**
   * Create a recurring payments profile.
   * You must invoke the CreateRecurringPaymentsProfile API operation for each
   * profile you want to create. The API operation creates a profile and an
   * associated billing agreement.
   *
   * Note: There is a one-to-one correspondence between billing agreements and
   * recurring payments profiles. To associate a a recurring payments profile
   * with its billing agreement, the description in the recurring payments
   * profile must match the description of a billing agreement.
   * For version 54.0 and later, use SetExpressCheckout to initiate
   * creation of a billing agreement.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_CreateRecurringPayments
   * @param array $params
   * @return array
   */
  public function createRecurringPaymentsProfile(array $params)
  {
    // Check params
    $this->_checkParams($params, array(
      'TOKEN', 'PROFILESTARTDATE', 'DESC', 'BILLINGPERIOD', 'BILLINGFREQUENCY',
      'AMT',
    ), array(
      'SUBSCRIBERNAME', 'PROFILEREFERENCE', 'MAXFAILEDPAYMENTS', 'AUTOBILLAMT',
      'TOTALBILLINGCYCLES', 'SHIPPINGAMT', 'TAXAMT', 'INITAMT',
      'FAILEDINITAMTACTION', 'SHIPTONAME', 'SHIPTOSTREET', 'SHIPTOSTREET2', 
      'SHIPTOCITY', 'SHIPTOSTATE', 'SHIPTOZIP', 'SHIPTOCOUNTRY',
      'SHIPTOPHONENUM', 'EXPDATE', 'CVV2', 'STARTDATE', 'ISSUENUMBER',
      'PAYERID', 'PAYERSTATUS', 'COUNTRYCODE', 'BUSINESS', 'SALUTATION',
      'FIRSTNAME', 'MIDDLENAME', 'LASTNAME', 'SUFFIX', 'STREET2',
      'SHIPTOPHONENUM', 
      
      // Might be required
      'TRIALBILLINGPERIOD', 'TRIALBILLINGFREQUENCY',
      'TRIALTOTALBILLINGCYCLES', 'TRIALAMT', 
      
      // Might also be required
      'CURRENCYCODE',
      'CREDITCARDTYPE', 'ACCT', 'EMAIL', 'STREET',
      'CITY', 'STATE', 'COUNTRYCODE', 'ZIP',
    ));

    // Send request
    $client = $this->_prepareHttpClient('CreateRecurringPaymentsProfile');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Obtain information about a recurring payments profile.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_GetRecurringPaymentsProfileDetails
   * @param string $profileId
   * @return array
   */
  public function detailRecurringPaymentsProfile($profileId)
  {
    if( is_array($profileId) ) {
      $params = $profileId;
    } else {
      $params = array();
      $params['PROFILEID'] = $profileId;
    }

    // Check params
    $params = $this->_checkParams($params, 'PROFILEID');

    // Send request
    $client = $this->_prepareHttpClient('GetRecurringPaymentsProfileDetails');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Cancel, suspend, or reactivate a recurring payments profile.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_ManageRecurringPaymentsProfileStatus
   * @param string $profileId
   * @param string $action
   * @param string $note
   * @return array
   */
  public function manageRecurringPaymentsProfile($profileId, $action = null,
      $note = null)
  {
    // Build params
    if( is_array($profileId) ) {
      $params = $profileId;
    } else {
      $params = array();
      $params['PROFILEID'] = $profileId;
      if( null !== $action ) {
        $params['ACTION'] = $action;
      }
      if( null !== $note ) {
        $params['NOTE'] = $note;
      }
    }

    // Check params
    $params = $this->_checkParams($params, 'PROFILEID', array(
      'ACTION', 'NOTE',
    ));

    // Send request
    $client = $this->_prepareHttpClient('ManageRecurringPaymentsProfileStatus');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Reactivate a recurring payments profile.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_ManageRecurringPaymentsProfileStatus
   * @param string $profileId
   * @param string $note
   * @return array
   */
  public function reactivateRecurringPaymentsProfile($profileId, $note = null)
  {
    return $this->manageRecurringPaymentsProfile($profileId, 'Reactivate', $note);
  }

  /**
   * Suspend a recurring payments profile.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_ManageRecurringPaymentsProfileStatus
   * @param string $profileId
   * @param string $note
   * @return array
   */
  public function suspendRecurringPaymentsProfile($profileId, $note = null)
  {
    return $this->manageRecurringPaymentsProfile($profileId, 'Suspend', $note);
  }

  /**
   * Update a recurring payments profile.
   *
   * @link http://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_UpdateRecurringPaymentsProfile
   * @param string $profileId
   * @param array $params
   * @return array
   */
  public function updateRecurringPaymentsProfile($profileId, array $params = null)
  {
    // Build params
    if( is_array($profileId) ) {
      $params = $profileId;
    } else {
      if( !is_array($params) ) {
        $params = array();
      }
      $params['PROFILEID'] = $profileId;
    }

    // Check params
    $this->_checkParams($params, array(
      'PROFILEID', 'CURRENCYCODE', 
      'CREDITCARDTYPE', 'ACCT',
      'FIRSTNAME', 'MIDDLENAME', 'LASTNAME', 
      'STREET', 'CITY', 'STATE', 'COUNTRYCODE', 'ZIP',
    ), array(
      'NOTE', 'DESC', 'SUBSCRIBERNAME', 'PROFILEREFERENCE',
      'ADDITIONALBILLINGCYCLES', 'AMT', 'SHIPPINGAMT', 'TAXAMT',
      'OUTSTANDINGAMT', 'AUTOBILLAMT', 'MAXFAILEDPAYMENTS', 'PROFILESTARTDATE',
      'SHIPTONAME', 'SHIPTOSTREET', 'SHIPTOSTREET2', 'SHIPTOCITY',
      'SHIPTOSTATE', 'SHIPTOZIP', 'SHIPTOCOUNTRY', 'SHIPTOPHONENUM',
      'TOTALBILLINGCYCLES', 'TRIALTOTALBILLINGCYCLES', 'TRIALAMT',
      'EXPDATE', 'CVV2', 'STARTDATE', 'ISSUENUMBER', 'EMAIL', 
      'STREET2',
    ));

    // Send request
    $client = $this->_prepareHttpClient('UpdateRecurringPaymentsProfile');
    $client
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }



  // Utility

  /**
   * Get the http client and set default parameters
   *
   * @return Zend_Http_Client
   */
  protected function _prepareHttpClient($method = null)
  {
    // Get uri
    if( $this->_signature ) {
      if( $this->_testMode ) {
        $uri = 'https://api-3t.sandbox.paypal.com/nvp';
      } else {
        $uri = 'https://api-3t.paypal.com/nvp';
      }
    } else {
      if( $this->_testMode ) {
        $uri = 'https://api.sandbox.paypal.com/nvp';
      } else {
        $uri = 'https://api.paypal.com/nvp';
      }
    }
    
    $client = $this->getHttpClient();
    
    $client
      ->resetParameters()
      ->setUri($uri)
      ->setMethod(Zend_Http_Client::POST)
      ;

    // Set method
    if( null !== $method ) {
      $client->setParameterPost('METHOD', $method);
    }
    // Set version
    if( null !== $this->_version ) {
      $client->setParameterPost('VERSION', urlencode($this->_version));
    }
    // Set credentials
    if( null !== $this->_username ) {
      $client->setParameterPost('USER', urlencode($this->_username));
    }
    if( null !== $this->_password ) {
      $client->setParameterPost('PWD', urlencode($this->_password));
    }
    if( null !== $this->_signature ) {
      $client->setParameterPost('SIGNATURE', urlencode($this->_signature));
    }
    //if( null !== $this->_certificate ) {
    //
    //}
    
    return $client;
  }

  /**
   * Process the response
   *
   * @param Zend_Http_Response $response
   * @return array
   * @throws Zend_Service_Exception
   */
  protected function _processHttpResponse(Zend_Http_Response $response)
  {
    // Hack for logging
    if( $this->_log instanceof Zend_Log ) {
      $client = $this->getHttpClient();
      $this->_log->log(sprintf("Request:\n%s\nResponse:\n%s\n",
          $client->getLastRequest(), $client->getLastResponse()->asString()), Zend_Log::DEBUG);
    }
    
    // Check HTTP Status code
    if( 200 !== $response->getStatus() ) {
      throw new Engine_Service_PayPal_Exception(sprintf('HTTP Client ' .
          'returned error status: %1$d', $response->getStatus()), 'HTTP');
    }

    // Check response body
    $responseStr = $response->getBody();
    if( !is_string($responseStr) || '' === $responseStr ) {
      throw new Engine_Service_PayPal_Exception('HTTP Client returned an ' .
          'empty response', 'IS_EMPTY');
    }

    // Decode response body
    $responseData = array();
    foreach( explode("&", $responseStr) as $tmp ) {
      $tmp = explode('=', $tmp, 2);
      if( count($tmp) > 1 ) {
        $responseData[urldecode($tmp[0])] = urldecode($tmp[1]);
      }
    }

    // Check for valid response
    if( !is_array($responseData) ||
        empty($responseData) ||
        count($responseData) <= 0 ||
        !array_key_exists('ACK', $responseData) ) {
      throw new Engine_Service_PayPal_Exception('HTTP Client returned ' .
          'invalid NVP response', 'NOT_VALID');
    }

    // Check for response status and message
    if( strtolower($responseData['ACK']) == 'failure' ) {
      switch( strtolower($responseData['L_SEVERITYCODE0']) ) {
        default:
        case 'error':
          $level = Zend_Log::ERR;
          break;
      }
      throw new Engine_Service_PayPal_Exception(sprintf('API Error: ' .
          '[%1$d] %2$s - %3$s', $responseData['L_ERRORCODE0'], $responseData['L_SHORTMESSAGE0'],
          $responseData['L_LONGMESSAGE0']), $responseData['L_ERRORCODE0']);
    }

    return $responseData;
  }
  
  /**
   * Check params
   *
   * @param array $params
   * @param array $requiredParams
   * @param array $supportedParams
   * @return array
   */
  protected function _checkParams(array $params, $requiredParams = null,
      $supportedParams = null)
  {
    // Check params
    if( !is_array($params) ) {
      if( !empty($params) ) {
        throw new Engine_Service_PayPal_Exception('Invalid data type',
            'UNKNOWN_PARAM');
      } else {
        $params = array();
      }
    }

    // Check required params
    if( is_string($requiredParams) ) {
      $requiredParams = array($requiredParams);
    } else if( null === $requiredParams ) {
      $requiredParams = array();
    }

    // Check supported params
    if( is_string($supportedParams) ) {
      $supportedParams = array($supportedParams);
    } else if( null === $supportedParams ) {
      $supportedParams = array();
    }

    // Nothing to do
    if( empty($requiredParams) && empty($supportedParams) &&
        is_array($requiredParams) && is_array($supportedParams) ) {
      return array();
    }

    // Build full supported
    if( is_array($supportedParams) && is_array($requiredParams) ) {
      $supportedParams = array_unique(array_merge($supportedParams, $requiredParams));
    }
    
    // Run strtoupper on all keys?
    $params = array_combine(array_map('strtoupper', array_keys($params)), array_values($params));

    // Init
    $processedParams = array();
    $foundKeys = array();

    // Process out simple params
    $processedParams = array_merge($processedParams,
        array_intersect_key($params, array_flip($supportedParams)));
    $params = array_diff_key($params, array_flip($supportedParams));
    $foundKeys = array_merge($foundKeys, array_keys($processedParams));

    // Process out complex params
    foreach( $supportedParams as $supportedFormat ) {
      foreach( $params as $key => $value ) {
        if( count($parts = sscanf($key, $supportedFormat)) > 0 ) {
          $foundKeys[] = $supportedFormat;
          $processedParams[$key] = $value;
        }
      }
    }

    // Remove complex params
    $params = array_diff_key($params, $processedParams);

    // Anything left is an unsupported param
    if( !empty($params) ) {
      $paramStr = '';
      foreach( $params as $unsupportedParam ) {
        if( $paramStr != '' ) $paramStr .= ', ';
        $paramStr .= $unsupportedParam;
      }
      //trigger_error(sprintf('Unknown param(s): %1$s', $paramStr), E_USER_NOTICE);
      throw new Engine_Service_PayPal_Exception(sprintf('Unknown param(s): ' .
          '%1$s', $paramStr), 'UNKNOWN_PARAM');
    }

    // Let's check required against foundKeys
    if( count($missingRequiredParams = array_diff_key($requiredParams, $foundKeys)) > 0 ) {
      $paramStr = '';
      foreach( $missingRequiredParams as $missingRequiredParam ) {
        if( $paramStr != '' ) $paramStr .= ', ';
        $paramStr .= $missingRequiredParam;
      }
      throw new Engine_Service_PayPal_Exception(sprintf('Missing required ' .
          'param(s): %1$s', $paramStr), 'MISSING_REQUIRED');
    }
    
    return $processedParams;
  }

  /**
   * Pre-process the request data to support arrays
   * 
   * @param array $requestData
   * @param array $structure
   * @param array $stack
   * @return array
   */
  protected function _preProcessRequestData($requestData, $structure,
      $stack = null)
  {
    // Init
    $foundKeys = array();
    $processedData = array();
    if( !is_array($stack) ) {
      $stack = array();
    }

    if( !is_array($requestData) ) {
      // Should we throw here?
      return array();
    }

    foreach( $structure as $structureKey => $structureValue ) {
      // Not found
      if( !isset($requestData[$structureKey]) ) {
        continue;
      }
      $foundKeys[] = $structureKey;
      $currentRequestData =& $requestData[$structureKey];

      // Array
      if( is_array($structureValue) ) {
        foreach( $currentRequestData as $index => $subRequestData ) {
          $tmpStack = $stack;
          array_push($tmpStack, $index);
          $subData = $this->_preProcessRequestData($subRequestData,
              $structureValue, $tmpStack);
          $processedData = array_merge($processedData, $subData);
        }
      }
      // List
      else if( is_array($currentRequestData) ) {
        foreach( $currentRequestData as $index => $subRequestData ) {
          $tmpStack = $stack;
          array_push($tmpStack, $index);
          $subKey = vsprintf($structureValue, $tmpStack);
          $processedData[$subKey] = $subRequestData;
        }
      }
      // Scalar
      else {
        $subKey = vsprintf($structureValue, $stack);
        $processedData[$subKey] = $currentRequestData;
      }
    }

    // Remove processed keys
    $requestData = array_diff_key($requestData, array_flip($foundKeys));

    // Merge in processed data
    $requestData = array_merge($requestData, $processedData);

    return $requestData;
  }

  /**
   * Post-process the response data to support arrays
   * 
   * @param array $responseData
   * @param array $structure
   * @param array $scalarList
   * @return array
   */
  protected function _postProcessResponseData($responseData, $structure,
      $scalarList = null)
  {
    // Init
    $foundKeys = array();
    $processedData = array();

    // Process out scalars
    if( is_array($scalarList) ) {
      $processedData = array_merge($processedData,
          array_intersect_key($responseData, array_flip($scalarList)));
      $responseData = array_diff_key($responseData, array_flip($scalarList));
    }

    // Process response lists
    $processedData = array();
    foreach( $structure as $format => $path ) {
      foreach( $responseData as $key => $value ) {
        if( count(array_filter($parts = sscanf($key, $format), 'is_numeric')) &&
            vsprintf($format, $parts) == $key ) {
          // Build structure
          $ref =& $processedData;
          /**
          $pos = 0;
          foreach( $path as $index => $pathSegment ) {
            if( null === $pathSegment ) {
              if( !isset($ref[$parts[$pos]]) ) {
                $ref[$parts[$pos]] = array();
              }
              $ref =& $ref[$parts[$pos]];
              $pos++;
            } else {
              if( !isset($ref[$pathSegment]) ) {
                $ref[$pathSegment] = array();
              }
              $ref =& $ref[$pathSegment];
            }
          }
           */
          for( $i = 0, $l = max(count($parts), count($path)); $i < $l; $i++ ) {
            if( isset($path[$i]) ) {
              if( !isset($ref[$path[$i]]) ) {
                $ref[$path[$i]] = array();
              }
              $ref =& $ref[$path[$i]];
              //$last = 0;
            }
            if( isset($parts[$i]) ) {
              if( !isset($ref[$parts[$i]]) ) {
                $ref[$parts[$i]] = array();
              }
              $ref =& $ref[$parts[$i]];
              //$last = 1;
            }
          }
          // Assign value
          $ref = $value;
          // Set found
          $foundKeys[] = $key;
        }
      }
    }

    // Remove processed keys
    $responseData = array_diff_key($responseData, array_flip($foundKeys));

    // Merge in processed data
    $responseData = array_merge($responseData, $processedData);

    return $responseData;
  }
}
