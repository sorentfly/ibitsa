
<div style="padding-top: 20px; padding-left: 20px;">

  <?php switch( $this->file->extension ): case 'netbeans-hack': ?>
    <?php case 'jpg': case 'jpeg': case 'gif': case 'png': ?>
      <?php echo $this->htmlImage($this->file->map()) ?>
    <?php break; ?>

    <?php default: ?>
      <?php echo $this->translate('Unknown file extension: %s', $this->file->extension) ?>
    <?php break; ?>
  <?php endswitch; ?>

</div>