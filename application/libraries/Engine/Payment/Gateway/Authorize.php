<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Authorize.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Payment_Gateway_Authorize extends Engine_Payment_Gateway
{



  // General

  public function  __construct(array $options = null)
  {
    parent::__construct($options);

    if( null === $this->getGatewayUrl() ) {
      //https://test.authorize.net/gateway/transact.dll
      $this->setGatewayUrl('https://secure.authorize.net/gateway/transact.dll');
    }

    if( null === $this->getGatewayVersion() ) {
      $this->setGatewayVersion('3.0');
    }
  }


  
  // Actions

  public function validateIpn(Engine_Payment_Ipn $ipn)
  {
    // Check gateway for info
    if( null == ($vendorIdentity = $this->getVendorIdentity()) ) {
      $this->_throw('Unable to validate IPN: vendor identity is missing.');
      return false;
    }
    if( null == ($vendorSecret = $this->getVendorSecret()) ) {
      $this->_throw('Unable to validate IPN: vendor secret is missing.');
      return false;
    }

    // Get raw data
    $rawData = $ipn->getRawData();

    // Check for empty parameters
    if( !isset($rawData['x_trans_id']) ) {
      $this->_throw('Unable to validate IPN: x_trans_id is missing.');
      return false;
    }
    if( !isset($rawData['x_amount']) ) {
      $this->_throw('Unable to validate IPN: x_amount is missing.');
      return false;
    }
    if( !isset($rawData['x_MD5_Hash']) ) {
      $this->_throw('Unable to validate IPN: x_MD5_Hash is missing.');
      return false;
    }
    if( !isset($rawData['x_response_code']) ) {
      $this->_throw('Unable to validate IPN: x_response_code is missing.');
      return false;
    }

    // Validate hash
    $givenHash = strtoupper($rawData['x_MD5_Hash']);
    $expectedHash = strtoupper(md5($vendorSecret . $vendorIdentity .
        $rawData['x_trans_id'] . $rawData['x_amount']));

    if( $givenHash !== $expectedHash ) {
      $this->_throw(sprintf('Unable to validate IPN: hashes do not match - given %s, expected %s',
          $givenHash, $expectedHash));
      return false;
    } else if( $rawData['x_response_code'] != '1' ) {
      $this->_throw(sprintf('Unable to validate IPN: invalid response code - %s',
          $rawData['x_response_code']));
      return false;
    }

    return true;
  }

  public function processIpn(Engine_Payment_Ipn $ipn)
  {
    $rawData = $ipn->getRawData();

    // Process: type

    $data = $rawData;

    return $data;
  }



  // Transaction

  public function processTransaction(Engine_Payment_Transaction $transaction)
  {
    $data = array();
    $rawData = $transaction->getRawData();

    // Add vendor id
    $data['x_Login'] = $this->getVendorIdentity();

    // Add version
    $data['x_Version'] = $this->getGatewayVersion();

    // Add test mode
    if( $this->getTestMode() ) {
      $data['x_Test_Request'] = 'TRUE';
    }

    // Add timestamp
    $data['x_fp_timestamp'] = time();
    
    // No idea what this is
    $data['x_Show_Form'] = 'PAYMENT_FORM';

    // No idea what this is either
    $data['x_Relay_Response'] = 'TRUE';


    // @todo the other stuff

    // @todo esp x_Invoice_num + x_Amount

    $data['x_fp_sequence'] = $data['x_Invoice_num'];

    // Make hash
    $data['x_fp_hash'] = self::hmac($this->getVendorSecret(),
        $data['x_Login'] . '^' .
        $data['x_Invoice_num'] . '^' .
        $data['x_fp_timestamp'] . '^' .
        $data['x_Amount'] . '^');

    
    return $data;
  }

  public function validateTransaction(Engine_Payment_Transaction $transaction)
  {
    $data = $transaction->getData();

    if( empty($data['x_Login']) ) {
      $this->_throw('No vendor identity provided');
      return false;
    }

    if( empty($data['x_Invoice_num']) ) {
      $this->_throw('No invoice identity provided');
      return false;
    }

    if( empty($data['x_Amount']) ) {
      $this->_throw('No total provided');
      return false;
    }
    
    return true;
  }



  // Admin

  public function test()
  {
    throw new Engine_Payment_Gateway_Exception('Not yet implemented');
  }
}
