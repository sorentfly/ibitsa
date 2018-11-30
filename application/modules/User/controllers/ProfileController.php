<?
class User_ProfileController extends Core_Controller_Action_Standard
{
    public function init()
    {
        // @todo this may not work with some of the content stuff in here, double-check
        $subject = null;
        if(!Engine_Api::_()->core()->hasSubject())
        {
            $id = $this->_getParam('id');

            if($id == null)
            {
                $id = 1;
                //$id = substr($this->_getParam('uid'), 2);
            }

            // use viewer ID if not specified
            //if( is_null($id) )
            //  $id = Engine_Api::_()->user()->getViewer()->getIdentity();

            if(null !== $id)
            {
                $subject = Engine_Api::_()->user()->getUser($id);
                if($subject->getIdentity())
                {
                    Engine_Api::_()->core()->setSubject($subject);
                }
            }
        }
        
        if (($layout = $this->_getParam('layout')) && $layout == 'ajax'){
            $this->_helper->layout->setLayout('ajax');
        }

        $this->_helper->requireSubject('user');
        $this->_helper->requireAuth()->setNoForward()->setAuthParams(
                $subject, Engine_Api::_()->user()->getViewer(), 'view'
        );
    }

    public function indexAction()
    {
        /* @var User_Model_User $subject */
        $subject = Engine_Api::_()->core()->getSubject(); /* Просматриваемый профиль */
        /* @var User_Model_User $viewer */
        $viewer = Engine_Api::_()->user()->getViewer(); /* Человек, который просматривает */

        $DS = Engine_Api::_()->core()->getNowDomainSettings();
        if (!empty($DS['academyEnabled']) && $subject->academyStatus() != 'none'){
            Zend_Registry::set('overloadProfilePageToZftsh', true);
            $this->_helper->content->setContentName('zftsh_user_profile');
        }
        // check public settings
        $require_check = Engine_Api::_()->getApi('settings', 'core')->core_general_profile;
        if(!$require_check && !$this->_helper->requireUser()->isValid())
        {
            return;
        }

        if (!$viewer->getIdentity()){
            //Task #463 - http://nat.dc.phystech.edu:3022/redmine/issues/463
            return $this->forward('requireauth', 'error', 'core');
        }
        // Check enabled
        if(!$subject->enabled && !$viewer->isAdmin())
        {
            return $this->forward('requireauth', 'error', 'core');
        }

        // Check block
        if($viewer->isBlockedBy($subject) && !$viewer->isAdmin())
        {
            return $this->forward('requireauth', 'error', 'core');
        }

        $this->view->headMeta()->appendProperty('og:type', 'profile');
        $this->view->headMeta()->appendProperty('profile:first_name', $subject->first_name);
        $this->view->headMeta()->appendProperty('profile:last_name', $subject->last_name);
        $this->view->headMeta()->appendProperty('profile:gender', $subject->gender == 2 ? 'male' : 'female');

        // Increment view count
        if(!$subject->isSelf($viewer))
        {
            $subject->view_count++;
            $subject->save();
        }
        
        // Render
        $this->_helper->content->setNoRender()->setEnabled();
    }
}