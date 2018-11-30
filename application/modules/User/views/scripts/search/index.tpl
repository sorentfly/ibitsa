<?
 $this->headScript()
                ->appendFile(JQUERY_UI_LIB)
                ->appendFile('/application/modules/Core/externals/scripts/notification.js') /* Виджет для уведомлений */
                ->appendFile('/application/modules/User/externals/scripts/search/core_selects.js');
    
    $this->headLink()->appendStylesheet('/application/modules/User/externals/styles/search/main.css');
    
    $this->headTitle('Пользователи');
?>

<script async src="/application/modules/User/externals/scripts/search/user_search.js"></script>
<? if ($this->isZftshSearch){ ?>
    <style type="text/css">
        .search-result-item
        {
            text-align: center;
        }
        .user_status
        {
            text-align: center;
            max-width: 170px;
            display: inline-block;
            padding-bottom: 3px;
        }
        .user_status > span
        {
            white-space: nowrap;
            opacity: 0.7;
        }
        .user_status > span.profile_info
        {
            opacity: 0.5;
            font-weight: bold;
        }
        .result-item-user-name .fa
        {
            color: #AAA;
        }
        .result-item-user-name .fa.green
        {
            color: darkgreen;
        }

        .search-result-wrap .search-result-item > div.zftsh-info-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.8;
        }
        .search-result-wrap .search-result-item > div.zftsh-info-wrapper > *
        {
            margin: 5px;
        }
    </style>
    <script type="text/javascript">
        window.onUserSearchAddItem = function(item, data){
            item.prepend(
                '<div class="user_status">' + (data.zftsh_academy_status_label ? ( '<span>' + data.zftsh_academy_status_label + '</span>') : '') +' <span class="profile_info">('
                + (
                    ['pupil_intramural','pupil_extramural'].includes(data.zftsh_academy_status) && parseInt(data.school_class)
                        ? data.school_class + ' класс'
                        : data.profile_status_label
                )
                + ')</span></div>'
            );
            item.attr('title', (data.gender == 1 ? 'Зарегистрарована ' : 'Зарегистрирован ') + data.creation_date);
            if (data.school_reference_status){
                item.find('.result-item-user-name').prepend(
                    '<i class="fa fa-check'+(data.school_reference_status == 2 ? ' green' : '')+'" title="'
                    +(data.school_reference_status == 2 ? 'Справка об обучении в школе подтверждена' : 'Справка об обучении в школе прикреплена, но ещё не подтверждена')
                    +'" onclick="event.preventDefault();window.open(\'/members/edit/profile/id/'+data.user_id+'/passport/#school_reference\');"></i>'
                );
            }
            if (data.member_code || data.region_code){
                item.append(
                    '<div class="zftsh-info-wrapper">'
                    + (data.member_code ? '<B title="Код ЗФТШ">' + data.member_code + '</B> ' : '')
                    + (data.region_code ? '<span title="Код региона">Рег. ' + data.region_code + '</span> ' : '')
                    + '</div>'
                );
            }
        };
    </script>
<? } ?>
<? if($this->viewer->getIdentity() && ($this->viewer->level_id === 1 || $this->viewer->level_id === 2)): ?>
    <input class="is_admin" type="hidden" value="1"/>
<? endif; ?>
    
