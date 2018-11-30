<?
    $current_href = '/members/edit/profile/';
    if(!$this->viewer->isSelf($this->user)) {
        $current_href .= 'id/' . $this->user->user_id . '/';
    }

    $this->headScript()->appendFile('/externals/moolasso/Lasso.js')
                       ->appendFile('/externals/moolasso/Lasso.Crop.js')
                       ->appendFile(JQUERY_UI_LIB)
                       ->appendFile('/application/modules/Core/externals/scripts/jquery.ba-bbq.min.js') /* Разбор get параметров */
                       ->appendFile('/application/modules/Core/externals/scripts/datetime.js') /* jQuery календарик */
                       ->appendFile('/application/modules/Core/externals/scripts/notification.js') /* Виджет для уведомлений */
                       ->appendFile('/application/modules/Core/externals/scripts/tab_change.js')
                       ->appendFile('/application/modules/User/externals/scripts/profile_edit.js')
                       ->appendFile('/application/modules/User/externals/scripts/teachersMultitext.js');

    
    $this->headLink()->appendStylesheet('/application/modules/User/externals/styles/profile.css')
                     ->appendStylesheet('/application/modules/Core/externals/styles/datetimepicker.css');
?>
<SCRIPT type="text/javascript">
    window.subjectMultiOptions = <?=json_encode(Core_Api_Core::$themes, JSON_UNESCAPED_UNICODE)?>;
    jQuery(function($){
        $('#personal_information #profile_status').change(function(){
            $('#personal_information').submit();
            $("#personal_information button[type='submit']").html(getLoaderImg()).removeAttr('type');
        });

        $('#profile_tabs a').click(function(){
            setTimeout(fillRegionListIfVoid,300);
        });
        fillRegionListIfVoid();
        
        <?/*костылик для ограниченности редактирования профиля - на disabled-элементах не происходят события*/?>
        $('.form-element > input[onmouseover],.form-element > select[onmouseover],.form-element > textarea[onmouseover]').each(function(){
            $(this).parent().attr('onmouseover', jQuery(this).attr('onmouseover'));
        });
    });
</SCRIPT>

<input id="editing_user_id" name="editing_user_id" type="hidden" value="<?=$this->editing_user;?>"/>
    
<div class="headline">
    <h2>
        <? if ($this->viewer->isSelf($this->user)):?>
            <?=$this->translate('Edit my profile');?>
        <? else: ?>
            <? if($this->locale()->getLocale()->__toString() === 'en'):?>
                <a href="<?=$this->user->getHref();?>" title="Edit <?=$this->user->getTitle();?>\'s profile"><?=$this->user->getTitle();?></a>'s profile
            <? else: ?>
                Профиль <a href="<?=$this->user->getHref();?>"><?=$this->name_case_lib->q($this->user->getTitle(), NCL::$GENETIVE);?></a>
            <? endif; ?>
        <? endif; ?>        
    </h2>
</div>

<?php if (!$this->viewer->is_required_fields_filled){
    $DS = Engine_Api::_()->core()->getNowDomainSettings();
?>
<ul class="form-errors"><li>
	<i class="fa fa-warning"></i>
	<ul class="errors"><li><?=$this->translate('Fill out the required fields for full-featured use of the portal,')?> <?=!empty($DS['zftshDefaults']) ? $this->translate('learning in online school') : $this->translate('passing olympiads, obtaining diplomas') ?>! <BR>
                <?=$this->translate('You must enter the correct data')?><BR>
                <?=$this->translate('Fields are not filled:')?> <span class="requied_fields_alert_flabels">
                <?
				$asteriksCategories = array();
				foreach($this->unfilled_fields as $field){ 
					$labels[] = $this->translate($field['label']);
                	$asteriksCategories[$field['category']] = 1;
                } 
                echo(implode(', ', $labels));
                ?></span>.
            <? if (empty(Engine_Api::_()->core()->getNowDomainSettings()['user_field_overrides'])){ ?>
                <?=$this->translate('Or set the profile type to "Other"')?>.
            <? } ?>
      </li></ul></li></ul>
<?php } ?>

<div class="generic_layout_container layout_core_container_tabs">
    
    <div class="tabs_alt tabs_parent" id="profile_tabs">
        <ul id="main_tabs">
            <? foreach($this->profile_tabs as $key => $value): ?>
                <? if($this->tabname === $key): ?>
                    <li class="active"><a data-name="<?=$key;?>" data-href="<?=$current_href . $key;?>/"><?=$value['label']; ?><?=isset($asteriksCategories[$key])?' <b style="color:red">*</b>':''?></a></li>
                <? else: ?>
                    <li><a data-name="<?=$key;?>" href="<?=$current_href . $key;?>/"><?=$value['label']; ?><?=isset($asteriksCategories[$key])?' <b style="color:red">*</b>':''?></a></li>
                <? endif; ?>
            <? endforeach; ?>
        </ul>
    </div>    

    <?
	$tabH3 = [
	    'home_address'        => 'Requires actual address to send diploma',
	    'secondary_education' => 'Where did you go to school?',
	    'higher_education'    => 'Where did you go to college or university?',
	    'childs_info'         => $this->translate('List of children'),
	    'work_info'           => $this->translate('Information about the place of work'),
	    'students'            => $this->translate('List of your students'),
	    'passport'            => $this->translate('The identity card for issuing participation and pass documents')
	];        
	foreach($this->fieldTabs as $key) {   
		if( isset($this->profile_tabs[$key]) && ($form = $this->profile_tabs[$key]['form']) && $form->hasFields){
		?>
    <div class="generic_layout_container <?=$key?>"<?= ($this->tabname !== $key) ? ' style="display: none;"': '' ?>>
    	<? if (isset($tabH3[$key])){ ?>
		<h4><?=$this->translate($tabH3[$key]); ?></h4>
        <? } ?>
        <?= $form ?>
	</div>
<? 		} 
	} 		?>
    
<?	
	if(!empty($this->profile_tabs['user_photo'])){ 
    	echo $this->partial('/edit/profile.user-photo.tpl', $this->getVars());    	
    } 
?>	
        
<?	
	if(!empty($this->profile_tabs['social_profiles'])){
    	echo $this->partial('/edit/profile.social-profiles.tpl', $this->getVars());    	
    }  
?>
   
   
   <div class="generic_layout_container settings_general"<?= ($this->tabname !== 'settings_general') ? ' style="display: none;"': '' ?>>
		<?= $this->action('general', 'settings', 'user') ?>
   </div>
   
   <div class="generic_layout_container settings_privacy"<?= ($this->tabname !== 'settings_privacy') ? ' style="display: none;"': '' ?>>
		<?= $this->action('privacy', 'settings', 'user') ?>
   </div>
   
   <div class="generic_layout_container settings_notifications"<?= ($this->tabname !== 'settings_notifications') ? ' style="display: none;"': '' ?>>
		<?= $this->action('notifications', 'settings', 'user') ?>
   </div>
   
   <div class="generic_layout_container settings_password"<?= ($this->tabname !== 'settings_password') ? ' style="display: none;"': '' ?>>
		<?= $this->action('password', 'settings', 'user') ?>
   </div>
   
   <div class="generic_layout_container settings_delete"<?= ($this->tabname !== 'settings_delete') ? ' style="display: none;"': '' ?>>
		<?= $this->action('delete', 'settings', 'user') ?>
   </div>
   
</div>