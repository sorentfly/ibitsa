<div class="clear">
    <?=$this->navigation()
    ->menu()
    ->setContainer($this->navigation)
    ->setUlClass('admin_levels_tabs')
    ->render()
    ?>

    <div class="settings">
        <?=$this->form->setAttrib('novalidate', 'novalidate')->render($this); ?>
    </div>
</div>