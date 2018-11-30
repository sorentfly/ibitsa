<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Recipient.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Messages_Model_Recipient extends Engine_Db_Table_Row
{

	public function getLastViewedTS(){
		if(empty($this->last_viewed)) {
			return null;
		} else {
			return strtotime($this->last_viewed);
		} 
	}
	

	var $user = false;
    /**
     * @return User_Model_User
     */
	public function getUser(){
		if($this->user === false){
			$this->user = Engine_Api::_()->getItem('user', $this->user_id);
		} 
		return $this->user;
	}
	
}