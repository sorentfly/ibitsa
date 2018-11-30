<? if($this->user  && $this->viewer  && $this->viewer->getIdentity() && $this->user->user_id !== $this->viewer->user_id && ($this->viewer->level_id === 1 || $this->viewer->level_id === 2)): ?>
    <script async src="/application/modules/User/externals/scripts/profile_view_admin.js"></script>
    <input class="view_user_id" type="hidden" value="<?=$this->user->user_id;?>"/>
<? endif; ?>
    
<? if($this->is_authorized && $this->user->user_id !== $this->viewer->user_id && ($this->viewer->level_id === 1 || $this->viewer->level_id === 2)): ?>
    <div class="buttons_shell">
        <div class="simple_button auth_into">Авторизоваться под <?=$this->first_name_instrumental;?></div>
        <div class="simple_button user_remove">Удалить <?=$this->first_name_accusative;?></div>
    </div>
    <div class="admin-info">

    </div>
<? endif; ?>

<style type="text/css">
    #build_in_avatar{
        padding-right: 25px;
        min-width: 170px;
    }
    .user_info_tabs
    {
        display: flex;
        flex-flow: row wrap;
        justify-content: space-around;
    }
    
    .user_info_tabs li
    {
        flex-basis: 200px;
        padding:5px;
    }
   
    
    .user_info_tabs li:before
    {
        content: '⚫';
        padding-left: 10px;
        padding-right: 10px;
        color: #cacaca;
    }
    .user_info_tabs li a.active
    {
        color: #0d64ac;
    }
</style>
<script>
    var userInfoTabSwitch = function(tab){
        jQuery('.profile_fields').hide();
        jQuery('.'+tab).show();
        jQuery('.user_info_tabs li a').removeClass('active');
        jQuery('li.'+tab+' a').addClass('active');
    };
</script>
<? if ($this->buidInAvatarAndOptions){ ?>
    <div id="build_in_avatar">
        <?=$this->content()->renderWidget('user.profile-photo') ?>
        <?=$this->content()->renderWidget('user.profile-options') ?>
    </div>
    <style type="text/css">
        .layout_user_profile_fields > div:last-child {
            flex-grow: 1;
            max-width: 700px;
        }
        .layout_user_profile_fields
        {
            display: flex;
        }
        @media (max-width: 700px) {
            .layout_user_profile_fields
            {
                flex-wrap: wrap;
            }
        }

        .profile_fields > ul > li > span:last-child
        {
            max-width: 50%;
        }
    </style>
<? } ?>
<div>
<?
$getFieldGroupLabel = function($groupName)
{
    if (empty($this->user_values[$groupName])){
        return $groupName == 'personal_information' ? "Личная информация" : $groupName;
    }

    $group = $this->user_values[$groupName];
    $some = end($group);
    return $this->translate($some['categoryLabel']);
}
?>

