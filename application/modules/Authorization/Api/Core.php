<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
/**
 * @property Authorization_Model_DbTable_Allow $context 
 * @property Authorization_Model_DbTable_Permissions $levels
 * @property Authorization_Model_DbTable_Direct $direct 
 */
class Authorization_Api_Core extends Core_Api_Abstract
{
  /**
   * Constants
   */
	

  const LEVEL_DISALLOW = 0;
  const LEVEL_ALLOW = 1;
  const LEVEL_MODERATE = 2;
  const LEVEL_NONBOOLEAN = 3;
  const LEVEL_IGNORE = 4;
  const LEVEL_SERIALIZED = 5;

  /**
   * @var array an array of registered adapters
   */
  protected $_adapters = array();

  /**
   * @var array Adapter names by order
   */
  protected $_order = array();

  /**
   * @var bool Need to sort adapters?
   */
  protected $_needsSort = false;

  static protected $_constants = array(
    0 => 'disallow',
    1 => 'allow',
    2 => 'moderate',
    3 => 'nonboolean',
    4 => 'ignore',
    5 => 'serialized',
  );



  // General

  static public function getConstantKey($constantValue)
  {
    if( is_scalar($constantValue) && isset(self::$_constants[$constantValue]) ) {
      return self::$_constants[$constantValue];
    }
    return null;
  }


  
  /**
   * Constructor
   */
  public function __construct()
  {
    $this->loadDefaultAdapters();
  }

  /**
   * Magic getter (gets an adapter)
   *
   * @param string $key The adapter type
   * @return Authorization_Model_Adapter_Abstract
   */
  public function __get($key)
  {
    return $this->getAdapter($key);
  }

  /**
   * Gets the specified permission for the context
   *
   * @param Core_Model_Item_Abstract|string $resource The resource type or object that is being accessed
   * @param Core_Model_Item_Abstract $role The item (user) performing the action
   * @param string $action The name of the action being performed
   * @return mixed 0/1 for allowed, or data for settings
   */
  public function isAllowed($resource, $role, $action = 'view')
  {
    if( null === $resource ) {
      try {
        $resource = Engine_Api::_()->core()->getSubject();
      } catch( Exception $e ) {
        
      }
      if( !$resource ) {
        return false;
      }
    }
    
    if( null === $role ) {
      $viewer = Engine_Api::_()->user()->getViewer();
      if( null !== $viewer && $viewer->getIdentity() ) {
        $role = $viewer;
      } else {
        $role = 'everyone';
      }
    }

    if ( ($module = Engine_Api::_()->getItemModule(is_object($resource) ? $resource->getType() : $resource)) ) {
        $api = Engine_Api::_()->getApi('core', $module);
        if (!in_array($module, ['authorization', 'zftsh']) && method_exists($api, 'isAllowed')){
            if (($result = $api->isAllowed($resource, $role, $action)) != self::LEVEL_IGNORE){
                return $result;
            }
        }
    }
    
    /*ABITU modification - items can rule ACL by themself*/
    if( is_object($resource) )
    {
      if ( ($role instanceof User_Model_User) && !($resource instanceof Zftsh_Model_Academy) ){
          if ($action == 'comment'){
            if (($result = $this->isCommentModerationAllowed($resource, $role, 'comment')) != self::LEVEL_IGNORE){
                return $result;
            }
          }
          if ($action == 'edit_comment'){
              if (($result = $this->isCommentModerationAllowed($resource, $role, 'edit')) != self::LEVEL_IGNORE){
                  return $result;
              }
              $action = 'edit';
          }
      }
      // Allow resource to specify manual authorization check
      if ( ($result = $resource->isAllowed($role, $action)) != self::LEVEL_IGNORE ){
          return $result;
      }
      // Allow resource to specify an object that it inherits permissions from
      $authProxy = $resource->getAuthorizationItem();
      if ($authProxy && !$authProxy->isSelf($resource)){
        $resource = $authProxy;
        // Allow authorization proxy item to specify manual authorization check
        if ( ($result = $resource->isAllowed($role, $action)) != self::LEVEL_IGNORE ){
            return $result;
        }
      }
    }

      if ($action == 'edit_comment'){
          $action = 'edit';
      }
      /*ABITU modification - END */

      if( !is_string($action) )
      {
        throw new Authorization_Model_Exception('action must be a string');
      }

      // Iterate over each adapter and check permission
      $final = self::LEVEL_DISALLOW;
      foreach( $this->getAdapters() as $adapter )
      {
        $result = $adapter->isAllowed($resource, $role, $action);
        switch( $result ) {
          // Unknown value, ignore, nonboolean
          default:
          case self::LEVEL_IGNORE:
          case self::LEVEL_NONBOOLEAN:
          case self::LEVEL_SERIALIZED:
            continue;
            break;
          case self::LEVEL_DISALLOW:
            return self::LEVEL_DISALLOW;
            break;
          case self::LEVEL_MODERATE:
            return self::LEVEL_ALLOW;
            break;
          case self::LEVEL_ALLOW:
            $final = self::LEVEL_ALLOW;
            break;
        }
      }

      return $final;
    }

