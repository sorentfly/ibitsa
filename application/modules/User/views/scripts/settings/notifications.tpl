<script>
    jQuery(function($) {
        $('#spam').change(function () {
            $(this).parents('form:first')
                .find('input[type=checkbox]:not(#spam)')
                .attr("disabled", $(this).is(':checked') ? 'disabled' : null);
        }).trigger('change');
    });
</script>
<?php echo $this->form->render($this) ?>