<? if (empty($this->isZftshPupil)){ ?>
    <? if ( !empty($this->learninfo)){ ?>
        <div class="profile_fields zftsh">
            <h4>О себе
                <span class="profile_part_edit"><a class="profile_part_edit_a" href="/members/edit/profile/personal_information/#birthdate"><?=$this->translate('_EDIT'); ?></a></span>
            </h4>
            <?=$this->learninfo?>
        </div>
    <? } ?>
<? } ?>
<? if ($this->defaultInfoTab == 'zftsh'){ /*Вкладка существует только в вырожденном случае*/ ?>
    <div class="profile_fields zftsh">
        <h4><?=$getFieldGroupLabel('zftsh')?></h4>
        <ul>
            <? if (isset($this->member_code) && $this->member_code){ ?>
                <li><b>Персональный номер</b> <span><B><?=$this->member_code?></B></span></li>
            <? } ?>
            <? if (!empty($this->learninfo) && $this->isZftshPupil){ ?>
                <li><b>Заметки об обучении методисту</b> <span><?=$this->learninfo?></span></li>
            <? } ?>
            <? if (!empty($this->contact_information)){ ?>
                <li><span>Контактная информация ЗФТШ</span> <span><?=$this->contact_information?></span></li>
            <? } ?>
            <? if (!empty($this->wishes) && !$this->isZftshPupil){ ?>
                <li><b>Пожелания по работе в ЗФТШ-онлайн</b> <span><?=$this->wishes?></span></li>
            <? } ?>
            <? if (!empty($this->cell_number && !$this->isZftshPupil)){ ?>
                <li><B>Номер ячейки</B> <span><?=$this->cell_number?></span></li>
            <? } ?>
            <? if (isset($this->user_values['personal_information']['zftsh_year']) && $this->user_values['personal_information']['zftsh_year']['value'] && $this->isZftshPupil){ ?>
                <li><span>Год начала обучения</span> <span><?=date('Y', strtotime($this->user_values['personal_information']['zftsh_year']['value'])) ?></span></li>
            <? } ?>
            <? if ($this->zftsh_max_class){ ?>
                <li><span>Класс <?=$this->isZftshPupil ? 'обучения' : 'преподавания' ?> в ЗФТШ</span> <span><?=$this->zftsh_max_class ?></span></li>
            <? } ?>
            <? if ( count($this->zftsh_methodists) ){ ?>
                <li><span>Методисты </span> <span><?=implode(', ', $this->zftsh_methodists) ?></span></li>
            <? } ?>
            <? if ( count($this->zftsh_teachers) ){ ?>
                <li><span>Преподаватели </span> <span><?=implode(', ', $this->zftsh_teachers) ?></span></li>
            <? } ?>
                <? if ( count($this->zftsh_academies) ){ ?>
                <li><span>Предметы </span> <span><?=implode(', ', $this->zftsh_academies) ?></span></li>
            <? } ?>
        </ul>
    </div>   
<? } ?>
    
