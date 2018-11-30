<?php
class User_AjaxController extends Core_Controller_Action_Standard {

	public function suggestAction() {
		// Requires user
		if (! $this->_helper->requireUser()
			->isValid())
			return;
			
			// Get params
		$text = $this->_getParam('text', $this->_getParam('search', $this->_getParam('value')));
		$limit = ( int ) $this->_getParam('limit', 10);
		$offset = ( int ) $this->_getParam('offset', 0);
		$friends = ( bool ) $this->_getParam('friends', false);
        $subject = $friends ? Engine_Api::_()->user()->getViewer() : Engine_Api::_()->getItemByGuid($this->_getParam('subject', null));
		$usersDb = Engine_Api::_()->getItemTable('user');
		// Generate query
		if ($subject && method_exists($subject, 'membership')) {
            // Friends only
            $select = $subject->membership()
                ->getMembersObjectSelect();
            if (null !== $text) {
                $select->where('`' . $usersDb->info('name') . '`.`displayname` LIKE ?', '%' . $text . '%');
            }
            $select->limit($limit, $offset);
            $users = $usersDb->fetchAll($select);
        } else {
			// Searchable users only
                        $select = $usersDb->getSearchIdsSelect($text)
                                ->limit($limit, $offset);
                        $users= $usersDb->getAdapter()->fetchCol($select);
		}
		
		
		
		

		// Retv data
		$data = array ();
		
		/* @var $user User_Model_User */
		foreach ( $users as $user ) {
                        if (!is_object($user)){
                            $user = Engine_Api::_()->getItem('user', $user);
                        }
			$data [] = array (
					'type' => 'user',
					'id' => $user->getIdentity(),
					'guid' => $user->getGuid(),
					'label' => $user->getFIO(), // We should recode this to use title instead of label
					'title' => $user->getFIO(),
					'photo' => $this->view->itemPhoto($user, 'thumb.icon'),
					'url' => $user->getHref() 
			);
		}
		
		// send data
		if ($this->_getParam('sendNow', true)) {
			return $this->_helper->json($data);
		} else {
			$this->_helper->viewRenderer->setNoRender(true);
			$data = Zend_Json::encode($data);
			$this->getResponse()
				->setBody($data);
		}
	}
}