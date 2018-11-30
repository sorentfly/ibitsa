<?

/**
 *
 * @category Application_Core
 * @package Messages
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Messages_MessagesController extends Core_Controller_Action_User
{
    protected $_form;

    const MAX_RECIPIENTS = 999;
    var $viewer = null;

    /**
     *
     */
    public function init(){
        $this->_helper->requireUser();
        $this->_helper->requireAuth()->setAuthParams('messages', null, 'create');
        $this->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        if ($viewer->getIdentity() && !Engine_Api::_()->core()->hasSubject()){
            Engine_Api::_()->core()->setSubject($viewer);
        }
        $this->isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

        $domainSets = Engine_Api::_()->core()->getNowDomainSettings();
        $this->view->hasAcademyMenu = isset($domainSets['pages']['user_profile_index']) && $domainSets['pages']['user_profile_index'] == 'zftsh_user_profile';

    }

    /**
     *
     */
    public function inboxAction() {
        /** @var Core_Model_DbTable_Settings $settingsApi */
        $settingsApi = Engine_Api::_()->getApi('settings', 'core');
        /* @var $conversationsDb Messages_Model_DbTable_Conversations */
        $conversationsDb = Engine_Api::_()->getItemTable('messages_conversation');

        $this->view->page = $page = $this->_getParam('page', 1);
        $itemsPerPage = min( max(( int ) $settingsApi->getSetting('dialogs.page', 20), 10) , 60);

        $this->view->filter = $filter = [
            'only_unreaded' => $this->getParam('only_unreaded', 0),
            'pupil_class' => $this->getParam('pupil_class', 'all'),
        ];

        $this->view->paginator = $paginator = $conversationsDb->getDialogsPaginator($this->viewer,$filter);


        if ($this->isAjax) {
            $paginator
                ->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage);
            $this->disab();
            if ($paginator->count() < $page) {
                echo 'stop';
                die();
            }
            $this->view->isAjax = true;
            $this->renderScript('messages/inbox.list.tpl');
        } else {
            $paginator
                ->setCurrentPageNumber(1)
                ->setItemCountPerPage($page * $itemsPerPage);
            $this->view->unreadCount = Engine_Api::_()->messages()
                ->getUnreadMessageCount($this->viewer);
            if (!$filter['only_unreaded'] && $filter['pupil_class'] == 'all'){
                $this->view->wholeCount = $paginator->getTotalItemCount();
            }else{
                $this->view->wholeCount = $conversationsDb->getAdapter()->fetchOne($conversationsDb->getInboxCountSelect($this->viewer));
            }

            if(in_array($this->viewer->academyStatus(), ['teacher_new', 'teacher_approved', 'methodist','admin'])){
                $this->view->classSelectOptions = $conversationsDb->getRecipientClassSelectOptions($this->viewer);
            }

            $this->_helper->content->setEnabled();
        }
    }


    /**
     *
     */
    public function outboxAction()
    {
        $this->_helper->redirector->gotoRoute(['action' => 'inbox']);
        /*
      $viewer = Engine_Api::_()->user()->getViewer();
      $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('messages_conversation')->getOutboxPaginator($viewer);
      $paginator->setCurrentPageNumber($this->_getParam('page'));
      $this->view->unread = Engine_Api::_()->messages()->getUnreadMessageCount($viewer);

      // Render
      $this->_helper->content
          //->setNoRender()
          ->setEnabled()
          ;
      */
    }

    /**
     * @throws Exception
     */
    public function viewAction() {
        // Get navigation
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('messages_main');

        $id = $this->_getParam('id');
        $startFrom = $this->_getParam('start');
        $viewer = Engine_Api::_()->user()->getViewer();

        // Get conversation info
        /* @var $conversation Messages_Model_Conversation */
        $this->view->conversation = $conversation = Engine_Api::_()->getItem('messages_conversation', $id);
        if(empty($conversation)){
            return $this->_helper->redirector->gotoRoute(array('action' => 'inbox'));
        }

        $this->view->recipient = $recipient = $conversation->getRecipientInfo($this->viewer);

        // Make sure the user is part of the conversation
        if (! $conversation || ! $conversation->hasRecipient($this->viewer)) {
            return $this->forward('inbox');
        }

        $greetingsUser = false;
        // Check for resource
        if (! empty($conversation->resource_type) && ! empty($conversation->resource_id)) {
            $resource = Engine_Api::_()->getItem($conversation->resource_type, $conversation->resource_id);
            if (! ($resource instanceof Core_Model_Item_Abstract)) {
                return $this->sforward('inbox');
            }
            $this->view->resource = $resource;
        } 		// Otherwise get recipients
        else {
            /* @var Messages_Model_Recipient[] $recipients */
            $this->view->recipients = $recipients = $conversation->getRecipientsInfo();
            $this->view->lastOnlineDate = $this->getRecipientsLastOnline($recipients);

            $blocked = false;
            $blocker = "";

            // This is to check if the viewered blocked a member
            $viewer_blocked = false;
            $viewer_blocker = "";
            foreach ( $recipients as $recipient ) {
                $user = $recipient->getUser();
                if (!$user || !$user->getIdentity()){
                    continue;
                }
                if ($this->viewer->isBlockedBy($user)) {
                    $blocked = true;
                    $blocker = $recipient;
                } elseif ($user->isBlockedBy($viewer)) {
                    $viewer_blocked = true;
                    $viewer_blocker = $user;
                }
                if (!$user->isSelf($viewer)){
                    $greetingsUser = $greetingsUser === false ? $user : null;
                }
            }

            $this->view->blocked = $blocked;
            $this->view->blocker = $blocker;
            $this->view->viewer_blocked = $viewer_blocked;
            $this->view->viewer_blocker = $viewer_blocker;
        }

        // Can we reply?
        $this->view->locked = $conversation->locked;
        if (!$conversation->locked) {
            // Process form
            $this->view->form = $form = new Messages_Form_Reply();
            if ($greetingsUser){
                $greetingsName = $greetingsUser->hasMethodistRights() ? $greetingsUser->first_name . ' ' . $greetingsUser->middle_name : $greetingsUser->first_name;
                $form->body->setValue('Здравствуйте, ' . $greetingsName.'!');
            }
            if ($this->getRequest()
                    ->isPost() && $form->isValid($this->getRequest()
                    ->getPost())) {
                $db = Engine_Api::_()->getDbtable('messages', 'messages')
                    ->getAdapter();
                $db->beginTransaction();
                try {
                    // Try attachment getting stuff
                    $attachment = null;
                    $attachmentData = $this->getRequest()
                        ->getParam('attachment');
                    if (! empty($attachmentData) && ! empty($attachmentData ['type'])) {
                        $type = $attachmentData ['type'];
                        $config = null;
                        foreach ( Zend_Registry::get('Engine_Manifest') as $data ) {
                            if (! empty($data ['composer'] [$type])) {
                                $config = $data ['composer'] [$type];
                            }
                        }
                        if ($config) {
                            $plugin = Engine_Api::_()->loadClass($config ['plugin']);
                            $method = 'onAttach' . ucfirst($type);
                            $attachment = $plugin->$method($attachmentData);

                            $parent = $attachment->getParent();
                            if ($parent->getType() === 'user') {
                                $attachment->search = 0;
                                $attachment->save();
                            } else {
                                $parent->search = 0;
                                $parent->save();
                            }
                        }
                    }

                    $values = $form->getValues();
                    $values ['conversation'] = ( int ) $id;

                    /* @var Messages_Model_Message $message */
                    $message = $conversation->reply($this->viewer, $values ['body'], $attachment);

                    // Send email notifications
                    $userIds = [];
                    foreach ( $recipients as $recipient ) {
                        if ($recipient->user_id != $this->viewer->getIdentity()){
                            $userIds[] = $recipient->user_id;
                        }
                    }
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($userIds, 'notify_message_new', [
                        'host' => $_SERVER['HTTP_HOST'],
                        'sender_title' => $this->viewer->getTitle(),
                        'object_description' => $message->body,
                        'object_link'  => $message->getHref()
                    ]);

                    // Increment messages counter
                    Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');

                    $db->commit();
                } catch ( Exception $e ) {
                    $db->rollBack();
                    throw $e;
                }

                $form->populate([
                    'body' => ''
                ]);
                $this->_helper->redirector->gotoRoute([
                    'action' => 'view',
                    'id' => $id
                ]);
                return;
            }
        }

        // Make sure to load the messages after posting :P
        /* @var $messages Engine_Db_Table_Rowset */
        $this->view->messages = $messages = $conversation->getMessages($this->viewer,$startFrom);
        $this->view->minMessageId = $minId = count($messages) ? $messages[0]->message_id : 9999999999;
        $this->view->messageLeftCount = $conversation->getMessageLeftCount($minId);
        $conversation->setAsRead($this->viewer);

        if ($this->isAjax) {
            $this->disab();
            $this->renderScript('messages/view.list.tpl');
        } else {
            $this->_helper->content->setEnabled();
        }
    }

    /**
     *
     */
    public function userToUserAction()
    {
        $userId = (int)$this->getParam('user_id', null);
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$userId || !$viewer->getIdentity()){
            return $this->forward('requireauth', 'error', 'core');

        }
        $conv = Engine_Api::_()->getItemTable('messages_conversation')->getUserToUserConversation($viewer, $userId);
        if ($conv){
            $this->forward('view',null,null,array('id' => $conv->getIdentity()));
        }else{
            $this->forward('compose',null,null,array('to' => $userId));
        }
    }

    /**
     * @throws Zend_Controller_Action_Exception
     */
    public function userListEditAction(){
        $id = $this->_getParam('id');
        $addUserId = $this->_getParam('add_user_id');
        $removeUserId = $this->_getParam('remove_user_id');

        /*@var $conversation Messages_Model_Conversation */
        $this->view->conversation = $conversation = Engine_Api::_()->getItem('messages_conversation', $id);

        if(!empty($addUserId)){
            $conversation->addRecipient($addUserId);
            $this->view->recipient = $conversation->getRecipient($addUserId);
            $this->disab();
            $this->renderScript('messages/user-list-edit.list.tpl');

        } else if(!empty($removeUserId)){
            $conversation->removeRecipient($removeUserId);
            $this->view->recipient = $conversation->getRecipient($removeUserId);
            $this->disab();
            $this->renderScript('messages/user-list-edit.list.tpl');

        } else {
            if(empty($conversation)){
                throw new Zend_Controller_Action_Exception(sprintf('Conversation id=%s does not exist', $id), 404);
            }
            $this->view->recipients = $recipients = $conversation->getRecipientsInfo();
            $this->view->maxRecipients = $maxRecipients = self::MAX_RECIPIENTS;
        }
    }

    /**
     * @param $recipients
     * @return false|int
     */
    public function getRecipientsLastOnline($recipients){
        $recipientsLastOnline = 0;
        /* @var $recipient Messages_Model_Recipient */
        foreach ($recipients as $recipient){
            $user = $recipient->getUser();
            if(count($recipients) > 1 && ($user->user_id == $this->viewer->getIdentity() || empty($user->online_date)) ){
                continue;
            }
            $onlineDate = strtotime($user->online_date);

            if($onlineDate > $recipientsLastOnline){
                $recipientsLastOnline = $onlineDate;
            }
        }
        return $recipientsLastOnline;
    }

    /**
     *
     */
    public function setViewedAction() {
        $id = $this->_getParam('id');
        $conversation = Engine_Api::_()->getItem('messages_conversation', $id);
        if(!empty($conversation)){
            $recipient = $conversation->getRecipientInfo($this->viewer);
            if(!empty($recipient)){
                $recipient->last_viewed = date('Y-m-d H:i:s');
                $recipient->save();
            }
        }
        echo 'OK';
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     *
     */
    public function messageDeleteAction(){
        $id = $this->_getParam('id');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        /* @var $message  Messages_Model_Message */
        $message = Engine_Api::_()->getItem('messages_message', $id);
        if(empty($message) || $message->user_id != $this->viewer->getIdentity()) {
            echo "Ошибка, сообщение не найдено"; return;
        };

        $conversation = Engine_Api::_()->getItem('messages_conversation', $message->conversation_id);
        if(empty($conversation))  {
            echo "Ошибка, разговор не найден"; return;
        };

        $me = $conversation->getRecipientInfo($this->viewer);
        if(empty($me)) {
            echo "Ошибка, нет доступа к разговору"; return;
        }
        if($message->canBeDeletedBy($me)){
            $message->deleted = date('Y-m-d H:i:s');
            $message->save();
            echo 'OK';
        } else {
            echo "Удаление невозможно, сообщение уже прочитано";
        }
    }


    public function getMessagesMainMenu() {
        return Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('messages_main');
    }


    public function composeAction()
    {
        $this->view->isSmoothbox = 'smoothbox' === $this->_helper->contextSwitch->getCurrentContext();

        // Get navigation
        $this->view->navigation = $this->getMessagesMainMenu();

        $personally = (bool) $this->_getParam('personally');

        // Make form
        $this->view->form = $form = new Messages_Form_Compose(['personally' => $personally]);

        // Prepopulate from GET
        $this->view->to = $to = $this->_getParam('to');
        $send_to = $this->_getParam('send_to');
        $multi = $this->_getParam('multi');
        $form->prepopulate($to, $multi);

        $this->view->toObject = $toObject = $form->getToObject();
        $this->view->isPopulated = $isPopulated = $form->getIsPopulated();

        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->receivers_limit = $viewer->hasMethodistRights() ? 0 : 30;


        // Assign the composing stuff
        /*$composePartials = array ();
        foreach ( Zend_Registry::get('Engine_Manifest') as $data ) {
            if (empty($data ['composer']))
                continue;
            foreach ( $data ['composer'] as $type => $config ) {
                $composePartials [] = $config ['script'];
            }
        }
        $this->view->composePartials = $composePartials;*/
        if ((Engine_Api::_()->core()->getNowDomainSettings()['key'] == 'zftsh') && ($toObject instanceof User_Model_User)){
            if ($contactInfo = $toObject->getZftshMemberData('contact_information')){
                $form->contact_information->setContent($contactInfo)
                    ->getDecorator('HtmlTag2')->setOption('class', 'form-wrapper') /*show a decorator*/;
            }
        }
        // Get config
        $this->view->maxRecipients = $maxRecipients = self::MAX_RECIPIENTS;

        // Check method/data
        if (! $this->getRequest()
            ->isPost()) {
            return;
        }

        if (! $form->isValid($this->getRequest()
            ->getPost())) {
            return;
        }

        // Process
        $db = Engine_Api::_()->getDbtable('messages', 'messages')
            ->getAdapter();
        $db->beginTransaction();
        try {
            // Try attachment getting stuff
            $attachment = $this->processAttachments();


            $viewer = Engine_Api::_()->user()
                ->getViewer();
            $values = $form->getValues();

            // Prepopulated
            $isItemDistribution = false;
            if ($toObject instanceof User_Model_User) {
                $recipientsUsers = array (
                    $toObject
                );
                $recipients = $toObject;
                // Validate friends
                if (false
                    /*NOTE - правка, сообщения можно всем отправлять*/
                    && 'friends' == Engine_Api::_()->authorization()
                        ->getPermission($viewer, 'messages', 'auth')) {
                    if (! $viewer->membership()
                        ->isMember($recipients)) {
                        return $form->addError('One of the members specified is not in your friends list.');
                    }
                }
            } else if ($toObject instanceof Core_Model_Item_Abstract && method_exists($toObject, 'membership')) {
                $recipientsUsers = $toObject->membership()
                    ->getMembers();
                $isItemDistribution = true;
                $recipients = $toObject;
            } 			// Normal
            else {
                $recipients = preg_split('/[,. ]+/', $values ['toValues']);
                // clean the recipients for repeating ids
                // this can happen if recipient is selected and then a friend list is selected
                $recipients = array_unique($recipients);
                // Slice down to 10
                $recipients = array_slice($recipients, 0, $maxRecipients);
                // Get user objects
                $recipientsUsers = Engine_Api::_()->getItemMulti('user', $recipients);
                // Validate friends
                if (false
                    /*NOTE - правка, сообщения можно всем отправлять*/
                    && 'friends' == Engine_Api::_()->authorization()
                        ->getPermission($viewer, 'messages', 'auth')) {
                    foreach ( $recipientsUsers as &$recipientUser ) {
                        if (! $viewer->membership()
                            ->isMember($recipientUser)) {
                            return $form->addError('One of the members specified is not in your friends list.');
                        }
                    }
                }
            }

            if ( !empty($send_to) && is_array($send_to) ) {
                $recipients = $send_to;
            }
            // Create conversation
            /* @var $conversationDb Messages_Model_DbTable_Conversations */
            $conversationDb = Engine_Api::_()->getItemTable('messages_conversation');
            $conversations = $conversationDb->send($viewer, $recipients, empty($values ['title']) ? '' : $values ['title'], $values ['body'], $attachment, $personally);

            // Send email notifications
            if ( $personally ) {
                foreach ( $conversations as $user_id => $conversation ) {
                    $user = Engine_Api::_()->getItem('user', $user_id);
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem(array($user), 'notify_message_new', [
                        'host' => $_SERVER['HTTP_HOST'],
                        'sender_title' => $viewer->getTitle(),
                        'object_description' => $values['body'],
                        'object_link' => $conversation->getHref()
                    ]);
                }
            } else {
                $conversation = $conversations;
                $users = [];
                foreach ($recipientsUsers as $user) {
                    if ($user->getIdentity() != $viewer->getIdentity()) {
                        $users[] = $user;
                    }
                }
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($users, 'notify_message_new', [
                    'host' => $_SERVER['HTTP_HOST'],
                    'sender_title' => $viewer->getTitle(),
                    'object_description' => $values['body'],
                    'object_link' => $conversation->getHref()
                ]);
            }

            // Increment messages counter
            Engine_Api::_()->getDbtable('statistics', 'core')
                ->increment('messages.creations');

            // Commit
            $db->commit();
        } catch ( Exception $e ) {
            $db->rollBack();
            throw $e;
        }

        //$this->view->isSmoothbox
        $succOpts = array (
            'messages' => array (
                Zend_Registry::get('Zend_Translate')->_('Your message has been sent successfully.')
            ),

        );

        if ($this->view->isSmoothbox){
            $succOpts['parentRedirect'] = $conversation->getHref();
        }else{
            $succOpts['redirect'] = $conversation->getHref();
        }
        return $this->forward('success', 'utility', 'core', $succOpts);
        // $this->getFrontController()->getRouter()->assemble(array('action' => 'inbox'));

    } // composeAction

    /**
     * @return null
     */
    private function processAttachments(){
        $attachment = null;
        $attachmentData = $this->getRequest()
            ->getParam('attachment');
        if (! empty($attachmentData) && ! empty($attachmentData ['type'])) {
            $type = $attachmentData ['type'];
            $config = null;
            foreach ( Zend_Registry::get('Engine_Manifest') as $data ) {
                if (! empty($data ['composer'] [$type])) {
                    $config = $data ['composer'] [$type];
                }
            }
            if ($config) {
                $plugin = Engine_Api::_()->loadClass($config ['plugin']);
                $method = 'onAttach' . ucfirst($type);
                $attachment = $plugin->$method($attachmentData);
                $parent = $attachment->getParent();
                if ($parent->getType() === 'user') {
                    $attachment->search = 0;
                    $attachment->save();
                } else {
                    $parent->search = 0;
                    $parent->save();
                }
            }
        }
        return $attachment;
    }

    /**
     *
     */
    public function successAction()
    {

    }

    /**
     * @return void
     * @throws Exception
     */
    public function deleteAction()
    {
        if( !$this->_helper->requireUser()->isValid() ) return;

        $message_ids = $this->view->message_ids = $this->getRequest()->getParam('message_ids');
        $messages = explode(',', $message_ids);

        $place = $this->view->place = $this->getRequest()->getParam('place');

        if (!$this->getRequest()->isPost())
            return;

        // In smoothbox
        $this->_helper->layout->setLayout('default-simple');

        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $this->view->deleted_conversation_ids = array();

        $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
        $db->beginTransaction();
        try {
            foreach ($messages as $message_id) {
                $recipients = Engine_Api::_()->getItem('messages_conversation', $message_id)->getRecipientsInfo();
                //$recipients = Engine_Api::_()->getApi('core', 'messages')->getConversationRecipientsInfo($message_id);
                foreach ($recipients as $r) {
                    if ($viewer_id == $r->user_id) {
                        $this->view->deleted_conversation_ids[] = $r->conversation_id;
                        $r->inbox_deleted  = true;
                        $r->outbox_deleted = true;
                        $r->save();
                    }
                }
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }

        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected messages have been deleted.');

        if ($place != 'view') {
            return $this->forward('success' ,'utility', 'core', array(
                'smoothboxClose' => true,
                'format'=> 'smoothbox',
                'parentRefresh' => true,
                'messages' => Array($this->view->message)
            ));
        }
        else {

            return $this->forward('success' ,'utility', 'core', array(
                'smoothboxClose' => true,
                'format'=> 'smoothbox',
                'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'inbox')),
                'messages' => Array($this->view->message)
            ));
        }
    }
}