<div class="profile_fields personal_information">
    <h4>
        <?=$getFieldGroupLabel('personal_information');?>
        <? if($this->is_self): ?>
            <span class="profile_part_edit"><a class="profile_part_edit_a" href="/members/edit/profile/personal_information/"><?=$this->translate('_EDIT'); ?></a></span>
        <? elseif($this->is_admin): ?>
            <span class="profile_part_edit"><a class="profile_part_edit_a" href="/members/edit/profile/id/<?=$this->user->user_id;?>/personal_information/"><?=$this->translate('_EDIT'); ?></a></span>
        <? endif; ?>
    </h4>

    <ul>
    	<? if($this->private_view || $this->user->authorization()->isAllowed($this->viewer(), 'view_username')){ ?>
    	<li><span><?=$this->translate('Last Name'); ?></span> <span><?=$this->user->last_name; ?></span></li>
        <li><span><?=$this->translate('First Name'); ?></span> <span><?=$this->user->first_name; ?></span></li>
        <li><span><?=$this->translate('Middle Name'); ?></span> <? if($this->is_authorized): ?><span><?=$this->user->middle_name; ?></span><? else: ?><span class="hidden_field"><?=$this->translate('_HIDDEN_N'); ?></span><? endif; ?></li>
        <? } ?>
        <? if($this->user->gender === 1): ?>
            <li><span><?=$this->translate('Gender'); ?></span> <span><?=$this->translate('Female'); ?></span></li>
        <? elseif($this->user->gender === 2): ?>
            <li><span><?=$this->translate('Gender'); ?></span> <span><?=$this->translate('Male'); ?></span></li>
        <? endif; ?>

        <? if($this->user->birthdate != null && $this->private_view){
            /*FIX BUG*/
            $bDayField = Engine_Api::_()->fields()->getValByName($this->user, 'birthdate');
            if ($bDayField &&  strtotime($bDayField) > 86400 && ($bDayFieldFormatted = date('Y-m-d', strtotime($bDayField))) != $this->user->birthdate){
                $this->user->birthdate = $bDayFieldFormatted;
                $this->user->save();
            }
        ?>
            <li><span><?=$this->translate('Birthdate'); ?></span> <span><?=$this->locale()->toDateTime($this->user->birthdate. ' 12:00:00', array('format' => "d MMMM y 'г'.")); ?></span></li>

        <? }elseif($this->user_age != null){ ?>
            <li><span><?=$this->translate('Age'); ?></span> <? if($this->private_view): ?><span><?=$this->user_age; ?></span><? else: ?><span class="hidden_field"><?=$this->translate('_HIDDEN_M'); ?></span><? endif; ?></li>
        <? } ?>

        <? if($this->private_view): ?>
            <li><span><?=$this->translate('Email'); ?></span> <span><a href="mailto:<?=$this->user->email; ?>" target="_blank"><?=$this->user->email; ?></a></span></li>
        <? else: ?>
            <li><span><?=$this->translate('Email'); ?></span> <span class="hidden_field"><?=$this->translate('_HIDDEN_M'); ?></span></li>
        <? endif; ?>

        <? if($this->user->mobilephone != null && $this->private_view): ?>
            <li><span><?=$this->translate('Mobilephone'); ?></span> <span><?=$this->user->mobilephone; ?></span></li>
        <? elseif($this->user->mobilephone != null): ?>
            <li><span><?=$this->translate('Mobilephone'); ?></span> <span class="hidden_field"><?=$this->translate('_HIDDEN_M'); ?></span></li>
        <? endif; ?>

        <? if($this->user->homephone != null && $this->private_view): ?>
            <li><span><?=$this->translate('Homephone'); ?></span> <span><?=$this->user->homephone; ?></span></li>
        <? elseif($this->user->homephone != null): ?>
            <li><span><?=$this->translate('Homephone'); ?></span> <span class="hidden_field"><?=$this->translate('_HIDDEN_M'); ?></span></li>
        <? endif; ?>

        <? if($this->profile_status != null):?>
            <li><span><?=$this->translate('Status'); ?></span> <span><?=$this->profile_status; ?></span></li>
        <? endif; ?>
    </ul>
    <?=$this->cadastreWidget->render()?>
</div>
<? if ( !empty($this->isPupilThisOrLastYear) ){ ?>
    <div class="profile_fields legal_docs">
        <h4>
            Документы и справки ЗФТШ
        </h4>
        <ul>
                <? if ($this->showZftshDocumentsBlock) foreach ($this->zftsh_documents as $themeBitmask => $status) { ?>
                <? $school_subject = Core_Api_Core::getThemeByBit($themeBitmask)['label']; ?>
                <li>
                    <A HREF="<?=$this->url(['action' => 'print-transfer-pdf', 'id' => $this->user->getIdentity()], 'zftsh-docs', true)?>?theme=<?=$themeBitmask?>&year=<?=$this->academyCurrentYear?>" target="_blank" >
                        <i class="fa fa-file-pdf-o" style="font-size: 1.2em;"></i> <B><?=ucfirst($school_subject)?>: уведомление <?=$status['title']?></B>
                    </A>
                </li>
                <? } ?>

                <? if ($this->hasFulltimeStudy) { ?>
                <li><A HREF="/folders/file/2127" id="personal_data_doc">
                        <i class="fa fa-file-pdf-o" style="font-size: 1.2em;"></i> Заявление от родителей (представителей)
                    </A>
                </li>
                <li><A HREF="/folders/file/2128" id="personal_data_doc">
                        <i class="fa fa-file-pdf-o" style="font-size: 1.2em;"></i> Согласие родителей (представителей) на обработку персональных данных
                    </A>
                    <p align="right" style="margin-right: 0px; margin-left:auto; width:270px; font-size:0.7em; color:#999">Учащиеся, поступившие на очное отделение в этом году, должны до конца октября сдать методисту Светлане Ивановне Борис (106 комната Аудиторного корпуса) или одному из преподавателей следующие документы:<br>1. Заявление от родителей (законных представителей);<br>2. Согласие родителей (законных представителей) на обработку персональных данных;<br>3. Справку из школы (о том, что вы являетесь учащимся общеобразовательного учреждения с указанием класса и печатью образовательного заведения).</p>
                </li>
                <? } else { ?>
                <? if ($this->UserCountry == 'Россия') { ?>
                    <li><A HREF="/application/modules/Zftsh/externals/pismo_o_vznose_s_kvitantsiey_2018_1.docx" download>
                            <i class="fa fa-file-word-o" style="font-size: 1.2em;"></i> Письмо о взносе с квитанцией
                        </A></li>
                <? } ?>
                <li><A HREF="/application/modules/Zftsh/externals/pismo_roditelyam_2018.docx" download>
                        <i class="fa fa-file-word-o" style="font-size: 1.2em;"></i> Письмо родителям
                    </A></li>
                <li><A HREF="/application/modules/Zftsh/externals/SOGLASIE_ZAKONNOGO_PREDSTAVITELYa.docx" id="personal_data_doc" download>
                        <i class="fa fa-file-word-o" style="font-size: 1.2em;"></i> Согласие законного представителя
                    </A>
                    <p align="right" style="margin-right: 0px; margin-left:auto; width:270px; font-size:0.7em; color:#999">Необходимо заполнить, подписать и отправить оригинал почтой по адресу: ЗФТШ МФТИ Институтский пер., 9, г. Долгопрудный, Московская обл., 141700</p>
                </li>
                <? } ?>
        </ul>
    </div>
<? } ?>
    
