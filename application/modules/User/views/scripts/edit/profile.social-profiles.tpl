 <div class="generic_layout_container social_profiles" <? if($this->tabname !== 'social_profiles'):?>style="display: none;"<? endif; ?>>
    <? if($this->viewer->isSelf($this->user)): ?>
        <? if($this->locale()->getLocale()->__toString() !== 'en'): ?>
            <h4>Вы можете привязать аккаунты в популярных сервисах к <a href="https://<?=$_SERVER['SERVER_NAME'];?>" target="_blank"><?=$_SERVER['SERVER_NAME'];?></a>, чтобы использовать их для авторизации без ввода логина и пароля</h4>
        <? else: ?>
            <h4><?=$this->translate('Your social network profiles'); ?></h4>
        <? endif; ?>
    <? endif; ?>
    <div class="global_form form-elements social">
        <div class="form-wrapper">
            <div class="form-label">
                <a href="https://vk.com" target="_blank">
                    <? if($this->locale()->getLocale()->__toString() === 'en'):?>
                        <img class="social_logo" height="39" src="/application/modules/User/externals/images/VK_logo_eng.png" width="39"/>
                    <? else: ?>
                        <img class="social_logo" height="39" src="/application/modules/User/externals/images/VK_logo.png" width="150"/>
                    <? endif; ?>
                </a>
            </div>
            <? if($this->viewer->isSelf($this->user)): ?>
                <input id="abitu-vk-link" type="hidden" value="<?=$this->vk_link;?>"/>
                <div class="form-element social_buttons vk_profile_button" id="vk_profile_button">
                    <? if(empty($this->social_profiles[0]['vk_id'])): ?>
                        <a href="<?=$this->vk_link;?>"><?=$this->translate('Add');?></a>
                    <? else: ?>
                        <a href="https://vk.com/id<?=$this->social_profiles[0]['vk_id']; ?>" target="_blank">vk.com/id<?=$this->social_profiles[0]['vk_id']; ?></a>&nbsp;<button class="social_remove_button" id="vk_remove"><?=$this->translate('Unlink profile');?></button>
                    <? endif; ?>
                </div>
            <? else: ?>
                <div class="form-element social_buttons vk_profile_button">
                    <? if(empty($this->social_profiles[0]['vk_id'])): ?>
                        <?=$this->translate('No linked account');?>
                    <? else:?>
                        <a href="https://vk.com/id<?=$this->social_profiles[0]['vk_id']; ?>" target="_blank">vk.com/id<?=$this->social_profiles[0]['vk_id']; ?></a>
                    <? endif; ?>
                </div>
            <? endif; ?>
        </div>

        <div class="form-wrapper">
            <div class="form-label"><a href="https://www.facebook.com" target="_blank"><img class="social_logo" height="56" src="/application/modules/User/externals/images/facebook.png" width="150"/></a></div>
            <div class="form-element social_buttons social_profile_block" id="fb_profile_button">
                <input id="abitu-fb-link" type="hidden" value="<?=$this->fb_link;?>"/>
                <? if(empty($this->social_profiles[0]['fb_id'])){ ?>
                    <? if ($this->viewer->isSelf($this->user)){ ?>
                        <a href="<?=$this->fb_link;?>" title="<?=$this->translate('Link profile');?>"><?=$this->translate('Add');?></a>
                    <? }else{ ?>
                        <?=$this->translate('No linked account');?>
                    <? } ?>
                <? }else{ ?>
                    <a href="https://www.facebook.com/app_scoped_user_id/<?=$this->social_profiles[0]['fb_id'];?>/" target="_blank">https://www.facebook.com/app_scoped_user_id/<?=$this->social_profiles[0]['fb_id'];?>/</a>&nbsp;
                    <? if ($this->viewer->isSelf($this->user)){ ?>
                        <button class="social_remove_button" id="fb_remove"><?=$this->translate('Unlink profile');?></button>
                    <? } ?>
                <? } ?>
            </div>
        </div>
        
        <div class="form-wrapper">
            <div class="form-label" style="text-align: center;">
                <a href="https://mipt.ru" target="_blank"><img class="social_logo" height="60" src="https://mipt.ru/images/tablo<? if($this->locale()->getLocale()->__toString() !== 'en'):?>_ru<? endif; ?>.png" width="150"/></a>
            </div>
            <? if($this->viewer->isSelf($this->user)): ?>
                <input id="abitu-mipt-link" type="hidden" value="<?=$this->mipt_link;?>"/>
                <div class="form-element social_buttons social_profile_block" id="mipt_profile_button">
                    <? if( empty($this->social_profiles[0]['mipt_id']) ): ?>
                        <a href="<?=$this->mipt_link;?>"><?=$this->translate('Add');?></a>
                    <? else: ?>
                        <?=$this->translate('Account linked');?> (id <?=$this->social_profiles[0]['mipt_id'];?>) &nbsp;<button class="social_remove_button" id="mipt_remove"><?=$this->translate('Unlink profile');?></button>
                    <? endif; ?>
                </div>
            <? else: ?>
                <div class="form-element social_buttons social_profile_block">
                    <? if( empty($this->social_profiles[0]['mipt_id']) ): ?><?=$this->translate('No linked account');?><? else: ?><?=$this->translate('Account linked');?> (id ?=$this->social_profiles[0]['mipt_id'];?>)<? endif; ?>
                </div>
            <? endif; ?>
        </div>
        
            <div class="form-wrapper">
                <div class="form-label">
                    <a href="https://ya.ru" target="_blank"><img class="social_logo" height="60" src="/application/modules/User/externals/images/YA_logo<? if($this->locale()->getLocale()->__toString() === 'en'): ?>_eng<? endif; ?>.png" width="150"/></a>
                </div>
                <? if($this->viewer->isSelf($this->user)): ?>
                    <input id="abitu-ya-link" type="hidden" value="<?=$this->ya_link;?>"/>
                    <div class="form-element social_buttons social_profile_block" id="ya_profile_button">
                        <? if( empty($this->social_profiles[0]['yandex_id']) ): ?>
                            <a href="<?=$this->ya_link;?>" title="<?=$this->translate('Link profile');?>"><?=$this->translate('Add');?></a>
                        <? elseif( !empty($this->social_profiles[0]['yandex_email']) ): ?>
                            <a href="mailto: <?=$this->social_profiles[0]['yandex_email']; ?>" target="_blank"><?=$this->social_profiles[0]['yandex_email']; ?></a>&nbsp;<button class="social_remove_button" id="ya_remove"><?=$this->translate('Unlink profile');?></button>
                        <? else: ?>
                            <?=$this->translate('Account linked');?> (id <?=$this->social_profiles[0]['yandex_id']; ?>)&nbsp;<button class="social_remove_button" id="ya_remove"><?=$this->translate('Unlink profile');?></button>
                        <? endif; ?>
                    </div>
                <? else: ?>
                    <div class="form-element social_buttons social_profile_block">
                        <? if( empty($this->social_profiles[0]['yandex_id']) ): ?>
                            <?=$this->translate('No linked account');?>
                        <? elseif( !empty($this->social_profiles[0]['yandex_email']) ): ?>
                            <a href="mailto: <?=$this->social_profiles[0]['yandex_email']; ?>" target="_blank"><?=$this->social_profiles[0]['yandex_email']; ?></a>
                        <? else: ?>
                            <?=$this->translate('Account linked');?> (id <?=$this->social_profiles[0]['yandex_id']; ?>)
                        <? endif; ?>
                    </div>
                <? endif; ?>
            </div>

            <div class="form-wrapper">
                <div class="form-label"><a href="https://google.ru" target="_blank"><img class="social_logo" height="51" src="/application/modules/User/externals/images/google_logo_flat_print_medium.png" width="143"/></a></div>
                <? if($this->viewer->isSelf($this->user)): ?>
                    <input id="abitu-google-link" type="hidden" value="<?=$this->google_link;?>"/>
                    <div class="form-element social_buttons social_profile_block" id="google_profile_button">
                        <? if( empty($this->social_profiles[0]['google_id']) ): ?>
                            <a href="<?=$this->google_link;?>" title="<?=$this->translate('Link profile');?>"><?=$this->translate('Add');?></a>
                        <? elseif( !empty($this->social_profiles[0]['google_email']) ): ?>
                            <a href="mailto: <?=$this->social_profiles[0]['google_email']; ?>" target="_blank"><?=$this->social_profiles[0]['google_email']; ?></a>&nbsp;<button class="social_remove_button" id="google_remove"><?=$this->translate('Unlink profile');?></button>
                        <? else: ?>
                            <?=$this->translate('Account linked');?> (id <?=$this->social_profiles[0]['google_id']; ?>)&nbsp;<button class="social_remove_button" id="google_remove"><?=$this->translate('Unlink profile');?></button>
                        <? endif; ?>
                    </div>
                <? else: ?>
                    <div class="form-element social_buttons social_profile_block">
                        <? if( empty($this->social_profiles[0]['google_id']) ): ?>
                            <?=$this->translate('No linked account');?>
                        <? elseif( !empty($this->social_profiles[0]['google_email'])  ): ?>
                            <a href="mailto: <?=$this->social_profiles[0]['google_email']; ?>" target="_blank"><?=$this->social_profiles[0]['google_email']; ?></a>
                        <? else: ?>
                            <?=$this->translate('Account linked');?> (id <?=$this->social_profiles[0]['google_id']; ?>)
                        <? endif; ?>
                    </div>
                <? endif; ?>
            </div>

            <div class="form-wrapper">
                <div class="form-label"><a href="https://mail.ru" target="_blank"><img class="social_logo" height="63" src="/application/modules/User/externals/images/mailru_medium.png" width="150"/></a></div>
                <? if($this->viewer->isSelf($this->user)): ?>
                    <input id="abitu-mailru-link" type="hidden" value="<?=$this->mailru_link;?>"/>
                    <div class="form-element social_buttons social_profile_block" id="mailru_profile_button">
                        <? if( empty($this->social_profiles[0]['mailru_id']) ): ?>
                            <a href="<?=$this->mailru_link;?>" title="<?=$this->translate('Link profile');?>"><?=$this->translate('Add');?></a>
                        <? elseif( !empty($this->social_profiles[0]['mailru_email']) ): ?>
                            <a href="mailto:<?=$this->social_profiles[0]['mailru_email']; ?>" target="_blank"><?=$this->social_profiles[0]['mailru_email']; ?></a>&nbsp;<button class="social_remove_button" id="mailru_remove"><?=$this->translate('Unlink profile');?></button>
                        <? else: ?>
                            <?=$this->translate('Account linked');?> (id <?=$this->social_profiles[0]['mailru_id']; ?>)&nbsp;<button  class="social_remove_button" id="mailru_remove"><?=$this->translate('Unlink profile');?></button>
                        <? endif; ?>
                    </div>
                <? else: ?>
                    <div class="form-element social_buttons social_profile_block">
                        <? if( empty($this->social_profiles[0]['mailru_id']) ): ?>
                            <?=$this->translate('No linked account');?>
                        <? elseif( !empty($this->social_profiles[0]['mailru_email']) ): ?>
                            <a href="mailto:<?=$this->social_profiles[0]['mailru_email']; ?>" target="_blank"><?=$this->social_profiles[0]['mailru_email']; ?></a>
                        <? else: ?>
                            <?=$this->translate('Account linked');?> (id <?=$this->social_profiles[0]['mailru_id']; ?>)
                        <? endif; ?>
                    </div>
                <? endif; ?>
            </div>

            <div class="form-wrapper">
                <div class="form-label"><a href="https://odnoklassniki.ru" target="_blank"><img class="social_logo" height="49" src="/application/modules/User/externals/images/ok.png" width="150"/></a></div>
                <? if($this->viewer->isSelf($this->user)): ?>
                    <input id="abitu-ok-link" type="hidden" value="<?=$this->ok_link;?>"/>
                    <div class="form-element social_buttons social_profile_block" id="ok_profile_button">
                        <? if( empty($this->social_profiles[0]['ok_id']) ): ?>
                            <a href="<?=$this->ok_link;?>" title="<?=$this->translate('Link profile');?>"><?=$this->translate('Add');?></a>
                        <? else: ?>
                            <a href="https://ok.ru/profile/<?=$this->social_profiles[0]['ok_id'];?>" target="_blank">ok.ru/profile/<?=$this->social_profiles[0]['ok_id'];?></a>&nbsp;<button class="social_remove_button" id="ok_remove"><?=$this->translate('Unlink profile');?></button>
                        <? endif; ?>
                    </div>
                <? else: ?>
                    <div class="form-element social_buttons social_profile_block">
                        <? if( empty($this->social_profiles[0]['ok_id']) ): ?>
                            <?=$this->translate('No linked account');?>
                        <? else: ?>
                            <a href="https://ok.ru/profile/<?=$this->social_profiles[0]['ok_id'];?>" target="_blank">ok.ru/profile/<?=$this->social_profiles[0]['ok_id'];?></a>
                        <? endif; ?>
                    </div>
                <? endif; ?>
            </div>
    </div>
</div>