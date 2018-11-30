<?

class User_Widget_LoginOrSignupController extends Engine_Content_Widget_Abstract
{

    public function indexAction()
    {
        // Do not show if logged in
        $this->view->authorized = false;
        $this->view->noSessionTransfer = false;
        if (Engine_Api::_()->user()->getViewer()->getIdentity())
        {
            $this->view->authorized = true;/* if true - render iframe for cross-domain auth change only*/
            $timeOfLogin = isset($_COOKIE["lastLoginTime"]) ? (int)$_COOKIE["lastLoginTime"] : 0;
            $timeOfCrossdomaiUpdate = isset($_COOKIE["lastCrossdomaiUpdateTime"]) ? (int)$_COOKIE["lastCrossdomaiUpdateTime"] : 0;
            if ( (time() - $timeOfLogin <= 40 || time() - $timeOfCrossdomaiUpdate >= 1800) && $_SERVER['HTTP_HOST'] != ABITU_SITE)
            {//able to PHP ses id cookie on central domain only during 20 secs
                 setcookie('lastCrossdomaiUpdateTime', time(), 0, '/');
            }else{
                $this->view->noSessionTransfer = true;
                return;
            }
        }
        $domain = Engine_Api::_()->core()->getNowDomainSettings();
        $this->view->enableSocials = empty($domain['login_socials_disabled']);
        $this->getElement()->removeDecorator('title')->removeDecorator('container');
    }

    public function getCacheKey()
    {
        return false;
    }

}
