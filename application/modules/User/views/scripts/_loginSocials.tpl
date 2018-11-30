<?
    $domSet = Engine_Api::_()->core()->getNowDomainSettings();
    $domainTo = $domSet['key'] == 'abitu' ? '' : $domSet['key'];
    $_SESSION['crossDomainSocialAuthDomain'] = $domainTo;
            
    $base_href = rtrim((constant('_ENGINE_SSL') ? 'https://' : 'http://') . ABITU_SITE . '/');
    $redirectBackPath =  '&backpath='. urlencode($_SERVER['REQUEST_URI']);
            
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $images_dir = '/application/modules/User/externals/images/user/';
    $vk_app_id = $settings->getSetting('core.vk.appid');

    $vk_redirect_uri = $base_href . 'simple_api/social_auth.php?method=vk_auth';
    $mipt_redirect_uri = $base_href . 'simple_api/social_auth.php?method=mipt_auth';
    /*NOTE: доступ к приложению гугла все проебали! А там надо http и https отдельно прописывать, не убирать протокол, иначе не бдет работать при https!*/
    $google_redirect_uri = 'http://'. ABITU_SITE . '/simple_api/social_auth.php?method=google_auth';
    $mailru_redirect_uri = $base_href . 'simple_api/social_auth.php?method=mailru_auth';
    $twitter_redirect_uri = $base_href . 'simple_api/social_auth.php?method=twitter_auth';
    $facebook_redirect_uri = $base_href . 'simple_api/social_auth.php?method=facebook_auth';
    $odnoklassniki_redirect_uri = $base_href . 'simple_api/social_auth.php?method=odnoklassniki_auth';

    $vk_display = $settings->getSetting('core.vk.display'); // page, popup или mobile
    $vk_api_version = $settings->getSetting('core.vk.version'); //Версия API
    $vk_permissions = $settings->getSetting('core.vk.permissions'); //Подробный список разрешений тут: https://vk.com/dev/permissions        
    $vk_response_type = 'code';

    $mipt_client_id = $settings->getSetting('core.mipt.appid');
    $ya_client_id = $settings->getSetting('core.ya.clientid');
    $google_client_id = $settings->getSetting('core.google.clientid');
    $mailru_client_id = $settings->getSetting('core.mailru.clientid');
    $twitter_client_id = $settings->getSetting('core.twitter.clientid');
    $facebook_client_id = $settings->getSetting('core.facebook.key');
    
    $mipt_login_image = 'http://mipt.ru/images/logo_auth_en.png';
?>
<div>
    <div class="social_signup bl-social-auth__item">
        <a onclick="globalSocialAuth()" title="<?=$this->translate('Log in with your VK profile')?>" target="_top" href="https://oauth.vk.com/authorize?client_id=<?=$vk_app_id?>&scope=<?=$vk_permissions?>&redirect_uri=<?=urlencode($vk_redirect_uri)?>&display=<?=$vk_display?>&v=<?=$vk_api_version?>&response_type=<?=$vk_response_type?>" class="bl-social-auth__btn">
            <i class="bl-social-auth__icon icon-vk"></i>
        </a>
    </div>
    <div onclick="globalSocialAuth()" class="social_signup bl-social-auth__item">
        <a title="<?=$this->translate('Log in with your Facebook profile')?>" target="_top" href="https://www.facebook.com/v2.8/dialog/oauth?client_id=<?=$facebook_client_id?>&redirect_uri=<?=urlencode($facebook_redirect_uri)?>">
            <i class="bl-social-auth__icon icon-facebook"></i>
        </a>
    </div>
    <div class="social_signup bl-social-auth__item">
        <a target="_top" href="http://mipt.ru/oauth/authorize.php?response_type=code&client_id=<?=$mipt_client_id?>&state=xyz&scope=userinfo%20email&redirect_uri=<?=urlencode($mipt_redirect_uri)?>">
            <i class="bl-social-auth__icon icon-mfti-mail"></i>
        </a>
    </div>
    <div class="social_signup bl-social-auth__item">
        <a onclick="globalSocialAuth()" target="_top" href="https://oauth.yandex.ru/authorize?response_type=code&client_id=<?=$ya_client_id?>">
            <i class="bl-social-auth__icon icon-ya"></i>
        </a>
    </div>
    <div onclick="globalSocialAuth()" class="social_signup bl-social-auth__item">
        <a title="<?=$this->translate('Log in with your Google profile')?>" target="_top" href="https://accounts.google.com/o/oauth2/auth?redirect_uri=<?=urlencode($google_redirect_uri)?>&response_type=code&client_id=<?=$google_client_id?>&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile">
            <i class="bl-social-auth__icon icon-google"></i>
        </a>
    </div>
    <div onclick="globalSocialAuth()" class="social_signup bl-social-auth__item">
        <a title="<?=$this->translate('Log in with your Mail.ru profile')?>" target="_top" href="https://connect.mail.ru/oauth/authorize?client_id=<?=$mailru_client_id?>&response_type=code&redirect_uri=<?=urlencode($mailru_redirect_uri)?>">
            <i class="bl-social-auth__icon icon-mailru"></i>
        </a>
    </div>
</div>