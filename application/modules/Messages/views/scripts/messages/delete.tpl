<div class='global_form_popup'>
  <form action="<?=$this->url() ?>" novalidate="novalidate" method="POST">
    <div>
      <h3>
        <?=$this->translate('Delete Message(s)?') ?>
      </h3>

      <p>
        <?=$this->translate('Are you sure that you want to delete the selected message(s)? This action cannot be
        undone.') ?>
      </p>

      <p>
        <input type="hidden" name="message_ids" value="<?=$this->message_ids?>"/>
        <input type="hidden" name="place" value="<?=$this->place?>"/>
        <button type='submit'><?=$this->translate('Delete') ?></button>
        <?=$this->translate('or') ?>
        <a href="javascript:void(0);" onclick="parent.Smoothbox.close();"><?=$this->translate('cancel') ?></a>
      </p>
    </div>
  </form>
</div>
