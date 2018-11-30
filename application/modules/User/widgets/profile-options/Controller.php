<?
class User_Widget_ProfileOptionsController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        // Don't render this if not authorized
        $viewer = Engine_Api::_()->user()->getViewer();
        if(!Engine_Api::_()->core()->hasSubject() || !$viewer->getIdentity())
        {
            return $this->setNoRender();
        }

        // Get subject and check auth
        $this->view->member = $subject = Engine_Api::_()->core()->getSubject('user');
        //if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
        //  return $this->setNoRender();
        //}

        $this->view->navigation = Engine_Api::_()
                ->getApi('menus', 'core')
                ->getNavigation('user_profile');
    }
}