<div class="search-wrap">
    <form class="main-search" id="user_search_container">
    <div class="search-form">
        <div class="search-wrap-inside">
            <input autocomplete="off" autofocus="autofocus" class="main-input" name="name" id="people_search_field" maxlength="70" placeholder="<?=$this->viewer()->hasMethodistRights() ? 'Поиск производится по ФИО, а также по ID участника, по коду участника Программ обучения' : $this->translate('Search Members');?>" type="search" value="<?=isset($_REQUEST['name'])?strip_tags($_REQUEST['name']):'';?>"/>
        </div>
        <button class="btn-search"></button>
        
        <div class="settings-search">
            <? if(!($this->viewer->getIdentity() && ($this->viewer->level_id === 1 || $this->viewer->level_id === 2))): ?><a class="advanced-search" href="/user_search"><span class="carat"></span> <?=$this->translate('Advanced search');?></a><? endif; ?>
            <a href="user_search?users=blocked" class="btn btn-link lock-user" style="display: none;"><?=$this->translate('Blocked');?></a>
        </div>
    </div>
    </form>
    
    <div class="search-filters"<? if(!($this->viewer->getIdentity() && ($this->viewer->level_id === 1 || $this->viewer->level_id === 2))): ?> style="display: none;"<? endif; ?>>
        <div class="search-filters-group">
            <label for="age_from" style="display: block;"><?=$this->translate('Age');?></label>
            <div class="search-filter filter-age">
                <div class="form-element custom-combobox">
                    <input autocomplete="off" class="select_box" data-value="<?=isset($_REQUEST['age_from'])?htmlspecialchars($_REQUEST['age_from']):''?>" id="age_from" name="age_from" placeholder="<?=$this->translate('From');?> &#9662;" readonly="true" type="text" value="<?=isset($_REQUEST['age_from'])?htmlspecialchars($_REQUEST['age_from']):''?>">
                    <div class="result_list" id="result_list_age_from" style="display:none;">
                        <ul>
                            <li class="active hover" data-value="0" onmousedown="selectItem(this, '', 0);" onmouseover="highlightItem(this);"><?=$this->translate('From');?></li>
                            <? for($i = 7; $i < 81; $i++): ?>
                                <li onmousedown="selectItem(this, '<?=$i;?>', <?=$i;?>);" onmouseover="highlightItem(this);"><?=$i;?></li>
                            <? endfor; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <span class="age_range">-</span>
            <div class="search-filter filter-age">
                <div class="form-element custom-combobox">
                    <input autocomplete="off" class="select_box" data-value="<?=isset($_REQUEST['age_to'])?htmlspecialchars($_REQUEST['age_to']):''?>" id="age_to" name="age_to" placeholder="<?=$this->translate('To');?> &#9662;" readonly="true" type="text" value="<?=isset($_REQUEST['age_to'])?htmlspecialchars($_REQUEST['age_to']):''?>">
                    <div class="result_list" id="result_list_age_to" style="display:none;">
                        <ul>
                            <li class="active hover" data-value="0" onmousedown="selectItem(this, '', 0);" onmouseover="highlightItem(this);"><?=$this->translate('To');?></li>
                            <? for($i = 7; $i < 81; $i++): ?>
                                <li data-value="<?=$i;?>" onmousedown="selectItem(this, '<?=$i;?>', <?=$i;?>);" onmouseover="highlightItem(this);"><?=$i;?></li>
                            <? endfor; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="search-filters-group ">
            <div class="search-filter filter-sex">
                <label class="filter-sex" for="gender"><?=$this->translate('Gender');?></label>
                <div class="form-element custom-combobox">
                    <input autocomplete="off" class="select_box" data-value="<?=isset($_REQUEST['gender'])?htmlspecialchars($_REQUEST['gender']):''?>" id="gender" name="gender" placeholder=" <?=$this->translate('_ANY_M');?> &#9662;" readonly="true" type="text" value="<?=isset($_REQUEST['gender'])?htmlspecialchars($_REQUEST['gender']):''?>">
                    </a>
                </div>
                <div class="result_list" id="result_list_gender" style="display:none;">
                    <ul>
                        <li class="active hover" data-value="" onmousedown="selectItem(this, '', 0);" onmouseover="highlightItem(this);">&nbsp;</li>
                        <li data-value="2" onmousedown="selectItem(this, '<?=$this->translate('Male');?>', 2);" onmouseover="highlightItem(this);"><?=$this->translate('Male');?></li>
                        <li data-value="1" onmousedown="selectItem(this, '<?=$this->translate('Female');?>', 1);" onmouseover="highlightItem(this);"><?=$this->translate('Female');?></li>
                    </ul>
                </div>
            </div>            
        </div>
        <div class="search-filters-group">
            <label class="filter-status" for="country"><?=$this->translate('Country');?></label>
            <div class="search-filter filter-status">
                <input autocomplete="off" class="select_box" data-value="" id="country" name="country" placeholder="<?=$this->translate('_NONE_SELECTED_F');?> &#9662;" readonly="true" type="text">
            </div>
            <div class="result_list" id="result_list_country" style="display: none;">
                <ul>
                    <li class="active hover" data-value="0" onmousedown="selectItem(this, '');" onmouseover="highlightItem(this);"><?=$this->translate('_NONE_SELECTED_F');?></li>                   
                    <? for($i = 0; $i < 18; $i++): ?>
                        <li data-value="<?=$this->countries[$i]['id'];?>" onmousedown="selectItem(this, '<?=$this->countries[$i][$this->country_name_key];?>', <?=$this->countries[$i]['id'];?>)" onmouseover="highlightItem(this);"><?=$this->countries[$i][$this->country_name_key];?></li>
                    <? endfor; ?>
                    <li data-value="<?=$this->countries[$i]['id'];?>" onmousedown="showAllCountries(this); return false;" onmouseover="highlightItem(this);"><?=$this->translate(' - Full list - ');?></li>
                </ul>
            </div>
        </div>
         <div class="search-filters-group region-search">
            <label class="filter-status" for="region"><?=$this->translate('Region');?></label>
            <div class="search-filter filter-status">
                <input autocomplete="off" class="select_box" id="region" name="region" placeholder="<?=$this->translate('_NONE_SELECTED_M');?> &#9662;" readonly="true" type="text">
            </div>
            <div class="result_list" id="result_list_region" style="display: none;">
                <ul>
                    <li class="active hover" data-value="0" onmousedown="selectItem(this, '');" onmouseover="highlightItem(this);"><?=$this->translate('_NONE_SELECTED_M');?></li>
                </ul>
            </div>
        </div>
        <div class="search-filters-group city-search">
            <label class="filter-status" for="city"><?=$this->translate('City');?></label>
            <div class="search-filter filter-status">
                <input autocomplete="off" class="select_box" data-value="0" id="city" name="city" placeholder="<?=$this->translate('_NONE_SELECTED_M');?> &#9662;" type="text">
            </div>
            <div class="result_list" id="result_list_city" style="display: none;">
                <ul>                    
                    <li class="active hover" data-value="0" onmousedown="selectItem(this, '');" onmouseover="highlightItem(this);"><?=$this->translate('_NONE_SELECTED_M');?></li>
                </ul>
            </div>
        </div>
        <div class="search-filters-group search-filter-online">
            <input class="search-filter filter-onlain" id="online" name="online" type="checkbox"> <label for="online"><?=$this->translate('online');?></label>
        </div>
        <? if( $this->viewer()->getIdentity() && $this->viewer()->hasMethodistRights() ){
                $DS = Engine_Api::_()->core()->getNowDomainSettings();
                $zftshStatuses = Engine_Api::_()->zftsh()->getAcademyStatusMultiOptions();
                $zftshStatuses['none'] = 'Вне программ обучения';
                $zftshStatuses['teacher_approved'] = 'Учитель, подтверждён';
                $zftshStatuses['teacher_new'] = 'Учитель, новый';
        ?>
            <input class="select_box search-filter" id="email" name="email" placeholder="Email" autocomplete="off" type="search" value="<?=isset($_REQUEST['email'])?htmlspecialchars($_REQUEST['email']):'';?>"/>
            <input class="select_box search-filter" id="phone" name="phone" placeholder="Телефон"  autocomplete="off" title="" type="search" value="<?=isset($_REQUEST['phone'])?htmlspecialchars($_REQUEST['phone']):''?>"/>
            <? if (!empty($DS['zftshDefaults'])){ ?>
                <?=$this->formSelect('school_reference_status', null, ['class' => 'search-filter', 'style' => 'width: 180px', 'autocomplete' => 'off'], Engine_Api::_()->zftsh()->getSchoolReferenceMultiOptions() )?>
            <? } ?>
            <? if (!empty($DS['academyEnabled'])){ ?>
                <?=$this->formSelect($DS['academyNamespace'], null, ['class' => 'search-filter', 'style' => 'width: 180px', 'autocomplete' => 'off'], $zftshStatuses )?>
            <? } ?>
        <? } ?>
    </div>
</div>

<div class="search-result-wrap"></div>