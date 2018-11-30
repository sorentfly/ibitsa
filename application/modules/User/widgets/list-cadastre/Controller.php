<?

class User_Widget_ListCadastreController extends Engine_Content_Widget_Abstract
{
    public $portfolio;
    public function indexAction()
    {
        //ВРЕМЕННО: убираем виджет кадастра из профиля для ЗФТШ
        $domainSettings = Engine_Api::_()->core()->getNowDomainSettings();
        if ( !empty($domainSettings['zftshDefaults']) ) {
            return $this->setNoRender();
        }
        //
        $user = Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : null;
        $viewer = $this->view->viewer = Engine_Api::_()->user()->getViewer();
        $user  = $this->view->subject = $user && $user->getType() == 'user' ? $user : $viewer;
        $this->getElement()->removeDecorator('Title');
        if (!$user){
            return $this->setNoRender();
        }
        
        if ($viewer->getIdentity()){
            $this->view->viewer = $viewer;
            $is_admin = $viewer->level_id == 1 || $viewer->level_id == 2 || $viewer->level_id == 3;
            $is_self = $user->user_id === $viewer->user_id;
            $pointViewAble = $is_self || $is_admin || /*$isFriend*/ $user->membership()->isMember($viewer, true);
        }else{
            $pointViewAble = false;
        }
        
        $this->portfolio = new Olympic_Model_DbTable_Portfolios();
        $this->portfolio->govnokodInit($user);

        $cadastreData = $this->portfolio->getCadastreShort();
        $this->view->pointViewAble = $pointViewAble;
        $this->view->cadastre = $cadastreData; /* Баллы и (опционально) медаль кадастра */
    }
}