    protected static $commentModerationCache = [];/*resource+role => false/edit/comment/banned */
    /**
     * @param Core_Model_Item_Abstract|string $resource The resource type or object that is being accessed
     * @param User_Model_User $role The user performing the action
     * @param 'comment'|'edit' $action The name of the action being performed
     * @return int for allowed, or data for settings
     */
  public function isCommentModerationAllowed(Core_Model_Item_Abstract $resource, User_Model_User $role, $action = 'comment')
  {
      if (!$role->getIdentity()) return false;
      $cacheKey = $resource->getGuid().'+'.$role->getIdentity();
      if (isset(self::$commentModerationCache[$cacheKey])){
          if (!self::$commentModerationCache[$cacheKey]){
              return self::LEVEL_IGNORE;
          }
          if (self::$commentModerationCache[$cacheKey] == 'banned'){
            return self::LEVEL_DISALLOW;
          }
          if (self::$commentModerationCache[$cacheKey] == 'edit'){
              return self::LEVEL_ALLOW;
          }
          return self::$commentModerationCache[$cacheKey] == 'comment' && $action == 'comment' ? self::LEVEL_ALLOW : self::LEVEL_IGNORE;
      }
      if ($role->level_id == 3){
          self::$commentModerationCache[$resource->getGuid().'+'.$role->getIdentity()] = 'edit';
          return self::LEVEL_ALLOW;
      }
      if ( ($resource instanceof Zftsh_Model_Academy) || ($academy = Engine_Api::_()->zftsh()->getAcademyOf($resource)) ){
          /* @var Zftsh_Model_Academy $academy */
          if ($resource instanceof Zftsh_Model_Academy){
              $academy = $resource;
          }
          /* @var Authorization_Model_DbTable_Direct $directPermissions */
          $directPermissions = Engine_Api::_()->getDbTable('direct', 'authorization');

          /*Получение $allow для редактирования комментов по админскому уровню, либо по списку $directPermissions */
          $allow = in_array($role->level_id, [1,2]) ? self::LEVEL_ALLOW : (int)$directPermissions->isAllowed($academy, $role, 'edit_comment');
          if (($allow == self::LEVEL_IGNORE) && ($membership = $academy->membership()->getMemberInfo($role))){
              $allow = $membership->role == 'content_manager' ? self::LEVEL_ALLOW : self::LEVEL_IGNORE;
          }

          /*Сначала проверка edit*/
          if ($allow != self::LEVEL_IGNORE){
              self::$commentModerationCache[$cacheKey] = $allow ? 'edit' : 'banned';
              /*Если edit разрашено то и comment разрешено*/
              if ($action == 'edit' || $allow){
                  return $allow;
              }
          }else{
              /*Если edit не разрешено - проверяем comment, но возвращаем значение comment-права только если запрашивается comment-право (параметр $action) */
              $allow = (int)$directPermissions->isAllowed($academy, $role, 'comment');

              if ($allow == self::LEVEL_DISALLOW){
                  self::$commentModerationCache[$cacheKey] = 'banned';
              }else if ($allow == self::LEVEL_ALLOW){
                  self::$commentModerationCache[$cacheKey] = 'comment';
              }else if ($allow == self::LEVEL_IGNORE) {
                  self::$commentModerationCache[$cacheKey] = false;
              }
              if ($action == 'comment'){
                  return $allow;
              }
          }
          return self::LEVEL_IGNORE;
      }
      self::$commentModerationCache[$cacheKey] = false;
      return self::LEVEL_IGNORE;
  }
    /**
     * @param Core_Model_Item_Abstract|string $resource The resource type or object that is being accessed
     * @param User_Model_User $role The user performing the action
     * @return false|'edit'|'comment'|'banned'
     */
  public function getCommentModerationAllowed(Core_Model_Item_Abstract $resource,User_Model_User $role)
  {
      $cacheKey = $resource->getGuid().'+'.$role->getIdentity();
      if (isset(self::$commentModerationCache[$cacheKey])){
          return self::$commentModerationCache[$cacheKey];
      }else{
          $this->isCommentModerationAllowed($resource, $role);
          return isset(self::$commentModerationCache[$cacheKey]) ? self::$commentModerationCache[$cacheKey] : false;
      }
  }

