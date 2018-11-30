<div class="headline">
  <h2>
    <?=$this->translate('My Messages') ?>
  </h2>
  <div class="tabs">
    <?=$this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render(); // Render the menu
    ?>
  </div>
</div>
