<div>
    <h2 style="text-align: center;">
        <?=$this->translate('Verification Email');?>
    </h2>

    <?php if( $this->error ): ?>
        <p>
          <?=$this->translate($this->error); ?>
        </p>
    <br/>
        <h3>
          <?php echo $this->htmlLink(array('route' => 'default'), $this->translate('Back')) ?>
        </h3>
    <?php else: ?>
        <div class="blue_border_message">
        <?=$this->translate('_RESEND_MESSAGE', $this->resend_email, $this->current_user_id ); ?>
      </div>
    <?php endif; ?>
</div>
