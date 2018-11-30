<? if($this->subject()->photo_id !== null ): ?>
    <div>
        <?=$this->itemPhoto($this->subject(), 'thumb.profile', '', array('id' => 'lassoImg')); ?>
    </div>
    <br/>
    <div id="preview-thumbnail" class="preview-thumbnail">
        <?=$this->itemPhoto($this->subject(), 'thumb.icon', '', array('id' => 'previewimage')) ?>
    </div>
    <div id="thumbnail-controller" class="thumbnail-controller">
        <? if ($this->subject()->getPhotoUrl()): ?>
            <a href="javascript:void(0);" onclick="lassoStart();"><?=$this->translate('Edit Thumbnail');?></a>
        <? endif; ?>
    </div>
<? endif; ?>