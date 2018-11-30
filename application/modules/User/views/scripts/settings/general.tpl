<div class="global_form">
  <? if ($this->form->saveSuccessful): ?>
    <h3><?=$this->translate('Settings were successfully saved.');?></h3>
  <? endif; ?>
  <?=$this->form->render($this); ?>
</div>