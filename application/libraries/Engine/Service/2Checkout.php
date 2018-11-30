<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Service_2Checkout
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: 2Checkout.php 10234 2014-05-22 16:25:59Z andres $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Service_2Checkout
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Service_2Checkout extends Zend_Service_Abstract
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
   * The return format
   * 
   * @var string
   */
  protected $_format;

  /**
   * The accept header
   * 
   * @var string
   */
  protected $_accept;

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
    if( !in_array($this->_format, array('json', 'xml', 'html')) ) {
      $this->_format = 'json';
    }
    switch( $this->_format ) {
      default:
      case 'json':
        $this->_accept = 'application/json';
        break;
      case 'xml':
        $this->_accept = 'application/xml';
        break;
      case 'html':
        $this->_accept = 'text/html';
        break;
    }
    if( empty($this->_username) || empty($this->_password) ) {
      throw new Engine_Service_2Checkout_Exception('Not all connection ' .
          'options were specified.', 'MISSING_LOGIN');
    }
  }



  // Vendor Information

  /**
   * Used to retrieve your account’s company information details from the
   * Site Management page.
   *
   * @link http://www.2checkout.com/documentation/api/acct-detail_company_info/
   * @return array
   */
  public function detailCompanyInfo()
  {
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/acct/detail_company_info')
      ->setMethod(Zend_Http_Client::GET)
      ;

    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    if( is_array($responseData) ) {
      return $responseData['vendor_company_info'];
    } else {
      return $responseData;
    }
  }

  /**
   * Used to retrieve your account’s contact information details from the
   * Contact Info page.
   *
   * @link http://www.2checkout.com/documentation/api/acct-detail_contact_info/
   * @return array 
   */
  public function detailContactInfo()
  {
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/acct/detail_contact_info')
      ->setMethod(Zend_Http_Client::GET)
      ;

    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);
    
    if( is_array($responseData) ) {
      return $responseData['vendor_contact_info'];
    } else {
      return $responseData;
    }
  }

  /**
   * Used to get a detailed estimate of the current pending payment.
   *
   * @link http://www.2checkout.com/documentation/api/acct-detail_pending_payment/
   * @return array
   */
  public function detailPendingPayment()
  {
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/acct/detail_pending_payment')
      ->setMethod(Zend_Http_Client::GET)
      ;

    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    if( is_array($responseData) ) {
      return $responseData['payment'];
    } else {
      return $responseData;
    }
  }

  /**
   * Used to get a list of past vendor payments
   * 
   * @link http://www.2checkout.com/documentation/api/acct-list_payments/
   * @return array 
   */
  public function listPayments()
  {
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/acct/list_payments')
      ->setMethod(Zend_Http_Client::GET)
      ;

    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    if( is_array($responseData) ) {
      return $responseData['payments'];
    } else {
      return $responseData;
    }
  }



  // Sales Information

  /**
   * Used to retrieve information about a specific sale.
   *
   * @link http://www.2checkout.com/documentation/api/sales-detail_sale/
   * @param mixed $saleId
   * @return array
   */
  public function detailSale($saleId)
  {
    // Build params
    if( is_array($saleId) ) {
      $params = $saleId;
    } else {
      $params = array();
      $params['sale_id'] = $saleId;
    }

    // Check params
    $params = $this->_checkParams($params, 'sale_id');
    
    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/sales/detail_sale')
      ->setMethod(Zend_Http_Client::GET)
      ->setParameterGet($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    if( is_array($responseData) ) {
      return $responseData['sale'];
    } else {
      return $responseData;
    }
  }

  /**
   * Used to retrieve information about a specific invoice.
   *
   * @link http://www.2checkout.com/documentation/api/sales-detail_sale/
   * @param mixed $invoiceId
   * @return array
   */
  public function detailInvoice($invoiceId)
  {
    // Build params
    if( is_array($invoiceId) ) {
      $params = $invoiceId;
    } else {
      $params = array();
      $params['invoice_id'] = $invoiceId;
    }

    // Check params
    $params = $this->_checkParams($params, 'invoice_id');

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/sales/detail_sale')
      ->setMethod(Zend_Http_Client::GET)
      ->setParameterGet($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    if( is_array($responseData) ) {
      return $responseData['sale'];
    } else {
      return $responseData;
    }
  }

  /**
   * Used to retrieve a summary of all sales or only those matching a variety
   * of sale attributes.
   *
   * @link http://www.2checkout.com/documentation/api/sales-list_sales/
   * @param array $params
   * @return array
   */
  public function listSales(array $params = array())
  {
    // Check params
    $params = $this->_checkParams($params, null, array(
      'sale_id', 'invoice_id', 'customer_name', 'customer_email',
      'customer_phone', 'vendor_product_id', 'ccard_first6', 'ccard_last2',
      'sale_date_begin', 'sale_date_end', 'declined_recurrings',
      'active_recurrings', 'refunded', 'cur_page', 'pagesize', 'sort_col',
      'sort_dir',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/sales/list_sales')
      ->setMethod(Zend_Http_Client::GET)
      ->setParameterGet($params)
      ;

    // Process response
    $response = $client->request();
    try {
      $responseData = $this->_processHttpResponse($response);
    } catch( Engine_Service_2Checkout_Exception $e ) {
      if( $e->getCode() === Engine_Service_2Checkout_Exception::RECORD_NOT_FOUND ) {
        return array();
      } else {
        throw $e;
      }
    }

    if( is_array($responseData) ) {
      return $responseData['sale_summary'];
    } else {
      return $responseData;
    }
  }



  // Update sales

  /**
   * Used to attempt to issue a full or partial refund on a sale. This
   * call will send the REFUND_ISSUED INS message.
   *
   * @link http://www.2checkout.com/documentation/api/sales-refund_invoice/
   * @param mixed   $saleId   Order sale ID to issue a refund on.
   * @param integer $category ID representing the reason the refund was issued.
   *                          Required. (values: 1-17 from the following list
   *                          can be used except for 7 as it is for internal use
   *                          only) 1 = Did not receive order 2 = Did not like
   *                          item 3 = Item(s) not as described 4 = Fraud
   *                          5 = Other 6 = Item not available 7 = Do Not Use
   *                          (Internal use only) 8 = No response from supplier
   *                          9 = Recurring last installment 10 = Cancellation
   *                          11 = Billed in error 12 = Prohibited product
   *                          13 = Service refunded at suppliers request
   *                          14 = Non delivery 15 = Not as described
   *                          16 = Out of stock 17 = Duplicate
   * @param string $comment   Message explaining why the refund was issued.
   *                          Required. May not contain ’<’ or ’>’.
   *                          (5000 character max)
   * @param string $amount    The amount to refund. Only needed when issuing a
   *                          partial refund. If an amount is not specified,
   *                          the remaining amount for the invoice is assumed.
   * @param string $currency  Currency type of refund amount. Can be ‘usd’,
   *                          ‘vendor’ or ‘customer’. Only required if amount is
   *                          used.
   * @return Engine_Service_2Checkout
   */
  public function refundSale($saleId, $category = null, $comment = null,
      $amount = null, $currency = null)
  {
    // Build params
    if( is_array($saleId) ) {
      $params = $saleId;
    } else {
      $params = array();
      $params['sale_id'] = $saleId;
      if( null !== $category ) {
        $params['category'] = $category;
      }
      if( null !== $comment ) {
        $params['comment'] = $comment;
      }
      if( null !== $amount ) {
        $params['amount'] = $amount;
      }
      if( null !== $currency ) {
        $params['currency'] = $currency;
      }
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'sale_id', 'comment', 'category',
    ), array(
      'amount', 'currency',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/sales/refund_invoice')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Used to attempt to issue a full or partial refund on an invoice. This
   * call will send the REFUND_ISSUED INS message.
   *
   * @link http://www.2checkout.com/documentation/api/sales-refund_invoice/
   * @param mixed $invoiceId  Invoice ID to issue a refund on.
   * @param integer $category ID representing the reason the refund was issued.
   *                          Required. (values: 1-17 from the following list
   *                          can be used except for 7 as it is for internal use
   *                          only) 1 = Did not receive order 2 = Did not like
   *                          item 3 = Item(s) not as described 4 = Fraud
   *                          5 = Other 6 = Item not available 7 = Do Not Use
   *                          (Internal use only) 8 = No response from supplier
   *                          9 = Recurring last installment 10 = Cancellation
   *                          11 = Billed in error 12 = Prohibited product
   *                          13 = Service refunded at suppliers request
   *                          14 = Non delivery 15 = Not as described
   *                          16 = Out of stock 17 = Duplicate
   * @param string $comment   Message explaining why the refund was issued.
   *                          Required. May not contain ’<’ or ’>’.
   *                          (5000 character max)
   * @param string $amount    The amount to refund. Only needed when issuing a
   *                          partial refund. If an amount is not specified,
   *                          the remaining amount for the invoice is assumed.
   * @param string $currency  Currency type of refund amount. Can be ‘usd’,
   *                          ‘vendor’ or ‘customer’. Only required if amount is
   *                          used.
   * @return Engine_Service_2Checkout
   */
  public function refundInvoice($invoiceId, $category = null, $comment = null,
      $amount = null, $currency = null)
  {
    // Build params
    if( is_array($invoiceId) ) {
      $params = $invoiceId;
    } else {
      $params = array();
      $params['invoice_id'] = $invoiceId;
      if( null !== $category ) {
        $params['category'] = $category;
      }
      if( null !== $comment ) {
        $params['comment'] = $comment;
      }
      if( null !== $amount ) {
        $params['amount'] = $amount;
      }
      if( null !== $currency ) {
        $params['currency'] = $currency;
      }
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'invoice_id', 'comment', 'category',
    ), array(
      'amount', 'currency',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/sales/refund_invoice')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;
      
    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Used to attempt to issue a full or partial refund on an invoice. This
   * call will send the REFUND_ISSUED INS message.
   *
   * @link http://www.2checkout.com/documentation/api/sales-refund_lineitem/
   * @param mixed $lineItemId Line item to issue refund on. Required.
   * @param integer $category ID representing the reason the refund was issued.
   *                          Required. (values: 1-17 from the following list
   *                          can be used except for 7 as it is for internal use
   *                          only) 1 = Did not receive order 2 = Did not like
   *                          item 3 = Item(s) not as described 4 = Fraud
   *                          5 = Other 6 = Item not available 7 = Do Not Use
   *                          (Internal use only) 8 = No response from supplier
   *                          9 = Recurring last installment 10 = Cancellation
   *                          11 = Billed in error 12 = Prohibited product
   *                          13 = Service refunded at suppliers request
   *                          14 = Non delivery 15 = Not as described
   *                          16 = Out of stock 17 = Duplicate
   * @param string $comment   Message explaining why the refund was issued.
   *                          Required. May not contain ’<’ or ’>’.
   *                          (5000 character max)
   * @return Engine_Service_2Checkout 
   */
  public function refundLineItem($lineItemId, $category = null, $comment = null)
  {
    // Build params
    if( is_array($lineItemId) ) {
      $params = $lineItemId;
    } else {
      $params = array();
      $params['lineitem_id'] = $lineItemId;
      if( null !== $category ) {
        $params['category'] = $category;
      }
      if( null !== $comment ) {
        $params['comment'] = $comment;
      }
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'lineitem_id', 'comment', 'category',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/sales/refund_lineitem')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Used to attempt to stop a recurring line item for a specified sale.
   * This call will send the RECURRING_STOPPED INS message.
   *
   * @link http://www.2checkout.com/documentation/api/sales-stop_lineitem_recurring/
   * @param mixed $lineItemId Line item to issue refund on. Required.
   * @return Engine_Service_2Checkout 
   */
  public function stopLineItemRecurring($lineItemId)
  {
    // Build params
    if( is_array($lineItemId) ) {
      $params = $lineItemId;
    } else {
      $params = array();
      $params['lineitem_id'] = $lineItemId;
    }

    // Check params
    $params = $this->_checkParams($params, 'lineitem_id');

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/sales/stop_lineitem_recurring')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Used to attempt to reauthorize sale having expired pre-authorized payment.
   * Please note you can only attempt to reauthorize a sale once per day.
   *
   * @link http://www.2checkout.com/documentation/api/sales-reauth/
   * @param mixed $saleId The order number/sale ID to reauthorize. Required.
   * @return Engine_Service_2Checkout
   */
  public function reauth($saleId)
  {
    // Build params
    if( is_array($saleId) ) {
      $params = $saleId;
    } else {
      $params = array();
      $params['sale_id'] = $saleId;
    }

    // Check params
    $params = $this->_checkParams($params, 'sale_id');

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/sales/reauth')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Used to attempt to mark an order as shipped and will attempt to reauthorize
   * sale if specified in call. This call will send the SHIP_STATUS_CHANGED
   * INS message.
   *
   * @link http://www.2checkout.com/documentation/api/sales-mark_shipped/
   * @param mixed   $invoiceId      ID of the invoice to add tracking
   *                                information to.
   * @param string  $trackingNumber The tracking number issued by the shipper.
   *                                Required.
   * @param boolean $ccCustomer     Specify whether the customer should be
   *                                automatically notified. Defaults to false.
   *                                Optional.
   * @param boolean $reauthorize    Reauthorize payment if payment authorization
   *                                has expired. Defaults to false. Optional.
   * @param string  $comment        Any text except for < and > up to 255
   *                                chars in length. Optional.
   * @return Engine_Service_2Checkout 
   */
  public function markInvoiceShipped($invoiceId, $trackingNumber = null,
      $ccCustomer = null, $reauthorize = null, $comment = null)
  {
    // Build params
    if( is_array($invoiceId) ) {
      $params = $invoiceId;
    } else {
      $params = array();
      $params['invoice_id'] = $invoiceId;
      if( null !== $trackingNumber ) {
        $params['tracking_number'] = $trackingNumber;
      }
      if( null !== $ccCustomer ) {
        $params['cc_customer'] = $ccCustomer;
      }
      if( null !== $reauthorize ) {
        $params['reauthorize'] = $reauthorize;
      }
      if( null !== $comment ) {
        $params['comment'] = $comment;
      }
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'invoice_id', 'tracking_number',
    ), array(
      'cc_customer', 'reauthorize', 'comment',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/sales/mark_shipped')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }


  /**
   * Used to attempt to mark an order as shipped and will attempt to reauthorize
   * sale if specified in call. This call will send the SHIP_STATUS_CHANGED
   * INS message.
   *
   * @link http://www.2checkout.com/documentation/api/sales-mark_shipped/
   * @param mixed   $saleId         The sale ID to mark shipped
   * @param string  $trackingNumber The tracking number issued by the shipper.
   *                                Required.
   * @param boolean $ccCustomer     Specify whether the customer should be
   *                                automatically notified. Defaults to false.
   *                                Optional.
   * @param boolean $reauthorize    Reauthorize payment if payment authorization
   *                                has expired. Defaults to false. Optional.
   * @param string  $comment        Any text except for < and > up to 255
   *                                chars in length. Optional.
   * @return Engine_Service_2Checkout
   */
  public function markSaleShipped($saleId, $trackingNumber = null,
      $ccCustomer = null, $reauthorize = null, $comment = null)
  {
    // Build params
    if( is_array($saleId) ) {
      $params = $saleId;
    } else {
      $params = array();
      $params['sale_id'] = $saleId;
      if( null !== $trackingNumber ) {
        $params['tracking_number'] = $trackingNumber;
      }
      if( null !== $ccCustomer ) {
        $params['cc_customer'] = $ccCustomer;
      }
      if( null !== $reauthorize ) {
        $params['reauthorize'] = $reauthorize;
      }
      if( null !== $comment ) {
        $params['comment'] = $comment;
      }
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'sale_id', 'tracking_number', 
    ), array(
      'cc_customer', 'reauthorize', 'comment',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/sales/mark_shipped')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Used to add a comment to a specified sale.
   *
   * @link http://www.2checkout.com/documentation/api/sales-create_comment/
   * @param mixed   $saleId       The order number/sale ID of a sale to look
   *                              for. Required.
   * @param string  $saleComment  String value of comment to be submitted.
   *                              Required.
   * @param boolean $ccVendor     Set to 1 to have a copy sent to the vendor.
   *                              Optional.
   * @param boolean $ccCustomer   Set to 1 to have the customer sent an email
   *                              copy. Optional.
   * @return Engine_Service_2Checkout
   */
  public function createComment($saleId, $saleComment = null, $ccVendor = null,
      $ccCustomer = null)
  {
    // Build params
    if( is_array($saleId) ) {
      $params = $saleId;
    } else {
      $params = array();
      $params['sale_id'] = $saleId;
      if( null !== $saleComment ) {
        $params['sale_comment'] = $saleComment;
      }
      if( null !== $ccVendor ) {
        $params['cc_vendor'] = $ccVendor;
      }
      if( null !== $ccCustomer ) {
        $params['cc_customer'] = $ccCustomer;
      }
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'sale_id', 'sale_comment',
    ), array(
      'cc_vendor', 'cc_customer',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/sales/create_comment')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }



  // Product Information

  /**
   * Used to retrieve the details for a single product.
   *
   * @link http://www.2checkout.com/documentation/api/products-detail_product/
   * @param mixed $productId ID of product to retrieve details for. Required.
   * @return array
   */
  public function detailProduct($productId)
  {
    // Build params
    if( is_array($productId) ) {
      $params = $productId;
    } else {
      $params = array();
      $params['product_id'] = $productId;
    }

    // Check params
    $params = $this->_checkParams($params, 'product_id');
    
    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/detail_product')
      ->setMethod(Zend_Http_Client::GET)
      ->setParameterGet($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    if( is_array($responseData) ) {
      return $responseData['product'];
    } else {
      return $responseData;
    }
  }

  /**
   * Gets product details by vendor product id
   * 
   * @param string $vendorProductId
   * @return array
   */
  public function detailVendorProduct($vendorProductId)
  {
    if( !is_array($vendorProductId) ) {
      $vendorProductId = array(
        'vendor_product_id' => $vendorProductId,
      );
    }
    
    $productList = $this->listProducts($vendorProductId);

    if( empty($productList['products']) ) {
      return false;
    } else if( count($productList['products']) > 1 ) {
      return false; // Too many?
    }

    $productInfo = array_shift($productList['products']);

    return $productInfo;
  }

  /**
   * Used to retrieve list of all products in account.
   *
   * @link http://www.2checkout.com/documentation/api/products-list_products/
   * @param array $params
   * @return array
   */
  public function listProducts(array $params = array())
  {
    // Check params
    $params = $this->_checkParams($params, null, array(
      // These seem to not be working
      //'2COID', 'product_id', 'product_name',
      
      'vendor_product_id',

      'cur_page', 'pagesize', 'sort_col', 'sort_dir',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/list_products')
      ->setMethod(Zend_Http_Client::GET)
      ->setParameterGet($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
    // return $responseData['products']; // We want the page info
  }



  // Update products

  /**
   * Used to create a new product.
   *
   * @link http://www.2checkout.com/documentation/api/products-create_product/
   * @param array $params
   * @return string ID assigned to the product by 2Checkout.
   */
  public function createProduct(array $params)
  {
    // Check params
    $params = $this->_checkParams($params, array(
      'name', 'price',
    ), array(
      'vendor_product_id', 'description', 'long_description', 'pending_url',
      'approved_url', 'tangible', 'weight', 'handling', 'recurring',
      'startup_fee', 'recurrence', 'duration', 'commission', 'commission_type',
      'commission_amount', 'option_id', 'category_id',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/create_product')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    if( is_array($responseData) ) {
      return $responseData['product_id'];
    } else {
      return $responseData;
    }
    // return $responseData; // We should switch to this but need to make sure it doesn't break anything
  }

  /**
   * Used to update a product.
   *
   * @link http://www.2checkout.com/documentation/api/products-update_product/
   * @param mixed $productId
   * @param array $params
   * @return Engine_Service_2Checkout 
   */
  public function updateProduct($productId, $params = null)
  {
    if( is_array($productId) ) {
      $params = $productId;
    } else {
      if( !is_array($params) ) {
        $params = array();
      }
      $params['product_id'] = $productId;
    }

    // Try to look up product
    $originalProductInfo = null;
    try {
      $originalProductInfo = $this->detailProduct($params['product_id']);
    } catch( Exception $e ) {}
    if( !$originalProductInfo ) {
      // Don't try/catch
      $originalProductInfo = $this->detailVendorProduct($params['product_id']);
      $params['product_id'] = $originalProductInfo['product_id'];
    }

    // Fill in info from original?
    if( empty($params['name']) || empty($params['price']) ) {
      if( empty($params['name']) ) {
        $params['name'] = $originalProductInfo['name'];
      }
      if( empty($params['price']) ) {
        $params['price'] = $originalProductInfo['price'];
      }
    }
    
    // Check params
    $params = $this->_checkParams($params, array(
      'product_id', 'name', 'price',
    ), array(
      'vendor_product_id', 'description', 'long_description', 'pending_url',
      'approved_url', 'tangible', 'weight', 'handling', 'recurring',
      'startup_fee', 'recurrence', 'duration', 'commission', 'commission_type',
      'commission_amount', 'option_id', 'category_id',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/update_product')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Used to delete a product.
   *
   * @link http://www.2checkout.com/documentation/api/products-delete_product/
   * @param mixed $productId 2CO assigned product ID to delete. Required.
   * @return Engine_Service_2Checkout 
   */
  public function deleteProduct($productId)
  {
    if( is_array($productId) ) {
      $params = $productId;
    } else {
      $params = array();
      $params['product_id'] = $productId;
    }

    // Try to look up product
    $originalProductInfo = null;
    try {
      $originalProductInfo = $this->detailProduct($params['product_id']);
    } catch( Exception $e ) {}
    if( !$originalProductInfo ) {
      // Don't try/catch
      $originalProductInfo = $this->detailVendorProduct($params['product_id']);
      $params['product_id'] = $originalProductInfo['product_id'];
    }

    // Check params
    $params = $this->_checkParams($params, 'product_id');
    
    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/delete_product')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }


  
  // Product Option Information

  /**
   * Used to retrieve the details for a single option.
   *
   * @link http://www.2checkout.com/documentation/api/products-detail_option/
   * @param string $optionId
   * @return array
   */
  public function detailOption($optionId)
  {
    if( is_array($optionId) ) {
      $params = $optionId;
    } else {
      $params = array();
      $params['option_id'] = $optionId;
    }

    // Check params
    $params = $this->_checkParams($params, 'option_id');

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/detail_option')
      ->setMethod(Zend_Http_Client::GET)
      ->setParameterGet($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    if( is_array($responseData) ) {
      if( count($responseData['option']) == 1 ) {
        return array_shift($responseData['option']); // They're returning the single option as an array?
      } else {
        return $responseData['option'];
      }
    } else {
      return $responseData;
    }
  }

  /**
   * Used to retrieve list of all options in account.
   *
   * @link http://www.2checkout.com/documentation/api/products-list_options/
   * @param array $params
   * @return array
   */
  public function listOptions($params = array())
  {
    if( is_array($params) ) {
      $params = $params;
    } else {
      $params = array();
    }

    // Check params
    $params = $this->_checkParams($params, null, array(
      'option_name', 'option_value_name', 'cur_page', 'pagesize', 'sort_col',
      'sort_dir', 
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/list_options')
      ->setMethod(Zend_Http_Client::GET)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
    //return $reponseData['options']; // We want the page info
  }


  
  // Update Product Options

  /**
   * Used to create a new product option.
   *
   * @link http://www.2checkout.com/documentation/api/products-create_option/
   * @param string $optionName
   * @param string $optionValueName
   * @param string $optionsValueSurcharge
   * @return array
   */
  public function createOption($optionName, $optionValueName = null,
      $optionsValueSurcharge = null)
  {
    if( is_array($optionName) ) {
      $params = $optionName;
    } else {
      $params = array();
      $params['option_name'] = $optionName;
      if( null !== $optionValueName ) {
        $params['option_value_name'] = $optionValueName;
      }
      if( null !== $optionsValueSurcharge ) {
        $params['option_value_surcharge'] = $optionsValueSurcharge;
      }
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'option_name', 'option_value_name', 'option_value_surcharge',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/create_option')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $responseData;
  }

  /**
   * Used to update an option.
   *
   * @link http://www.2checkout.com/documentation/api/products-update_option/
   * @param string $optionId
   * @param string $optionName
   * @return array
   */
  public function updateOption($optionId, $optionName = null)
  {
    if( is_array($optionName) ) {
      $params = $optionName;
    } else {
      $params = array();
      $params['option_id'] = $optionId;
      if( null !== $optionName ) {
        $params['option_name'] = $optionName;
      }
    }

    // Check params
    $params = $this->_checkParams($params, array(
      'option_id', 'option_name',

      // These are allowed, but you can also use updateProductOptionValue
      'option_value_id', 'option_value_name', 'option_value_surcharge', 
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/update_option')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Used to update an option value.
   *
   * @link http://www.2checkout.com/documentation/api/products-update_option/
   * @param string $optionId
   * @param string $optionName
   * @return array
   */
  public function updateOptionValue($optionId, $optionValueId = null,
      $optionValueName = null, $optionValueSurcharge = null)
  {
    if( is_array($optionId) ) {
      $params = $optionId;
    } else {
      $params = array();
      $params['option_id'] = $optionId;
      if( null !== $optionValueId ) {
        $params['option_value_id'] = $optionValueId;
      }
      if( null !== $optionValueName ) {
        $params['option_value_name'] = $optionValueName;
      }
      if( null !== $optionValueSurcharge ) {
        $params['option_value_surcharge'] = $optionValueSurcharge;
      }
    }

    // Let's run detail to get option_name
    // @todo wtf it doesn't list the option name?
    //$optionData = $this->detailOption($optionId);

    // Check params
    $params = $this->_checkParams($params, array(
      'option_id', 'option_name', 'option_value_id', 'option_value_name',
      'option_value_surcharge', 
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/update_option')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Used to delete an option
   *
   * @link http://www.2checkout.com/documentation/api/products-delete_option/
   * @param string $optionId
   * @return Engine_Service_2Checkout 
   */
  public function deleteOption($optionId)
  {
    if( is_array($optionId) ) {
      $params = $optionId;
    } else {
      $params = array();
      $params['option_id'] = $optionId;
    }

    // Let's run detail to get option_name
    // @todo wtf it doesn't list the option name?
    //$optionData = $this->detailOption($optionId);

    // Check params
    $params = $this->_checkParams($params, array(
      'option_id',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/delete_option')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Used to delete an option value.
   *
   * @link http://www.2checkout.com/documentation/api/products-update_option/
   * @param string $optionId
   * @param string $optionValueId
   * @return Engine_Service_2Checkout
   */
  public function deleteOptionValue($optionId, $optionValueId = null)
  {
    if( is_array($optionId) ) {
      $params = $optionId;
    } else {
      $params = array();
      $params['option_id'] = $optionId;
      if( null !== $optionValueId ) {
        $params['option_value_id'] = $optionName;
      }
    }

    // Let's run detail to get option_name
    // @todo wtf it doesn't list the option name?
    //$optionData = $this->detailOption($optionId);

    // Check params
    $params = $this->_checkParams($params, array(
      'option_id', 'option_name', 'option_value_id',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/update_option')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }


  
  // Coupon Information

  /**
   * Used to retrieve the details for a single coupon.
   *
   * @link http://www.2checkout.com/documentation/api/products-detail_coupon/
   * @param mixed $couponCode The string value of coupon code. Required.
   * @return array 
   */
  public function detailCoupon($couponCode)
  {
    // Build params
    if( is_array($couponCode) ) {
      $params = $couponCode;
    } else {
      $params = array();
      $params['coupon_code'] = $couponCode;
    }

    // Check params
    $params = $this->_checkParams($params, 'coupon_code');

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/detail_coupon')
      ->setMethod(Zend_Http_Client::GET)
      ->setParameterGet($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    if( is_array($responseData) ) {
      return $responseData['coupon'];
    } else {
      return $responseData;
    }
  }

  /**
   * Used to retrieve list of all coupons in the account.
   *
   * @link http://www.2checkout.com/documentation/api/products-list_coupons/
   * @return array 
   */
  public function listCoupons()
  {
    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/list_coupons')
      ->setMethod(Zend_Http_Client::GET)
      ;

    // Process response
    $response = $client->request();
    try {
      $responseData = $this->_processHttpResponse($response);
    } catch( Engine_Service_2Checkout_Exception $e ) {
      if( $e->getCode() == Engine_Service_2Checkout_Exception::RECORD_NOT_FOUND ) {
        return array();
      } else {
        throw $e;
      }
    }

    return $responseData;
  }



  // Update coupons

  /**
   * Used to create a new coupon.
   *
   * @link http://www.2checkout.com/documentation/api/products-create_coupon/
   * @param array $params
   * @return string ID assigned to the coupon by 2Checkout.
   */
  public function createCoupon($params)
  {
    // Check params
    $params = $this->_checkParams($params, array(
      'date_expire', 'type',
    ), array(
      'coupon_code', 'percentage_off', 'value_off', 'minimum_purchase',
      'product_id', 'select_all',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/create_coupon')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    if( is_array($responseData) ) {
      return $responseData['coupon_code'];
    } else {
      return $responseData;
    }
  }

  /**
   * Used to update a coupon.
   * 
   * @link http://www.2checkout.com/documentation/api/products-update_coupon/
   * @param string $couponCode
   * @param array $params
   * @return Engine_Service_2Checkout 
   */
  public function updateCoupon($couponCode, $params = null)
  {
    if( is_array($couponCode) ) {
      $params = $couponCode;
    } else {
      if( !is_array($params) ) {
        $params = array();
      }
      $params['coupon_code'] = $couponCode;
    }
    
    // Check params
    $params = $this->_checkParams($params, array(
      'coupon_code', 'type',
    ), array(
      'percentage_off', 'value_off', 'minimum_purchase',
      'product_id', 'select_all', 'new_code', 'date_expire',
    ));

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/update_coupon')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }

  /**
   * Used to delete a coupon.
   *
   * @link http://www.2checkout.com/documentation/api/products-delete_coupon/
   * @param string $couponCode  String value of coupon code for deleting coupon.
   *                            Required.
   * @return Engine_Service_2Checkout 
   */
  public function deleteCoupon($couponCode)
  {
    if( is_array($couponCode) ) {
      $params = $couponCode;
    } else {
      $params = array();
      $params['coupon_code'] = $couponCode;
    }

    // Check params
    $params = $this->_checkParams($params, 'coupon_code');

    // Send request
    $client = $this->_prepareHttpClient();
    $client
      ->setUri('https://www.2checkout.com/api/products/delete_coupon')
      ->setMethod(Zend_Http_Client::POST)
      ->setParameterPost($params)
      ;

    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);

    return $this;
  }



  // Utility

  /**
   * Get the http client and set default parameters
   * 
   * @return Zend_Http_Client
   */
  protected function _prepareHttpClient()
  {
    return $this->getHttpClient()
      ->resetParameters()
      ->setAuth($this->_username, $this->_password)
      ->setHeaders('Accept', $this->_accept)
      ;
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
    
    // Check response body
    $responseData = $response->getBody();
    if( !is_string($responseData) || '' === $responseData ) {
      throw new Engine_Service_2Checkout_Exception('HTTP Client returned an ' .
          'empty response', 'IS_EMPTY');
    }

    // These are only supported using json
    if( 'json' === $this->_format ) {

      // Decode response body
      $responseData = Zend_Json::decode($responseData, Zend_Json::TYPE_ARRAY);
      if( !is_array($responseData) ) {
        throw new Engine_Service_2Checkout_Exception('HTTP Client returned ' .
            'invalid JSON response', 'NOT_VALID');
      }

      // Check for special global error keys
      if( !empty($responseData['errors']) ) {
        foreach( $responseData['errors'] as $message ) {
          throw new Engine_Service_2Checkout_Exception(sprintf('API Error: ' .
              '[%1$s] %2$s', $message['code'], $message['message']),
              $message['code']);
        }
      }

      // Check for warnings
      if( !empty($responseData['warnings']) ) {
        foreach( $responseData['warnings'] as $message ) {
          throw new Engine_Service_2Checkout_Exception(sprintf('API Warning: ' .
              '[%1$s] %2$s', $message['code'], $message['message']),
              $message['code']);
        }
      }

      // Check for response status and message
      if( 'OK' !== $responseData['response_code'] ) {
        throw new Engine_Service_2Checkout_Exception(sprintf('Response Error: ' .
            '[%1$s] %2$s', $responseData['response_code'],
            $responseData['response_message']), $responseData['response_code']);
      }

    }

    // Check HTTP Status code
    if( 200 !== $response->getStatus() ) {
      // Note: looks like 2checkout gives 400 for invalid parameters
      throw new Engine_Service_2Checkout_Exception(sprintf('HTTP Client ' .
          'returned error status: %1$d', $response->getStatus()), 'HTTP');
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
  protected function _checkParams(array $params,
      $requiredParams = null, $supportedParams = null)
  {
    // Check params
    if( !is_array($params) ) {
      if( !empty($params) ) {
        throw new Engine_Service_2Checkout_Exception('Invalid data type',
            'UNKNOWN_PARAM');
      } else {
        $params = array();
      }
    }

    // Check required params
    if( is_string($requiredParams) ) {
      $requiredParams = array($requiredParams);
    } else if( !is_array($requiredParams) ) {
      $requiredParams = array();
    }

    // Check supported params
    if( is_string($supportedParams) ) {
      $supportedParams = array($supportedParams);
    } else if( !is_array($supportedParams) ) {
      $supportedParams = array();
    }

    // Nothing to do
    if( empty($requiredParams) && empty($supportedParams) ) {
      return array();
    }
    
    // Build full supported
    $supportedParams = array_unique(array_merge($supportedParams, $requiredParams));
    
    // Check supported
    if( count($params) > 0 &&
        count($unsupportedParams = array_diff(array_keys($params), $supportedParams)) > 0 ) {
      $paramStr = '';
      foreach( $unsupportedParams as $unsupportedParam ) {
        if( $paramStr != '' ) $paramStr .= ', ';
        $paramStr .= $unsupportedParam;
      }
      throw new Engine_Service_2Checkout_Exception(sprintf('Unknown param(s): ' .
          '%1$s', $paramStr), 'UNKNOWN_PARAM');
    }

    // Check required
    if( count($requiredParams) > 0 &&
        count($missingRequired = array_diff($requiredParams, array_keys($params))) > 0 ) {
      $paramStr = '';
      foreach( $missingRequired as $missingRequiredParam ) {
        if( $paramStr != '' ) $paramStr .= ', ';
        $paramStr .= $missingRequiredParam;
      }
      throw new Engine_Service_2Checkout_Exception(sprintf('Missing required ' .
          'param(s): %1$s', $paramStr), 'MISSING_REQUIRED');
    }
    
    return $params;
  }
}