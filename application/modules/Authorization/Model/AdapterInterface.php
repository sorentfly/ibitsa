<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdapterInterface.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
interface Authorization_Model_AdapterInterface
{
  /**
   * Get the adapter name
   * 
   * @return string
   */
  public function getAdapterName();

  /**
   * Get the order of the adapter
   *
   * @return integer
   */
  public function getAdapterPriority();

  /**
   * Check if an action is allowed. This is intended to be used with boolean
   * settings only. For non-boolean permissions, use getAllowed(). Adapters
   * should return Authorization_Model_Api::LEVEL_INCONCLUSIVE if no permission
   * is set, the Api will return Authorization_Model_Api::LEVEL_DISALLOW
   * automatically.
   *
   * @param Core_Model_Item_Abstract|mixed $resource The item the action is being performed on
   * @param Core_Model_Item_Abstract|mixed $role The item performing the action
   * @param string $action The action being performed
   */
  public function isAllowed($resource, $role, $action);

  /**
   * Gets the value of a permission setting. If only checking if an action is
   * allowed, use isAllowed()
   *
   * @param Core_Model_Item_Abstract|mixed $resource The item the action is being performed on
   * @param Core_Model_Item_Abstract|mixed $role The item performing the action
   * @param string $action The action being performed
   */
  public function getAllowed($resource, $role, $action);

  /**
   * Sets permissions. Omit value and pass an array as action to set multiple
   * values
   *
   * @param Core_Model_Item_Abstract|mixed $resource The item the action is being performed on
   * @param Core_Model_Item_Abstract|mixed $role The item performing the action
   * @param string $action The action being performed
   * @param mixed $value The value of the permissions
   */
  public function setAllowed($resource, $role, $action, $value = null);
}