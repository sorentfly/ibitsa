<? 
/*able to PHP ses id cookie on central domain only during 20 secs*/
if ($this->authorized){ 
    if (!empty($_COOKIE['autojoin']) && !count($this->viewer()->getUnfilledRequiredFields()) ){
        include __DIR__ . '/_autojoin.tpl';
    }
    if ($this->noSessionTransfer) return;
    ?>
    <iframe src="https://<?=bitsa_SITE?>/?crossdomainCookiesAuth=<?=session_id()?>" width="0" height="0" style="position:absolute;">
    </iframe>
<? 
return;
}?>
    
<link href="/externals/jReject-master/css/jquery.reject.css" media="screen" rel="stylesheet" />
<script src="/externals/jReject-master/js/jquery.reject.js"></script>
<script src="/externals/jquery-hideShowPassword/hideShowPassword.min.js"></script>

<? if ($_SERVER['HTTP_HOST'] != bitsa_SITE){ /*very fast cross-domain request - 1ms*/ ?>
<iframe src="https://<?=bitsa_SITE?>/?crossdomainCookiesAuth=0" width="0" height="0" style="position:absolute;">
</iframe>
<?php } ?>

<? /* ok */ ?>
<script type="text/javascript">
    var tst = document.createElement("div");
    var flexSupportCaps = [
        tst.style.flex, 
        tst.style.flexWrap, 
        tst.style.flexFlow,
        tst.style.flexGrow,
        tst.style.alignContent, 
        tst.style.alignItems, 
        tst.style.alignSelf, 
        tst.style.justifyContent, 
        tst.style.order
    ];
    var isie_less_edge = navigator.userAgent.match(/Trident.*rv\:11\./) || navigator.userAgent.indexOf("MSIE ") > 0;
    if (flexSupportCaps.indexOf(undefined)!=-1 || isie_less_edge){
        jQuery(document).ready(function(){
            jQuery.reject({
                reject: { all: true }, 
                header: 'Браузер не поддерживается', 
                paragraph1: 'Вы используете устаревший браузер.',
                paragraph2: 'Для корректной работы портала <?=$_SERVER['HTTP_HOST']?> рекомендуем установить один из следущих браузеров, или обновить ваш до последней версии.',
                closeLink: 'Закрыть окно',  
                closeMessage: 'Я согласен с тем, что отображение сайта может быть некорректно.',
                imagePath: '/externals/jReject-master/images/',  
                closeCookie: true,
                browserInfo: {
                    msie: {
                        text: 'Microsoft Edge',  
                        url: 'https://www.microsoft.com/ru-ru/windows/microsoft-edge'  
                    }
                }
            }); 
        });
    }
    delete tst;
