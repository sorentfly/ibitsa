<div>
    <?php if( $this->status ): ?>
    <div class="blue_border_message">
      <?=$this->translate('Your account has been verified. Please wait to be redirected or click %s to login.', $this->htmlLink('/members/edit/profile/', $this->translate('here'))) ?>
    </div>
    <script type="text/javascript">
        setTimeout(function() {
          parent.window.location.href = "/members/edit/profile/";
        }, 5000);
    </script>
    <?php else: ?>
      <div class="blue_border_message error">
        <span>
    <?=$this->translate($this->error); ?>
        </span>
      </div>
    <?php endif; ?>
</div>
