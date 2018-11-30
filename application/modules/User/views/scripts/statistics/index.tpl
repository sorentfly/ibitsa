<?
    $this->headScript()
                        ->appendFile(JQUERY_UI_LIB)
                        ->appendFile('/application/modules/Core/externals/scripts/datetime.js') /* jQuery Календарик */
                        ->appendFile('/application/modules/Core/externals/scripts/notification.js');

$this->headLink()->appendStylesheet('/application/modules/Core/externals/styles/datetimepicker.css');
?>
<h1>Статистика по регистрациям</h1>
<style>
    .referrals_link {
        font-size: 2em;
    }

    .referrals_details {
        border-collapse: collapse;
        font-size: 0.7em;
        margin: 20px auto;
    }

    .referrals_details th {
        background-color: #F5F7F8;
        height: 25px;
    }

    .referrals_details th,
    .referrals_details td {
        border: 1px solid #DEE4E8;
        font-size: 1.2em;
        padding: 2px 4px;
        text-align: center;
    }

    .AJAX_LOADER_BLOCK {
        margin: 30px auto;
        opacity: .75;
        text-align: center;
    }

    .stats-filter {
        box-sizing: border-box;
        margin: 30px auto;
        width: 1000px;
    }

    .stats-cell {
        text-align: center;
    }

    .filter-field {
        box-sizing: border-box;
        width: 100%;
    }
</style>
<script>
    /* Параметры календарика */
    var datepickerParams = {
        dayOfWeekStart: 1,
        format: "d.m.Y",
        formatDate: "d.m.Y",
        i18n: {
            ru: {
                dayOfWeek: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
                months: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"]
            }
        },
        lang: "ru",
        maxDate: String((new Date()).getDate()) + "." + String((new Date()).getMonth() + 1) + "." + String((new Date()).getFullYear()),
        timepicker: false,
        scrollMonth:false
    };


    if("jQuery" in window) {
        jQuery.noConflict();

        jQuery(document).ready(function () {
            var statsContainer, AJAX_LOADER_BLOCK;

            statsContainer = document.getElementsByClassName("referrals_details").item(0);
            AJAX_LOADER_BLOCK = document.getElementsByClassName("AJAX_LOADER_BLOCK").item(0);

            if (statsContainer !== null && statsContainer.style.display === "none") {
                statsContainer.style.display = "";
            }

            if (AJAX_LOADER_BLOCK !== null && AJAX_LOADER_BLOCK.parentNode) {
                AJAX_LOADER_BLOCK.parentNode.removeChild(AJAX_LOADER_BLOCK);
            }

            jQuery("#start_reg_date, #finish_reg_date").datetimepicker(datepickerParams);
        });
    }
</script>

