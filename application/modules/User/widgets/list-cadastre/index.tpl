<?php
$this->headLink()->appendStylesheet('/application/modules/Core/externals/styles/notification.css'); /* Стиль для уведомлений */
$this->headScript()->appendFile(JQUERY_UI_LIB);
$this->headScript()->appendFile('/application/modules/Core/externals/scripts/notification.js'); /* Виджет для уведомлений */
$this->headScript()->appendFile('/application/modules/Core/externals/scripts/classList.min.js'); /* Расширение прототипа html элементов */
$this->headScript()->appendFile('/application/modules/Olympic/externals/scripts/cad/cadastre_recalculate.js');

$isSelf = $this->subject && $this->subject->isSelf($this->viewer);
?>
<style type="text/css">
    .cadastre-widjet
    {
        background-color: #F7F7F7;
        border-top: 1px solid #E6E6E6;
        border-bottom: 1px solid #E6E6E6;
        vertical-align: top;
        display: flex;
        justify-content: space-between;
        align-items: center;
        height:inherit;
        margin:0;
        display:inline-flex;
        width:100%;
    }
    .cadastre-widjet > img
    {
        margin-right: 25px;
    }
    .cadastre-common-row
    {
        text-align: center;
        display:inline-block;
        max-width:510px;
        padding-top:10px;
        padding-bottom:10px;
        flex-grow:1;
    }
    .cadastre-scores
    {
        font-size: 50px;
        opacity:0.8;
        padding-left: 40px;
        padding-right: 30px;
        text-align: center;
    }
    .cadastre-management
    {
        min-width: 160px;
        height:90px;
        display:inline-block;
        padding-top: 10px;
        border-left:1px solid #E6E6E6;
        padding-left:15px;
        padding-right:15px;
        padding-top:18px;
    }
    .cadastre-management button
    {
        width:100%;
    }
    .cadastre-prize img
    {
        position:absolute;
    }
    .cadastre-detail
    {
        font-size: 1.1em;
        font-weight: normal;
        line-height: 28px;
        white-space: nowrap;
        display:block;
    }
    .cadastre-label
    {
        font-size:0.8em;
    }
    @media (max-width: 750px) {
        .cadastre-label
        {
            display: none;
        }
    }

    .cadastre-warning{
        text-align: center;
        margin: 4px 10px;
    }
</style>
<div class="cadastre-widjet">
    <div class="cadastre-common-row"<?=!$isSelf?' style="width:100%;"':''?>>
        <?php $this->cadastre['points'] = empty($this->cadastre['points']) ? 0 : $this->cadastre['points']; ?>
        <span class="cadastre-scores"><?=$this->pointViewAble ?  $this->translate(array('%s point', '%s points', $this->cadastre['points']),$this->locale()->toNumber($this->cadastre['points']))  : '<i style="font-size:0.8em;color:#cacaca;">Баллы скрыты</i>';?></span>
    </div>
    <? if(!empty($this->cadastre['medalImage']) ){ ?>
        <img alt="<?=$this->cadastre['medalTitle'];?>" height="60" src="<?=$this->cadastre['medalImage'];?>" title="<?=$this->cadastre['medalTitle'];?>" width="45">
    <? }; ?>
    <?php if ($isSelf){ ?>
    <div class="cadastre-management">
        <button title="Пересчитать баллы кадастра" onclick="cadastreRefreshAction(event);" class="cadastre-recalculate">Пересчитать</button><BR>
        <a class="cadastre-detail" href="/portfolio/" target="_blank" title="Подробная информация о ваших мероприятиях">Детализация кадастра</a>
    </div>
    <?php } ?>
</div>