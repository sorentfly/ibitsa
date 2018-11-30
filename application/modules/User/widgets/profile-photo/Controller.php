<?
class User_Widget_ProfilePhotoController extends Engine_Content_Widget_Abstract
{

    public function indexAction()
    {
        if (!Engine_Api::_()->user()->getViewer()->getIdentity()){
            //Task #463 - http://nat.dc.phystech.edu:3022/redmine/issues/463
            return $this->setNoRender();
        }
        $subject = Engine_Api::_()->core()->getSubject('user');
        $this->view->user = $subject;
    }

}