</script>
<script type="text/javascript">
    var crossdomainCookiesAuth =  function(event) {
        if (window.crossdomainAuthProcess != undefined) return;
        var getCookie = function(name) {
            var value = "; " + document.cookie;
            var parts = value.split("; " + name + "=");
            if (parts.length == 2) return parts.pop().split(";").shift();
        };
        var message  =  event.originalEvent.data;
        if (typeof message == 'object' && typeof message.setPhpSesId != 'undefined' && navigator.cookieEnabled && document.cookie.split('PHPSESSID=').length <= 2){
            var PHPses = getCookie('PHPSESSID');
            if (PHPses != message.setPhpSesId){
                document.cookie = 'PHPSESSID='+message.setPhpSesId+'; path=/';/*add/rewrite phpses cookie*/
                /*additional check crossdomainLoginReload before reload - to prevent infinity reload BAD bug*/
                var timeOfLastReload = getCookie('crossdomainLoginReload');
                if (!timeOfLastReload || (new Date()).valueOf() -  parseInt(timeOfLastReload) > 60000 ){
                    document.cookie = 'crossdomainLoginReload='+(new Date()).valueOf()+'; path=/';/*add/rewrite last login reload cookie*/
                    window.crossdomainAuthProcess = true;
                    document.location.reload();
                }
            }
        }
    };
    jQuery(window).on("message", crossdomainCookiesAuth);

    var globalLoginFormOpen = function(){
        var preventSSL = <?=defined('PREVENT_SSL') && PREVENT_SSL ? 'true' : 'false'?>;
        if (!preventSSL && window.location.protocol != 'https:'){
            window.location.href = 'https://' + window.location.host + window.location.pathname + (window.location.search ? window.location.search + '&login=1' : '?login=1' );
            return;
        }
        Smoothbox.open($$('.loginModal')[0].outerHTML);
        if (typeof window.onFormInitialize === 'function') window.onFormInitialize();
        HidePasswordOptionsStandart = window.HidePasswordOptionsStandart ? window.HidePasswordOptionsStandart : parent.HidePasswordOptionsStandart;
        jQuery('#TB_ajaxContent input[name=password]').hidePassword(HidePasswordOptionsStandart);/* plugin-info: https://github.com/cloudfour/hideShowPassword */
        <? /*LANDSCAPE orientation fixes*/ ?>
        jQuery('#TB_ajaxContent input').on("focus", function () {
            if (jQuery(window).innerHeight() < 400){
                jQuery('#TB_ajaxContent').find('.obj-login-modal__header,.obj-login-modal__content-social-login,.obj-login-modal__tip').hide();
                Smoothbox.instance.doAutoResize();
            }
        });
        jQuery('#TB_ajaxContent button[name=go]').click(function(event){
            event.stopPropagation();
            event.preventDefault();
            <? /*ajaxButton плагин делает это, но с stopPropagation он криво работает, посему - костылик*/ ?>
            var button = jQuery(this);
            if (button.hasClass('ajaxProcessing')){
                return;
            }
            button.addClass('ajaxProcessing');
            <? /*костылик конец*/ ?>
            var loginData = {};
            jQuery.each(jQuery('#TB_ajaxContent form').serializeArray(), function(_, kv) {
              loginData[kv.name] = kv.value;
            });
            loginData['page_subject'] = en4.core.subject.guid;
            jQuery.ajax({
                    "url" : (preventSSL? 'http://' : 'https://') + location.host + '/login?x=1',
                    "method": "POST",
                    "dataType": "html",
                    "data" : loginData,
                    withCredentials: true
              }).done(function(html) {
                respErrors = [];
                try{
                    var response = html;
                    html = jQuery( html.replace('<li>Email', '<li>').replace('<li>Пароль', '<li>') );
                    respErrors = html.find('.form-errors');
                }catch(e){}

                if(respErrors.length){
                    button.removeClass('ajaxProcessing');
                    jQuery('#TB_ajaxContent .for-errors-putting').html(respErrors[0].outerHTML);
                    Smoothbox.instance.doAutoResize();
                }else{
                    if (typeof window.onLoginSuccess == 'function') { if (window.onLoginSuccess(html)) return;}
                    var responseSplitted = response.split(' ');
                    var redirect = jQuery('#TB_ajaxContent input[name=return_url]').val();
                    if (responseSplitted.length > 2){
                        redirect = responseSplitted[2];
                    }
                    redirect = redirect.indexOf('/login')==0 ? '/' : redirect;
                    var sslRedirect = (responseSplitted[0] == 'moderator');
                    if (sslRedirect && !preventSSL){
                        if (redirect.indexOf('http:')==0){
                            redirect = redirect.replace('http:', 'https:');
                        }else{
                            redirect = 'https://'+ location.host +redirect;
                        }
                    }
                    document.cookie = 'PHPSESSID='+responseSplitted[1]+'; path=/';/*add/rewrite phpses cookie manualy, cause unknown jq bug http->https req*/
                    document.location.href = redirect.indexOf('/login')==0 ? '/' : redirect;
                }
              }).fail(function() {
                button.removeClass('ajaxProcessing');
                jQuery('#TB_ajaxContent .for-errors-putting').html('<span style="color:red">Ошибка сервера!</span>');
              });
      });
    };
    var globalRegisterOpen = function(event)
    {
        if (event) event.preventDefault();

        if (window.location.protocol != 'https:'){
            window.location.href = 'https://' + window.location.host + window.location.pathname + (window.location.search ? window.location.search + '&login=2' : '?login=2' );
            return;
        }

        if (jQuery(window).innerHeight() < 400){
            document.location.pathname = "/signup/";
            return;
        }
        if (document.location.pathname.indexOf('/signup/')!=0){
            document.cookie = 'registerWalkPath='+document.location.protocol+'//'+document.location.host+document.location.pathname+'; path=/';
        }
        if (Smoothbox.instance){
            Smoothbox.close();
        }
        Smoothbox.open('//'+document.location.hostname+ (document.location.port ? ':'+document.location.port : '') + '/signup/', {width: 400, noOverlayClose: true});
    };

    var globalSocialAuth = function()
    {
        document.cookie = 'socialWalkPath='+document.location.protocol+'//'+document.location.host+document.location.pathname+document.location.search+'; path=/';
    };
    
    jQuery(document).ready(function(){
      <? /*Всплывающее окно открывается автоматически только на спец-страницах и только если юзер незалогинен. В остальных случаях - по кнопке*/ ?>
      if ((document.location.pathname.indexOf('/login')==0
          ||  document.location.pathname.indexOf('/school_login')==0
          || (document.location.pathname.indexOf('/foreign_login')==0 && typeof window.onLoginSuccess == 'function')
          || window.location.search.includes('?login=1') || window.location.search.includes('&login=1')
    ) && !en4.user.viewer.id){
          setTimeout(globalLoginFormOpen, 300);
      }
      if (window.location.search.includes('?login=2') || window.location.search.includes('&login=2')){
          setTimeout(globalRegisterOpen, 300);
      }
      if (window.location.search.includes('login=')){
          window.history.replaceState(null,null,
              window.location.pathname + window.location.search.replace(/login=\d/, '').replace('&&', '&').replace('?&', '?').replace(/[&?]$/, '')
          );
      }
      if (typeof Smoothbox != 'undefined'){
        jQuery('.core_mini_auth').attr('href', 'javascript:void(0);').click(globalLoginFormOpen);
      }else{
        jQuery('.core_mini_auth').attr('href', '/login');
      }
    });