    /**
     * @param Core_Model_Item_Abstract $resource The resource type or object that is being accessed
     * @param User_Model_User $role|null The user to get status of.
     * @return array
     */
  public function getCommentAllowStatus($resource, User_Model_User $role)
  {
      if (!$resource){
          return ['status' => '', 'class' => 'none'];
      }
      /* @var Core_Model_Item_Abstract $resource */
      $t = Zend_Registry::get('Zend_Translate');
      $globalStatus = $this->getCommentModerationAllowed($resource, $role);

      if ($globalStatus == 'banned'){
          return ['status' => $t->translate('Ban'), 'class' => 'banned'];
      }else if ($globalStatus == 'edit'){
          return ['status' => $t->translate('Moderator'), 'class' => 'moderator'];
      }

      if ($academy = Engine_Api::_()->zftsh()->getAcademyOf($resource)){
       /* @var Zftsh_Model_Academy $academy */
        if ($memberInfo = $academy->membership()->getMemberInfo($role)){
            return ['status' => $t->translate(ucfirst($memberInfo->role)), 'class' => ($memberInfo->role == 'methodist' || $memberInfo->role == 'content_manager') ? 'moderator' : 'member'];
        }else{
            return ['status' => '', 'class' => 'none'];
        }
      }else{
        /* TODO - получить первую сущность с membership в цепочке вложенности */
        if (strpos($resource->getType(), '_post')!==false || strpos($resource->getType(), '_topic')!==false  ){
            $memberItem = $resource->getParent('course');
        }else{
            $memberItem = $resource;
        }

        if ($memberItem->isOwner($role) ){
            return ['status' => $t->translate('Host'), 'class' => 'host'];
        }else if (($membership = $memberItem->membership()->getMemberInfo($role))){
            if (isset($membership->organizer) && $membership->organizer){
                return ['status' => $t->translate('Host'), 'class' => 'host'];
            }else{
                return ['status' =>  $t->translate('Member'), 'class' => 'member'];
            }
        }else{
            return ['status' => '', 'class' => 'none'];
        }
      }
  }



  // Adapters

  /**
   * Adds an authorization adapter to the stack
   *
   * @param Authorization_Model_Adapter_Abstract $adapter The authorization adapter
   * @param int $order The order for execution
   * @return Authorization_Model_Api
   */
  public function addAdapter(Authorization_Model_AdapterInterface $adapter)
  {
    $name = $adapter->getAdapterName();
    $this->_adapters[$name] = $adapter;
    $this->_order[$name] = $adapter->getAdapterPriority();
    $this->_needsSort = true;
    return $this;
  }

  /**
   * Clears the current adapters
   *
   * @return Authorization_Model_Api
   */
  public function clearAdapters()
  {
    $this->_adapters = array();
    $this->_order = array();
    return $this;
  }

  /**
   * Gets an adapter by class name
   *
   * @param string $type The type of the adapter
   * @return Authorization_Model_Adapter_Abstract|null
   */
  public function getAdapter($type)
  {
    return $this->_adapters[$type];
  }

  public function getAdapters()
  {
    $this->_sort();

    $adapters = array();
    foreach( $this->_order as $type => $order ) {
      $adapters[] = $this->_adapters[$type];
    }

    return $adapters;
  }

  /**
   * Set the order of an adapter
   *
   * @param string $name The name of the adapter
   * @param int $order The order to set
   * @return Authorization_Model_Api
   */
  public function setAdapterOrder($name, $order = 100)
  {
    if( isset($this->_adapters[$name]) )
    {
      $this->_order[$name] = $order;
    }

    return $this;
  }

  /**
   * Removes an adapter by class name
   *
   * @param string $name The name of the adapter
   * @return Authorization_Model_Api
   */
  public function removeAdapter($name)
  {
    if( $name instanceof Authorization_Model_AdapterInterface )
    {
      $name = $name->getAdapterName();
    }

    if( is_string($name) )
    {
      unset($this->_adapters[$name]);
      unset($this->_order[$name]);
      $this->_needsSort = true;
    }

    return $this;
  }

  /**
   * Loads the default adapters
   *
   * @return Authorization_Model_Api
   */
  public function loadDefaultAdapters()
  {
    if( empty($this->_adapters) )
    {
      $this->addAdapter(Engine_Api::_()->getDbtable('direct', 'authorization'), 300)
				->addAdapter(Engine_Api::_()->getDbtable('permissions', 'authorization'), 150)
				->addAdapter(Engine_Api::_()->getDbtable('allow', 'authorization'), 50);
    }

    return $this;
  }
  
  protected function _sort()
  {
    if( $this->_needsSort )
    {
      arsort($this->_order);
      $this->_needsSort = false;
    }
  }



  // permissions functions

  public function getPermission($level_id, $type, $name)
  {
    if( $level_id instanceof User_Model_User ) {
      $level_id = $level_id->level_id;
    }
    $permissionTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $select = $permissionTable->select()
      ->where('level_id = ?', $level_id)
      ->where('type = ?', $type)
      ->where('name = ?', $name)
      ;

    $level_permission = $permissionTable->fetchRow($select);
    if( !$level_permission ) {
      return self::LEVEL_DISALLOW;
    } else if( !empty($level_permission->params) ) {
      return $level_permission->params;
    } else {
      return $level_permission->value;
    }
  }

  public function isReAuthenticated()
  {
      if( Engine_Api::_()->getApi('settings', 'core')->core_admin_reauthenticate ) {
          $session = new Zend_Session_Namespace('Core_Auth_Reauthenticate');
          $timeout = Engine_Api::_()->getApi('settings', 'core')->core_admin_timeout;
          if( $timeout && (time() > $timeout + $session->start) ) {
              unset($session->identity);
          }
          if( empty($session->identity) ) {
              return false;
          }
      }

      return true;
  }

}