<?php if ($this->user_values) foreach($this->user_values as $groupName => $group){
    if ($groupName == 'personal_information' || $groupName == 'zftsh') continue;
    ?>
    <? if($group): ?>
        <div class="profile_fields <?=$groupName?>">
            <h4>
                <?=$getFieldGroupLabel($groupName);?>
                <? if($this->is_self): ?>
                <span class="profile_part_edit"><a class="profile_part_edit_a" href="/members/edit/profile/<?=$groupName?>/"><?=$this->translate('_EDIT'); ?></a></span>
                <? elseif($this->is_admin): ?>
                    <span class="profile_part_edit"><a class="profile_part_edit_a" href="/members/edit/profile/id/<?=$this->user->user_id;?>/<?=$groupName?>/"><?=$this->translate('_EDIT'); ?></a></span>
                <? endif; ?>
            </h4>
            <ul>
                <? foreach($group as $field){
                    if ($field['name'] == 'teachers'){
                        /* КОСТЫЛЬ: отображение поля "Учителя" - поидее бы вынести этот кусок кода чтоб не путал */
                        $transformedValue = [];
                        foreach(is_array($field['value'])?$field['value']:[$field['value']] as $line){
                            $chunks = explode('&', $line);
                            if (count($chunks) > 1 && isset(Core_Api_Core::$themes[$chunks[0]]) ){
                                $transformedValue[] = '<i>'.Core_Api_Core::$themes[$chunks[0]]['label'].'</i>: '.$chunks[1];
                            }else{
                                $transformedValue[] = $line;
                            }
                        }
                        $field['value'] = $transformedValue;
                    }
                ?>
                    <? if($field['value'] && $field['value'] != '0' && $field['hidden']!='2'): ?>
                        <li>
                            <span><?=$this->translate($field['label']); ?></span>
                            <?php if ($field['hidden']=='1' && !$this->private_view){ ?>
                               <span class="hidden_field">Скрыто</span>
                            <?php }else{ ?>
                               <span><?=is_array($field['value']) ? implode(', ', $field['value']) :  $field['value']; ?></span>
                            <?php } ?>
                        </li>
                    <? endif; ?>
                <? } ?>
                <? if ($groupName == 'home_address' && $this->private_view){ ?>
                <li><span>&nbsp;</span> <span><a href="<?=$this->maps_url; ?>/?text=<?=urlencode($this->location_address); ?>" target="_blank">На карте</a></span></li>
                <? }?>
            </ul>
        </div>
    <? endif; ?>
<?php } ?>

    
<? if(isset($this->users_social[0]) && count($this->users_social[0]) > 0 && $this->is_authorized): ?>
    <div class="profile_fields users_social">
        <h4>
            <span><?=$this->translate('Profiles in social networks'); ?></span>
            <? if($this->is_self): ?>
                <span class="profile_part_edit"><a class="profile_part_edit_a" href="/members/edit/profile/social_profiles/"><?=$this->translate('_EDIT'); ?></a></span>
            <? elseif($this->is_admin): ?>
                <span class="profile_part_edit"><a class="profile_part_edit_a" href="/members/edit/profile/id/<?=$this->user->user_id;?>/social_profiles/"><?=$this->translate('_EDIT'); ?></a></span>
                <? endif; ?>
        </h4>    
        <ul>
            <? if( !empty($this->users_social[0]['vk_id']) ): ?>
                <li><span>Вконтакте</span> <a href="https://vk.com/id<?=$this->users_social[0]['vk_id']; ?>" target="_blank">vk.com/id<?=$this->users_social[0]['vk_id']; ?></a>
                    <? if($this->is_authorized && $this->user->user_id !== $this->viewer->user_id): ?>
                        <a class="simple_button" href="https://vk.com/write<?=$this->users_social[0]['vk_id']; ?>" style="margin-left: 10px; text-decoration: none;" target="_blank">Написать сообщение вконтакте</a>
                    <? endif; ?>
                </li>
            <? endif; ?>

            <? if( !empty($this->users_social[0]['mipt_id']) ): ?>
                <li><span>МФТИ</span> user id <?=$this->users_social[0]['mipt_id']; ?></li>
            <? endif; ?>

            <? if( !empty($this->users_social[0]['yandex_email']) && $this->private_view): ?>
                <li><span>Яндекс</span> <a href="mailto:<?=$this->users_social[0]['yandex_email']; ?>" target="_blank"><?=$this->users_social[0]['yandex_email']; ?></a></li>
            <? elseif( !empty($this->users_social[0]['yandex_id']) && $this->private_view): ?>
                <li><span>Яндекс</span> user id <?=$this->users_social[0]['yandex_id']; ?></li>
            <? endif; ?>

            <? if( !empty($this->users_social[0]['google_email']) && $this->private_view): ?>
                <li><span>Google</span> <a href="mailto:<?=$this->users_social[0]['google_email']; ?>" target="_blank"><?=$this->users_social[0]['google_email']; ?></a></li>
            <? elseif( !empty($this->users_social[0]['google_id']) && $this->private_view): ?>
                <li><span>Google</span> user id <?=$this->users_social[0]['google_id']; ?></li>
            <? endif; ?>

            <? if( !empty($this->users_social[0]['mailru_email']) && $this->private_view): ?>
                <li><span>Mail.ru</span> <a href="mailto:<?=$this->users_social[0]['mailru_email']; ?>" target="_blank"><?=$this->users_social[0]['mailru_email']; ?></a></li>
            <? elseif( !empty($this->users_social[0]['mailru_id']) && $this->private_view): ?>
                <li><span>Mail.ru</span> user id <?=$this->users_social[0]['mailru_id']; ?></li>
            <? endif; ?>

            <? if( !empty($this->users_social[0]['ok_id']) && $this->private_view): ?>
                <li><span>ok.ru</span> <a href="https://ok.ru/profile/<?=$this->users_social[0]['ok_id'];?>" target="_blank">ok.ru/profile/<?=$this->users_social[0]['ok_id'];?></li>
            <? endif; ?>
        </ul>
    </div>
<? endif; ?>
</div>