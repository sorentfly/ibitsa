<div id="profile_status" class="profile_status_flexbox">
    <h2><span class="id_display"> ID <?=$this->user->getIdentity()?> </span><?=Zend_Registry::get('Zend_Translate')->getLocale() == 'en' ? Engine_Api::_()->string()->transliterate($this->user->getFIO()) : $this->user->getFIO() ?></h2>
    <?php if ($this->friends_count){ $frCount = $this->translate(array(
                   "%s friend",
                   "%s friends",
                   $this->friends_count),
                 $this->friends_count); ?>
        <div id="members_popup_link_container">
        <? if (!$this->auth){ ?>            
            <span id="members_popup">
                <?=$frCount?>
            </span>
        <? }else{ ?>
            <A HREF="javascript:void(0);" id="members_popup"
               <? if (empty(Engine_Api::_()->core()->getNowDomainSettings()['zftshDefaults'])){ ?>
               onmouseover="en4.core.members.popup(jQuery(this), '<?=$this->user->getGuid()?>');"
               <? } ?>
               onclick="en4.core.members.popup(jQuery(this), '<?=$this->user->getGuid()?>');">
                <?=$frCount?>
                <i class="fa fa-chevron-down"></i>
            </A>
        <? } ?>
        </div>
    <?php } ?>
    <div class="last_activity"><?=$this->last_activity; ?></div>
</div>
<? if($this->checked === 1): ?>
    <span class="checked_verified" onmouseover="pageVerifiedTipShow();" onmouseout="pageVerifiedTipHide();">✓</span>
    <div class="verified_tip" onmouseover="pageVerifiedTipShow();" onmouseout="pageVerifiedTipHide();" style="display: none;">
        <i class="triangle_top"></i>
        <h5><?=$this->translate('Verified account'); ?></h5>
        <?=$this->translate('This tick shows that %1$s page has been verified by the bitsa.Net team', $this->first_name_genetive); ?>
    </div>
    <br/>
<? endif; ?>
<? if($this->auth): ?>
    <span class="profile_status_text" id="user_profile_status_container">
        <?=$this->viewMore($this->subject()->status); ?>
        <? if(!empty($this->subject()->status) && $this->subject()->isSelf($this->viewer())): ?>
            <a class="profile_status_clear" href="javascript:void(0);" onclick="en4.user.clearStatus();">(<?=$this->translate('clear') ?>)</a>
        <? endif; ?>
    </span>
<? endif; ?>