<form>
    <table class="stats-filter">
        <tbody>
            <tr>
                <th class="stats-cell" colspan="6">Фильтр по статистике</th>
            </tr>
            <tr>
                <td class="stats-cell" colspan="6">
                    <label for="reg_site">Регистрационный сайт</label>
                    <select id="reg_site" name="reg_site">
                        <option value="">Не важно</option>
                        <option label="abitu.net"<? if($_REQUEST['reg_site'] === 'abitu.net'): ?> selected="selected"<? endif; ?> value="abitu.net">abitu.net</option>
                        <option label="<?=ABITU_SITE?>"<? if($_REQUEST['reg_site'] === ABITU_SITE): ?> selected="selected"<? endif; ?> value="<?=ABITU_SITE?>"><?=ABITU_SITE?></option>
                        
                        <option label="<?=SCHOOL_OLYMPICS_SITE?>"<? if($_REQUEST['reg_site'] === SCHOOL_OLYMPICS_SITE): ?> selected="selected"<? endif; ?> value="<?=SCHOOL_OLYMPICS_SITE?>"><?=SCHOOL_OLYMPICS_SITE?></option>
                        <option label="<?=MASTER_OLYMPICS_SITE?>"<? if($_REQUEST['reg_site'] === MASTER_OLYMPICS_SITE): ?> selected="selected"<? endif; ?> value="<?=MASTER_OLYMPICS_SITE?>"><?=MASTER_OLYMPICS_SITE?></option>
                    </select>

                    <input id="row_count" name="row_count" min="0" placeholder="Количество строк" type="number" value="<?=$_REQUEST['row_count'];?>"/>

                    <label><input <? if($_REQUEST['navigator_member'] === 'on'): ?>checked="checked"<? endif; ?> name="navigator_member" style="display: inline;float: none;" type="checkbox"/> Вступил в событие «Навигатор поступления — 2015»</label>
                </td>
            </tr>
            <tr>
                <td><input class="filter-field" id="start_reg_date" name="start_reg_date" placeholder="Начало интервала" type="text" value="<?=$this->start_reg_date;?>"/></td>
                <td><input class="filter-field" id="finish_reg_date" name="finish_reg_date" placeholder="Конец интервала" type="text" value="<?=$this->finish_reg_date;?>"/></td>
                <td><input class="filter-field" id="utm_term" name="utm_term" placeholder="utm_term" type="text" value="<?=$_REQUEST['utm_term'];?>"/></td>
                <td><input class="filter-field" id="utm_source" name="utm_source" placeholder="utm_source" type="text" value="<?=$_REQUEST['utm_source'];?>"/></td>
                <td><input class="filter-field" id="utm_medium" name="utm_medium" placeholder="utm_medium" type="text" value="<?=$_REQUEST['utm_medium'];?>"/></td>
                <td><input class="filter-field" id="utm_campaign" name="utm_campaign" placeholder="utm_campaign" type="text" value="<?=$_REQUEST['utm_campaign'];?>"/></td>
            </tr>
            <tr>
                <td class="stats-cell" colspan="6"><button type="submit">Применить фильтр</button> <a class="button-link" href="/statistics/">Сбросить</a></td>
            </tr>
        </tbody>
    </table>
</form>
<? if(count($this->list) !== 0): ?>
    <div class="AJAX_LOADER_BLOCK"><img alt="Загрузка ... " height="16" src="/application/modules/Core/externals/images/progress_gray_inv.gif" title="Загрузка ... " width="165"></div>
    <table class="referrals_details" style="display: none;">
        <thead>
            <tr>
                <th>№</th>
                <th>ID</th>
                <th>ФИО</th>
                <th>IP регистрации</th>
                <th>Дата</th>
                <th>utm_term</th>
                <th>utm_source</th>
                <th>utm_medium</th>
                <th>utm_campaign</th>
                <th>Сайт регистрации</th>
                <th>Регистрация через соц. сеть</th>
                <th>Подтверждён</th>
            </tr>
        </thead>
        <tbody>
            <? for($i = 0; $i < count($this->list); $i++): ?>
                <tr>
                    <td><?=($i+1);?></td>
                    <? foreach ($this->list[$i] as $key => $value): ?>
                        <? switch($key):
                            case 'ip': ?>
                                <td><?=$value;?><br/><?=$this->list[$i]['location'];?></td>
                            <? break; ?>
                            <? case 'name': ?>
                                <td><a href="/profile/<?=$this->list[$i]['user_id'];?>" target="_blank"><?=$value;?></a></td>
                            <? break; ?>
                            <? case 'registration_site':
                                case 'social_reg': ?>
                                <td><a href="http://<?=$value;?>" target="_blank"><?=$value;?></a></td>
                            <? break; ?>
                            <? default: ?>
                                <? if($key !== 'location'): ?>
                                    <td><?=$value;?></td>
                                <? endif; ?>
                        <? endswitch; ?>
                    <? endforeach; ?>
                </tr>
            <? endfor; ?>
        </tbody>
    </table>
<? else: ?>
    <h3>Нет людей для отображения</h3>
<? endif; ?>
