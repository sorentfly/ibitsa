<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Allow.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Authorization_Model_DbTable_Allow extends Engine_Db_Table
  implements Authorization_Model_AdapterInterface
{
  public function getAdapterName()
  {
    return 'context';
  }

  public function getAdapterPriority()
  {
    return 50;
  }

  
 const ROLE_PARENT_PROXY 		= 'parent_proxy';
 const ROLE_PARENT_MEMBER 		= 'parent_member';
 const ROLE_PARENT_MODERATOR 	= 'parent_moderator';
 const DELETE_ON_DISALLOW 		= true;

  /**
   * Valid relationship types. Ordered by speed of calculation
   *
   * @var array
   */
  protected $_relationships = array(
    	'everyone',
    	'registered',
  		'parent_member',
    	'member',  	
  		'parent_moderator',
  		'moderator', 
  		'parent_proxy',
    	'owner',
  );

  public function isAllowed($resource, $role, $action)
  {
    // Resource must be an instance of Core_Model_Item_Abstract
    if( !($resource instanceof Core_Model_Item_Abstract) )
    {
      // We have nothing to say about generic permissions
      return Authorization_Api_Core::LEVEL_IGNORE;
    }

    // Role must be an instance of Core_Model_Item_Abstract or a string relationship type
    if( !($role instanceof Core_Model_Item_Abstract) && !is_string($role) )
    {
      // Disallow access to unknown role types
      return Authorization_Api_Core::LEVEL_DISALLOW;
    }

    // Owner can do what they want with the resource
    if( ($role instanceof Core_Model_Item_Abstract && method_exists($resource, 'isOwner') && $resource->isOwner($role)) || $role === 'owner' ){
      return Authorization_Api_Core::LEVEL_ALLOW;
    }
    
    // Now go over set permissions
    // @todo allow for custom types
    $rowset = $this->_getAllowed($resource, $action);

    if( empty($rowset)){
       return Authorization_Api_Core::LEVEL_DISALLOW;
    }
    // Index by type
    $perms = array();
    $permsByOrder = array();
    $items = array();
    foreach( $rowset as $row ) {
      if( empty($row->role_id) ) {
        $index = array_search($row->role, $this->_relationships);
        if( $index === false ) { // Invalid type
          continue;
        }
        $perms[$row->role] = $row;
        $permsByOrder[$index] = $row->role;
      } else {
        $items[] = $row;
      }
    }

			
	// We we're passed a type role, how convenient
	if (is_string($role)) {
		if (isset($perms [$role]) && is_object($perms [$role]) && $perms [$role]->value == Authorization_Api_Core::LEVEL_ALLOW) {
			return Authorization_Api_Core::LEVEL_ALLOW;		
		} else {
			return Authorization_Api_Core::LEVEL_DISALLOW;
		}
	}

    // Scan available types
    foreach ( $permsByOrder as $perm => $type ) {
		$row = $perms [$type];
		$method = 'is_' . $type;
		if (method_exists($this, $method)) {
			$applies = $this->$method($resource, $role);
			if ($applies && $row->value == Authorization_Api_Core::LEVEL_ALLOW) {
				return Authorization_Api_Core::LEVEL_ALLOW;
			}
		} else if ($type == self::ROLE_PARENT_PROXY) {
			$parent = $resource->getParent();
			if (! empty($parent) && !$parent instanceof User_Model_User) {
				if ($this->isAllowed($parent, $role, $action) == Authorization_Api_Core::LEVEL_ALLOW) {
					return Authorization_Api_Core::LEVEL_ALLOW;
				}
			}
		}
	}

    // Ok, lets check the items then
    foreach( $items as $row ) {
      if( !Engine_Api::_()->hasItemType($row->role) ) {
        continue;
      }

      // Item itself is auth'ed
      if( is_object($role) && $role->getType() == $row->role && $role->getIdentity() == $row->role_id ) {
        return Authorization_Api_Core::LEVEL_ALLOW;
      }

      // Get item class
      $itemClass = Engine_Api::_()->getItemClass($row->role);

      // Member of
      if( method_exists($itemClass, 'membership') ) {
        $item = Engine_Api::_()->getItem($row->role, $row->role_id);
        if( $item && $item->membership()->isMember($role, null, $row->subgroup_id) ) {
          return Authorization_Api_Core::LEVEL_ALLOW;
        }
      }

      // List
      else if( method_exists($itemClass, 'has') ) {
        $item = Engine_Api::_()->getItem($row->role, $row->role_id);
        if( $item && $item->has($role) ) {
          return Authorization_Api_Core::LEVEL_ALLOW;
        }
      }
    }
    
    return Authorization_Api_Core::LEVEL_DISALLOW;
  }

  public function getAllowed($resource, $role, $action)
  {
    // Non-boolean values are not yet implemented
    return $this->isAllowed($resource, $role, $action);
  }

  public function setAllowed($resource, $role, $action, $value = false, $role_id = 0)
  {
    // Can set multiple actions
    if( is_array($action) )
    {
      foreach( $action as $key => $value )
      {
        $this->setAllowed($resource, $role, $key, $value, $role_id);
      }

      return $this;
    }

    // Resource must be an instance of Core_Model_Item_Abstract
    if( !($resource instanceof Core_Model_Item_Abstract) )
    {
      throw new Authorization_Model_Exception('$resource must be an instance of Core_Model_Item_Abstract');
    }

    // Ignore owner (since owner is allowed everything)
    /*if( $role === 'owner' ) {
      return $this;
    }*/

    if( is_string($role) ) {
      $role_id = 0;
    } else if( $role instanceof Core_Model_Item_Abstract ) {
      $role_id = $role->getIdentity();
      $role = $role->getType();
    } else if(is_array($role)){
    	return $this->setAllowedRolesMultiple($resource, $role, $action);    	
    }

    // Try to get an existing row
    $select = $this->select()
      ->where('resource_type = ?', $resource->getType())
      ->where('resource_id = ?', $resource->getIdentity())
      ->where('action = ?', $action)
      ->where('role = ?', $role)
      ->where('role_id = ?', $role_id)
      ->limit(1);

    $row = $this->fetchRow($select);


    // Whoops, create a new row)
    if( null === $row && (!self::DELETE_ON_DISALLOW || $value) )
    {    	
      $row = $this->createRow();
      $row->resource_type = $resource->getType();
      $row->resource_id = $resource->getIdentity();
      $row->action = $action;
      $row->role = $role;
      $row->role_id = $role_id;
    }

    if( null !== $row ) {
      if( !self::DELETE_ON_DISALLOW || $value ) {
        $row->value = (bool) $value;
        $row->save();
      } else if( self::DELETE_ON_DISALLOW && !$value ) {
        $row->delete();
      }
    }

    return $this;
  }

   
  protected function _getAllowed(Core_Model_Item_Abstract $resource, $action){
    // Make sure resource has an id (that it exists)
    $resourceType = $this->_getResourceType($resource);
    $resourceId = $this->_getResourceIdentity($resource);       
    if( is_null($resourceId)){
      return null;
    }

    // Get permissions
    $select = $this->select()
      ->from($this->_name, array('role', 'role_id', 'value'))
      ->where('resource_type = ?', $resourceType)
      ->where('resource_id = ?', $resourceId)
      ->where('action = ?', $action);

    $rows = $this->getAdapter()->fetchAll($select, null, Zend_Db::FETCH_OBJ);
    $rows = $this->checkForDefaultValue($rows,$resource, $action);
    return $rows;
  }

	protected function checkForDefaultValue($rows, Core_Model_Item_Abstract $resource, $action) {
		if (!count($rows) || count($rows) == 1 && $rows [0]->role == 'owner' && $rows [0]->value == Authorization_Api_Core::LEVEL_DISALLOW && $rows [0]->role_id == 0) {
			$parent = $resource->getParent();
			$hasParent = !empty($parent) && ! $parent instanceof User_Model_User;
			
			$authConfig = Engine_Api::_()->core()->getAuthSettings($resource->getType());
                        if (!$authConfig || !isset($authConfig['actions'][$action]['default'])){
                            return $rows;
                        }
			$defaultValue = $authConfig['actions'][$action]['default'];
			$result = array();
			foreach ($defaultValue as $defaultRow){
				if( !$hasParent && in_array($defaultRow, self::getParentRoles())) continue;
				$result[] = (object) array(
						'role' => $defaultRow,
						'role_id' => 0,
						'value' => 1,
					);
				
			}	
			return $result;
		} else {
			return $rows;
		}
	}
 
	
	public function setAllowedDefault(Core_Model_Item_Abstract $resource, $action = null){
		$resourceType = $resource->getType();
		$resourceId =  $resource->getIdentity();
		
		$authConfig = Engine_Api::_()->core()->getAuthSettings($resourceType);
		
		if($action == null){
			$action = @array_keys($authConfig['actions']);
			if(empty($action)){return ;}			
		}	
		if(is_array($action)){
			foreach ($action as $actionItem){
				$this->setAllowedDefault($resource, $actionItem);
			}	
		} else if(is_string($action)){
					
			$this->delete(array(
					'resource_type = ?' => $resourceType,
					'resource_id = ?' => $resourceId,
					'action = ?' => $action,
					'role_id = 0'
			));
						
			$defaultValue = @$authConfig['actions'][$action]['default'];
			if(empty($defaultValue)){
				$value = Authorization_Api_Core::LEVEL_ALLOW;
			} else {
				$value = Authorization_Api_Core::LEVEL_DISALLOW;
			}
			
			
			$this->insert(array(
					'resource_type' => $resourceType,
					'resource_id' => $resourceId,
					'action' => $action,
					'role' => 'owner',
					'value' => $value,
					'role_id' => 0));
		}			
		
	}
	
	public function setAllowedRolesMultiple(Core_Model_Item_Abstract $resource, array $roles, $action){
 	
		$resourceType = $resource->getType();
		$resourceId =  $resource->getIdentity();
		
		$authConfig = Engine_Api::_()->core()->getAuthSettings($resourceType);
		$defaultValue = @$authConfig['actions'][$action]['default'];
		$availlableRoles = @$authConfig['actions'][$action]['options'];
		$parent = $resource->getParent();
		if(empty($parent) || $parent instanceof User_Model_User){
			$defaultValue 		= array_diff($defaultValue, self::getParentRoles());
			$availlableRoles 	= array_diff($availlableRoles, self::getParentRoles());
		}
		
		$this->getAdapter()->beginTransaction();
		try{
			// Удаляем все что есть
			$this->delete(array(
					'resource_type = ?' => $resourceType,
					'resource_id = ?' => $resourceId,
					'action = ?' => $action,
					'role_id = 0'
			));			
		 	if( is_array($defaultValue) && !array_diff($roles, $defaultValue) && !array_diff($defaultValue, $roles)) {
		 		$this->insert(array(
			 		'resource_type' => $resourceType,
			 		'resource_id' => $resourceId,
			 		'action' => $action,
			 		'role' => 'owner',
		 			'value' => Authorization_Api_Core::LEVEL_DISALLOW,
			 		'role_id' => 0));		 		
		 	} else {
		 		// Активные роли
		 		foreach ($roles as $role){
		 			$this->setAllowed($resource, $role, $action, Authorization_Api_Core::LEVEL_ALLOW);
		 		}
		 		// Неактивные роли, если надо
		 		if(!self::DELETE_ON_DISALLOW){
		 			foreach ($availlableRoles as $role){
		 				if(!in_array($role, $roles)){
		 					$this->setAllowed($resource, $role, $action, Authorization_Api_Core::LEVEL_DISALLOW);
		 				}
		 			}
		 		}
		 	}
	 		
	 	} catch (Exception $e){
	 		$this->getAdapter()->rollBack();
	 		throw $e;
	 	}	 	
	 	$this->getAdapter()->commit();
 	}
 
 	

  // Calculators

  // Tier 1

  public function is_everyone($resource, $role)
  {
    return true;
  }
  
  public function is_registered($resource, $role)
  {
    if( $role === 'registered' ) {
      return true;
    }
    if( !$role instanceof Core_Model_Item_Abstract ) {
      return false;
    }
    return (bool) $role->getIdentity();
  }

  public function is_member($resource, $role)
  {
    if( $role === 'member' ) {
      return true;
    }
    if( !$role instanceof User_Model_User ) {
      return false;
    }
    if( !method_exists($resource, 'membership') ) {
      return false;
    }
    return $resource->membership()->isMember($role, true);
  }
  
  public function is_parent_member($resource, $role){
  	if( $role === 'parent_member' ) {
  		return true;
  	}
  	if( !($role instanceof Core_Model_Item_Abstract) || !($role instanceof User_Model_User) ) {
  		return false;
  	}
  	$parent = $resource->getParent();
  	if(empty($parent) || !($parent instanceof Core_Model_Item_Abstract)){
  		return false;
  	}
  	if( !method_exists($parent, 'membership') ) {
  		return false;
  	}
  	return $parent->membership()->isMember($role, true);
  }
  
  public function is_owner($resource, $role)
  {
    if( $role === 'owner' ) {
      return true;
    }
    if( !$role instanceof Core_Model_Item_Abstract ) {
      return false;
    }
    return $role->isSelf($resource->getOwner());
  }
  
  public function is_moderator($resource, $role){
  	if( $role === 'moderator' ) {
  		return true;
  	}
  	if($resource instanceof Core_Model_Item_Abstract && $role instanceof User_Model_User && method_exists($resource, 'isModerator')){  		
  		return $resource->isModerator($role);
  	}
  	return false;  	
  }

  public function is_parent_moderator($resource, $role){
  	if( $role === 'parent_moderator' ) {
  		return true;
  	}
  	if(!$role instanceof User_Model_User){
  		return false;
  	}
  	$parent = $resource->getParent();
    if(empty($parent) || $parent instanceof User_Model_User){
    	return false;
    }
  	if($parent instanceof Core_Model_Item_Abstract && method_exists($parent, 'isModerator')){
  		return $parent->isModerator($role);
  	} else {
  		return false;
  	}  	
  }
  
  static public function getParentRoles() {
  	return array(
  		self::ROLE_PARENT_MEMBER,
  		self::ROLE_PARENT_MODERATOR,
  		self::ROLE_PARENT_PROXY
  	);
  }
  
  // Utility
  
  protected function _getResourceType($resource)
  {
    if( is_string($resource) )
    {
      return $resource;
    }

    else if( is_array($resource) && isset($resource[0]) )
    {
      return $resource[0];
    }

    else if( $resource instanceof Core_Model_Item_Abstract )
    {
      return $resource->getType();
    }

    else
    {
      return null;
    }
  }

  /**
   * Returns the identity of a resource from several possible formats:
   * Core_Model_Item_Abstract->getIdentity()
   * integer
   * array(type, identity)
   *
   * @param mixed $resource
   * @return mixed The identity of the resource
   */
  protected function _getResourceIdentity($resource)
  {
    if( is_numeric($resource) )
    {
      return $resource;
    }

    else if( is_array($resource) && isset($resource[1]) )
    {
      return $resource[1];
    }

    else if( $resource instanceof Core_Model_Item_Abstract )
    {
      return $resource->getIdentity();
    }

    else
    {
      return null;
    }
  }
}