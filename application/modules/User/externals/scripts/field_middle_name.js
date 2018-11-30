jQuery(function($){
    let middleName = $('#middle_name');
    let isEditing = window.location.pathname.indexOf('/signup')!=0;

    if (!middleName.length) return;

    middleName.after('<div><input type="checkbox" id="middle_name_enabler"'+(isEditing && !middleName.val() ? ' checked' : '')+'> <span style="opacity: 0.7;">'+en4.core.language.translate('I have no middle name')+'</span></div>');
    $('#middle_name_enabler').change(function(){
        if ($(this).is(':checked')) middleName.removeAttr('required').attr('readonly', 'readonly').val('');
        else middleName.attr('required', 'required').removeAttr('readonly');
    }).trigger('change');

    if (isEditing){
        $('label[for="middle_name"]').append(' <b class="asterisk">*</b>');
    }
});