<?php

class User_Widget_HomeLinksController extends Engine_Content_Widget_Abstract {

    public $tb_prefix;
    public $db;

    public function indexAction() {

        // Don't render this if not logged in
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$viewer->getIdentity()) {
            return $this->setNoRender();
        }

        $this->view->navigation = Engine_Api::_()
                ->getApi('menus', 'core')
                ->getNavigation('user_home');



        if ($viewer->getIdentity()) 
        {
            $this->view->notification_count = Engine_Api::_()->getDbtable('notifications', 'activity')->hasNotifications($viewer);

            $core_navigation = Engine_Api::_()
                    ->getApi('menus', 'core')
                    ->getNavigation('core_mini');
            $this->view->messages_count = 0;
            foreach ($core_navigation as $item) 
            {
                if ($item->_route === 'messages_general') 
                {
                    $message_label = explode(' ', $item->getLabel());
                    $count = isset($message_label[1]) ? $message_label[1] : 0;
                    $this->view->messages_count = trim($count, '()');
                }
            }
        }
    }

    public function getCacheKey() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $translate = Zend_Registry::get('Zend_Translate');
        return $viewer->getIdentity() . $translate->getLocale();
    }

}