<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Engine_Payment_Plugin_Abstract
{
  // General

  /**
   * Constructor
   */
  abstract public function __construct(Zend_Db_Table_Row_Abstract $gatewayInfo);

  /**
   * Get the service API
   *
   * @return Zend_Service_Abstract
   */
  abstract public function getService();

  /**
   * Get the gateway object
   *
   * @return Engine_Payment_Gateway
   */
  abstract public function getGateway();



  // Actions

  /**
   * Create a transaction object from specified parameters
   *
   * @return Engine_Payment_Transaction
   */
  abstract public function createTransaction(array $params);

  /**
   * Create an ipn object from specified parameters
   *
   * @return Engine_Payment_Ipn
   */
  abstract public function createIpn(array $params);



  // SE Specific

  /**
   * Create a transaction for a subscription
   *
   * @param User_Model_User $user
   * @param Zend_Db_Table_Row_Abstract $subscription
   * @param Payment_Model_Package $package
   * @param array $params
   * @return Engine_Payment_Gateway_Transaction
   */
  abstract public function createSubscriptionTransaction(User_Model_User $user,
      Zend_Db_Table_Row_Abstract $subscription,
      Payment_Model_Package $package,
      array $params = array());

  /**
   * Process return of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param array $params
   */
  abstract public function onSubscriptionTransactionReturn(
      Payment_Model_Order $order, array $params = array());

  /**
   * Process ipn of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param Engine_Payment_Ipn $ipn
   */
  abstract public function onSubscriptionTransactionIpn(
      Payment_Model_Order $order,
      Engine_Payment_Ipn $ipn);

  /**
   * Cancel a subscription (i.e. disable the recurring payment profile)
   *
   * @params $transactionId
   * @return Engine_Payment_Plugin_Abstract
   */
  abstract public function cancelSubscription($transactionId);



  // Informational
  
  /**
   * Generate href to a page detailing the order
   *
   * @param string $transactionId
   * @return string
   */
  abstract public function getOrderDetailLink($orderId);
  
  /**
   * Generate href to a page detailing the transaction
   *
   * @param string $transactionId
   * @return string
   */
  abstract public function getTransactionDetailLink($transactionId);

  /**
   * Get raw data about an order or recurring payment profile
   *
   * @param string $orderId
   * @return array
   */
  abstract public function getOrderDetails($orderId);

  /**
   * Get raw data about a transaction
   *
   * @param $transactionId
   * @return array
   */
  abstract public function getTransactionDetails($transactionId);


  
  // IPN

  /**
   * Process an IPN
   * 
   * @param Engine_Payment_Ipn $ipn
   * @return Engine_Payment_Plugin_Abstract
   */
  abstract public function onIpn(Engine_Payment_Ipn $ipn);

  /**
   * Process a return
   *
   * @param Payment_Model_Order $order
   * @return Engine_Payment_Plugin_Abstract
   */
  public function onReturn(Payment_Model_Order $order, array $params = array())
  {
    if( $order->source_type == 'payment_subscription' ) {
      $this->onSubscriptionTransactionReturn($order, $params);
    } else {
      throw new Engine_Payment_Plugin_Exception('Unknown order type');
    }
    return $this;
  }


  // Forms

  /**
   * Get the admin form for editing the gateway info
   *
   * @return Engine_Form
   */
  abstract public function getAdminGatewayForm();

  abstract public function processAdminGatewayForm(array $values);
}