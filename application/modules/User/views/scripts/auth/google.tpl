
<div class="tip">
  <span>
    <?php if( $this->error == 'access_denied' ): ?>
      <?php echo $this->translate('You must grant access to login using Google.') ?>
    <?php else: ?>
      <?php echo $this->translate('An unknown error has occurred.') ?>
    <?php endif ?>
  </span>
</div>