</script>
<? $isZftsh = (Engine_Api::_()->core()->getNowDomainSettings()['key'] == 'zftsh'); ?>
<div  style="display:none;">
    <div class="loginModal modal" style="min-width: 365px;">
        <div class="obj-login-modal">
            <div class="obj-login-modal__header">
                <div class="obj-login-modal__flex-container">
                    <div class="obj-login-modal__flex-item mod-sidebar">

                    </div>
                    <div class="obj-login-modal__flex-item">
                        <img src="/public/admin/bitsa_logo_main.png" alt="" class="obj-login-modal__header-img-logo">
                    </div>
                    <div class="obj-login-modal__flex-item mod-sidebar">
                        <a href="javascript:void(0)" onclick="Smoothbox.close();" class="obj-login-modal__header-exit-btn"></a>
                    </div>
                </div>
            </div>
            <form enctype="application/x-www-form-urlencoded" class="global_form_box login-form" action="/login?x=1" method="post">
                <div class="obj-login-modal__content">
                    <?php if ($this->enableSocials){ ?>
                    <div class="obj-login-modal__content-social-login">
                        <div class="obj-login-modal__content-social-login-container">
                            <div class="obj-login-modal__content-social-login-title"><?=$this->translate('Log in with')?></div>
                            <?=$this->partial('_loginSocials.tpl','user')?>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="obj-login-modal__tip login_with_tip">
                        <? if (!empty($_COOKIE['next_login_location']) && $_COOKIE['next_login_location']){
                            $label = '';
                            $exp = explode('_', $_COOKIE['next_login_location']);
                            if (count($exp) > 1 && ($entity = Engine_Api::_()->getItem('school_entity', $exp[1]))){
                                $label = $this->translate('Login into the %1$s cabinet', $entity->__toString());
                            }else{
                                $label = $this->translate('Login into the school cabinet');
                            }
                        }else{
                            $label = $this->translate('Login with bitsaNet account');
                        } ?>
                        <?=$label?>
                    </div>
                    <div class="for-errors-putting">
                    </div>
                    <div class="obj-login-modal__content-fields-container">
                        <div class="obj-login-modal__content-fields-block">
                            <i class="obj-login-modal__content-field-icon user"></i>
                            <input class="obj-login-modal__content-input-text" placeholder="Email" type="email" required="required" title="<?=$this->translate('Your email')?>" tabindex="1" placeholder="Email" class="text" autofocus="autofocus" value="" name="email">
                        </div>
                        <div class="obj-login-modal__content-fields-block">
                            <i class="obj-login-modal__content-field-icon lock"></i>
                            <input class="obj-login-modal__content-input-text" placeholder="<?=$this->translate('Password')?>" type="password" required="required" title="<?=$this->translate('Your password')?>" tabindex="2" placeholder="<?=$this->translate('Password')?>" pattern="^[^\u0410-\u044f\u0451\u0401]+$" oninput="checkPasswordLayout(this)" value="" name="password">
                        </div>
                    </div>
                </div>
                <?php if (!$this->enableSocials){ ?>
                <div class="obj-login-modal__tip">
                    <?=$this->translate("You can use bitsa.Net account for login.")?>
                </div>
                <?php } ?>
                <div class="obj-login-modal__footer">
                    <div class="obj-login-modal__flex-container">
                        <div class="obj-login-modal__flex-item">
                            <div>
                                <a class="obj-login-modal__footer-links grey" href="/user/auth/forgot/"><?=$this->translate('Forgot password?')?></a>
                            </div>
                            <? if (!$isZftsh){ ?>
                                <div>
                                    <a class="obj-login-modal__footer-links blue" title="Регистрация на bitsa.Net" href="/signup/" onclick="globalRegisterOpen(event)"><?=$this->translate('Registration')?></a>
                                </div>
                            <? } ?>
                        </div>
                        <div class="obj-login-modal__flex-item 2-cols">
                            <button class="obj-login-modal__footer-submit el__button" tabindex="3" name="go"><?=$this->translate('Log in')?></button>
                        </div>
                    </div>
                    <? if ($isZftsh){ ?>
                        <a class="obj-login-modal__footer-links blue" title="Регистрация в ЗФТШ" href="/signup/" onclick="globalRegisterOpen(event)">Регистрация</a>
                    <? } ?>
                </div>
                <input type="hidden" value="<?=$this->url()?>" name="return_url">
            </form>
        </div>
    </div>
</div>

