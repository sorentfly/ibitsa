<? if (!$this->isSmoothBox){ ?>
    <h2><?=$this->translate("Create an uniform Abitu.Net account")?></h2>
    <?
    if ($this->enableSocials)
    {
        if(isset($_SESSION['registration_info']['vk_id']) ): ?>
            <p class="description">Чтобы привязать ваш <a href="//vk.com/id<?=$_SESSION['registration_info']['vk_id']?>" target="_blank" title="Ваш профиль Вконтакте">профиль</a> Вконтакте и использовать его для авторизации на <?=$this->current_site?>, необходимо пройти регистрацию.</p>
            <p class="description">Если у вас уже есть аккаунт на <?=$this->current_site?>, то вы можете привязать свой аккаунт Вконтакте на <a href="/members/edit/profile/social_profiles/">странице редактирования профиля</a>.</p>
        <? elseif(isset($_SESSION['registration_info']['mipt_id']) ): ?>
            <p class="description">Чтобы привязать ваш профиль на сайте <a href="https://mipt.ru/" target="_blank" title="Официальный сайт МФТИ">МФТИ</a> и использовать его для авторизации на <?=$this->current_site?>, необходимо пройти регистрацию.</p>
            <p class="description">Если у вас уже есть аккаунт на <?=$this->current_site?>, то вы можете привязать аккаунт <a href="https://mipt.ru/" target="_blank" title="Официальный сайт МФТИ">МФТИ</a> на <a href="/members/edit/profile/social_profiles/">странице редактирования профиля</a>.</p>
        <? elseif(isset($_SESSION['registration_info']['yandex_id']) ): ?>
            <p class="description">Чтобы привязать ваш профиль на <a href="https://ya.ru/" target="_blank">Яндексе</a> и использовать его для авторизации на <?=$this->current_site?>, необходимо пройти регистрацию.</p>
            <p class="description">Если у вас уже есть аккаунт на <?=$this->current_site?>, то вы можете привязать аккаунт Яндекса на <a href="/members/edit/profile/social_profiles/">странице редактирования профиля</a>.</p>
        <? elseif(isset($_SESSION['registration_info']['google_id']) ): ?>
            <p class="description">Чтобы привязать ваш профиль на <a href="https://google.com/" target="_blank">Google</a> и использовать его для авторизации на <?=$this->current_site?>, необходимо пройти регистрацию.</p>
            <p class="description">Если у вас уже есть аккаунт на <?=$this->current_site?>, то вы можете привязать аккаунт Google на <a href="/members/edit/profile/social_profiles/">странице редактирования профиля</a>.</p>
        <? elseif(isset($_SESSION['registration_info']['mailru_id']) ): ?>
            <p class="description">Чтобы привязать ваш профиль <a href="http://mail.ru/" target="_blank">Mail.ru</a> и использовать его для авторизации на <?=$this->current_site?>, необходимо пройти регистрацию.</p>
            <p class="description">Если у вас уже есть аккаунт на <?=$this->current_site?>, то вы можете привязать аккаунт Mail.ru на <a href="/members/edit/profile/social_profiles/">странице редактирования профиля</a>.</p>
        <? elseif(isset($_SESSION['registration_info']['fb_id']) ): ?>
            <p class="description">Чтобы привязать ваш профиль <a href="https://www.facebook.com/app_scoped_user_id/<?=$_SESSION['registration_info']['fb_id']?>/" target="_blank" title="Ваш профиль Facebook">профиль</a> на Facebook и использовать его для авторизации на <?=$this->current_site?>, необходимо пройти регистрацию.</p>
            <p class="description">Если у вас уже есть аккаунт на <?=$this->current_site?>, то вы можете привязать аккаунт Facebook на <a href="/members/edit/profile/social_profiles/">странице редактирования профиля</a>.</p>
        <? elseif(isset($_SESSION['registration_info']['twitter_id']) ): ?>
            <p class="description">Чтобы привязать ваш профиль <a href="https://twitter.com/" target="_blank">Twitter</a> и использовать его для авторизации на <?=$this->current_site?>, необходимо пройти регистрацию.</p>
            <p class="description">Если у вас уже есть аккаунт на <?=$this->current_site?>, то вы можете привязать аккаунт Twitter на <a href="/members/edit/profile/social_profiles/">странице редактирования профиля</a>.</p>
        <? elseif(isset($_SESSION['registration_info']['ok_id']) ): ?>
            <p class="description">Чтобы привязать ваш профиль на <a href="http://ok.ru/" target="_blank">одноклассниках</a> и использовать его для авторизации на <?=$this->current_site?>, необходимо пройти регистрацию.</p>
            <p class="description">Если у вас уже есть аккаунт на <?=$this->current_site?>, то вы можете привязать аккаунт на одноклассниках на <a href="/members/edit/profile/social_profiles/">странице редактирования профиля</a>.</p>
        <? endif;
    }
    ?>
<? }else{
    /* @var Zend_Form_Element */
    $this->form->submit->setLabel( $this->translate("Register") );

    if (isset($this->form->terms) ){
        $this->form->terms->setLabel( $this->translate("I agree to the <A href='/help/terms/' target='_blank'>site terms</A>"));
    }
    if (isset($this->form->profile_status) ) {
        $this->form->profile_status->setAttrib('disable', ['']);
    }
    if (isset($this->form->email)) {
        $this->form->email->setDescription(null);
    }
?>
    <style type="text/css">
        h2.reg_head
        {
            margin: 0;
            margin-top: 20px;
            opacity: 0.8;
        }
        .form-label, .formUpperLabel
        {
            display: none !important;
        }
        .formUpperLabel[for=mobilephone]
        {
            display: block !important;
        }
        .global_form div.form-wrapper {
            margin-bottom: 2px;
            margin-top: 2px;
        }
        .global_form div.form-element {
            display: block;
            box-sizing: border-box;
        }
        #profile_status
        {
            margin-top: 0;
            width: 270px;
            max-width: none;
            display: block;
        }
        #registration_form
        {
            display: flex;
            margin-bottom: 0;
            padding: 0;
        }
        .global_form input[type="text"],
        .global_form input[type="tel"],
        .global_form input[type="number"],
        .global_form input[type="datetime"],
        .global_form input[type="time"],
        .global_form input[type="date"],
        .global_form input[type="email"],
        .global_form input[type="password"]{
            width: 270px;
        }
        .global_form .form-element-captcha {display:flex;align-items:center;justify-content:space-between;width:275px;}
        .global_form .form-element-captcha input {width: 115px;}
        .form-elements
        {
            width: auto;
        }
        .form-wrapper.telephoneWrapper
        {
            margin-top: 0 !important;
        }
        .formIconCalendar
        {
            position: absolute;
            margin-left: -35px;
            margin-top: 2px;
        }
        .form-wrapper[for=submit]
        {
            padding-bottom: 0;
        }
        .form-wrapper[for=submit] > .form-element
        {
            display: block;
        }
        .form-wrapper[for=submit] > .form-element > button
        {
            width: 100%;
        }
        #password_confirm_status
        {
            display: block;
        }

        .registration_socials
        {
            border-top: 1px solid #F1F1F1;
            padding-top: 10px;
            margin-bottom: 0;
            align-items: center;
        }
        .social_icons_description
        {
            opacity: 0.25;
            font-size: 16px;
            transition: all 250ms cubic-bezier(0.42, 0, 0.58, 1) 0s;
        }
        .registration_socials:hover .social_icons_description
        {
            opacity:0.7;
        }

        .obj-login-modal__header-exit-btn
        {
            margin-top: 4px;
            margin-left: 10px;
            position: absolute;
        }
    </style>
    <script type="text/javascript">
        jQuery(function($){
            $('[required]').each(function(){
                $(this).parent().prepend('<span class="unlabeled_asterisk" title="<?=$this->translate("Required field")?>">*</span>');
            });
        });
    </script>
    <h2 class="reg_head">
        <? if (Engine_Api::_()->core()->getNowDomainSettings()['key'] == 'mipt_conference'){ ?>
            Регистрация
        <? }else{ ?>
            <?=$this->translate('First time on Abitu.Net?')?>
        <? } ?>
        <a href="javascript:void(0)" onclick="parent.Smoothbox.close();" class="obj-login-modal__header-exit-btn"></a>
    </h2>
<? } ?>
<? if (!$this->isSmoothBox){ ?>
    <div style="padding-top: 15px;">
        <? if($this->enableSocials){ ?>
            <div class="obj-login-modal__content-social-login-title"><?=$this->translate('Log-in with')?></div>
            <div class="registration_socials">
                <?=$this->partial('_loginSocials.tpl','user')?>
            </div>
        <? } ?>
        <p class="description"><?=$this->translate('If you have account')?> <A HREF="http://<?=ABITU_SITE?>">Abitu.Net</A><?=$this->translate(' - use it to log in here.')?></p>
        <p><?=$this->translate("Use your login and password to send verify account message twice, if you haven't recived this one.")?> </p>
    </div>
<? } ?>

<?=$this->form;?>
<script type="text/javascript">
    jQuery(function($){
        /* plugin-info: https://github.com/cloudfour/hideShowPassword */
        HidePasswordOptionsStandart = window.HidePasswordOptionsStandart ? window.HidePasswordOptionsStandart : parent.HidePasswordOptionsStandart;
        $('#password').hidePassword(HidePasswordOptionsStandart);
        $('#password_confirm').hidePassword(HidePasswordOptionsStandart);
    });
</script>

<? if ($this->isSmoothBox && $this->enableSocials){ ?>
    <div class="registration_socials">
        <span class="social_icons_description"><?=$this->translate('Register using ');?></span>
        <?=$this->partial('_loginSocials.tpl','user')?>
    </div>
<? } ?>
