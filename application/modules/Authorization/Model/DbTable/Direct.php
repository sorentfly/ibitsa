<?php
class Authorization_Model_DbTable_Direct extends Engine_Db_Table implements Authorization_Model_AdapterInterface {

	public function getAdapterName() {
		return 'direct';
	}

	public function getAdapterPriority() {
		return 300;
	}

	public function isAllowed($resource, $role, $action) {

	    if ($resource == 'school' && in_array($action, ['browse', 'create'])) {

            if ($role instanceof User_Model_User){
                $role = $role->getIdentity();
            } if (!is_numeric($role) ) {
                return Authorization_Api_Core::LEVEL_IGNORE;
            }
            if($role < 1){
                return Authorization_Api_Core::LEVEL_IGNORE;
            }

            $db = $this->getAdapter();
            $sql = $db->select()
                ->from($this->_name, array('value'))
                ->where('resource_type = ?', $resource)
                ->where('resource_id = 0')
                ->where('role = ?', $role)
                ->where('action = ?', $action);

            $value = $db->fetchOne($sql);
            if($value !== false){
                return $value;
            } else {
                return Authorization_Api_Core::LEVEL_IGNORE;
            }
        }

		if (!($resource instanceof Core_Model_Item_Abstract)) {
			return Authorization_Api_Core::LEVEL_IGNORE;
		}
		
		if (!is_string($action)) {
			throw new Exception('action must be a string');
		}
				
		if ($role instanceof User_Model_User){
			$role = $role->getIdentity();
		} if (!is_numeric($role) ) {
			return Authorization_Api_Core::LEVEL_IGNORE;
		}
		if($role < 1){
			return Authorization_Api_Core::LEVEL_IGNORE;
		}
		
		$db = $this->getAdapter();
		$sql = $db->select()
			->from($this->_name, array('value'))
			->where('resource_type = ?', $resource->getType())
			->where('resource_id = ?', $resource->getIdentity())
			->where('role = ?', $role)
			->where('action = ?', $action);
		
		$value = $db->fetchOne($sql);
		if($value !== false){
			return $value;
		} else {
			return Authorization_Api_Core::LEVEL_IGNORE;
		}
		
	}

	public function setAllowedMultiple(Core_Model_Item_Abstract $resource, array $roles, $action) {
		if (!is_string($action)) {
			throw new Exception('action must be a string');
		}
		
		$this->getAdapter()->beginTransaction();		
		try {
			$this->delete(array(
						'resource_type = ?' => $resource->getType(),
						'resource_id = ?' => $resource->getIdentity(),
						'action = ?' => $action,
				));
			foreach ( $roles as $role => $value ) {				
				$value = $this->normilizeValue($value);				
				if($value != Authorization_Api_Core::LEVEL_IGNORE){
					$this->insert(array(
							'resource_type' => $resource->getType(),
							'resource_id' => $resource->getIdentity(),
							'action' => $action,
							'role' => $role,
							'value' => $value											
					));
				}			
			}
		}catch (Exception $e){
			$this->getAdapter()->rollBack();
			throw $e;
		}
		$this->getAdapter()->commit();
		return $this;
	}

	public function getAllowed($resource, $role, $action){
		return $this->isAllowed($resource, $role, $action);
	}
	
	public function setAllowed($resource, $role, $action, $value = null) {
		if (!$resource instanceof Core_Model_Item_Abstract ) {
			throw new Exception('resource must be a Core_Model_Item_Abstract');			
		}
		
		if (!is_string($action)) {
			throw new Exception('action must be a string');
		}
		
		if ($role instanceof User_Model_User){
			$role=$role->getIdentity();			
		} if (!is_numeric($role) ) {
			throw new Exception('role must be a User or User-id');
		}
		
		if($role < 1){
			throw new Exception('Only registered users allowed here');
		}

		$value = $this->normilizeValue($value);

		$db = $this->getAdapter();
		
		if($value == Authorization_Api_Core::LEVEL_IGNORE){

			$this->delete(array(
					'resource_type = ?' => $resource->getType(),
					'resource_id = ?' => $resource->getIdentity(),
					'action = ?' => $action,
					'role = ?' => $role,
			));
			
		} else {
			$sql = $db->select()
				->from($this->_name, array ('value'))
				->where('resource_type = ?', $resource->getType())
				->where('resource_id = ?', $resource->getIdentity())
				->where('action = ?', $action)
				->where('role = ?', $role);
			$current = $db->fetchOne($sql);
			
			if ($current === false) {
				$this->insert(array (
						'resource_type' => $resource->getType(),
						'resource_id' => $resource->getIdentity(),
						'action' => $action,
						'role' => $role,
						'value' => $value 
				));
			} else if ($current != $value) {
				$this->update(array (
						'value' => $value 
				), array (
						'resource_type = ?' => $resource->getType(),
						'resource_id = ?' => $resource->getIdentity(),
						'action = ?' => $action,
						'role = ?' => $role 
				));
			}
		}
		return $this;
	}
	
	
	public function normilizeValue($value) {
		if ($value == Authorization_Api_Core::LEVEL_ALLOW || 
				$value == Authorization_Api_Core::LEVEL_MODERATE || 
				$value === true) {
			return Authorization_Api_Core::LEVEL_MODERATE;
			
		} else if($value === Authorization_Api_Core::LEVEL_DISALLOW || $value === false){
			return Authorization_Api_Core::LEVEL_DISALLOW;
			
		} else {
			return Authorization_Api_Core::LEVEL_IGNORE;			
		} 
		
	}
	
}