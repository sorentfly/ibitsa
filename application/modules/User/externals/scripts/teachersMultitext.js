jQuery(function($){
  let wrapper = $('#teachers-wrapper');
  if (!wrapper.length) return;
  
  /** @param {jQuery} textbox */
  let hasAnyValue = false;
  let addSubjectSelect = function(textbox){
    let html = '<select class="mulitext-theme-select"><option value="">-выберите предмет-</option>';
    for(let subj in window.subjectMultiOptions){if (window.subjectMultiOptions.hasOwnProperty(subj)){
        html+='<option value="'+subj+'">'+window.subjectMultiOptions[subj].label+'</option>';
    }}
    html += '</select>';
    $(html).insertBefore(textbox);

    let value = textbox.val();
    if (value) hasAnyValue = true;
    if (value.indexOf('&')!=-1){
        value = value.split('&');
        textbox.val(value[1]);
        textbox.prev('select').val(value[0]);
    }
  };

  wrapper.find('input[name="teachers[]"]').each(function(){
      jQuery(this).attr('placeholder', 'Введите ФИО учителя');
      addSubjectSelect(jQuery(this));
  });
  if (!hasAnyValue){
      wrapper.hide().before('<div style="text-align:center;font-size: 1.2em;">' +
          '<A HREF="javascript:void(0);" onclick="jQuery(this).parent().slideUp();jQuery(\'#teachers-wrapper\').slideDown();">Заполнить информацию об учителях <i class="fa fa-chevron-down"></i></A>' +
      '</div>');
  }

  wrapper.find('[data-action="multitext-add-line"]').click(function(){
    setTimeout(function(){
      let lastInput = wrapper.find('input[name="teachers[]"]:last');
      addSubjectSelect(lastInput);
    },30);
  });
  /**
   * @param {jQuery} sending_form
   * @param {FormData} formData
   */
  window.beforeFieldsSendedHook = function(sending_form, formData){
    if (!$.contains( sending_form[0], wrapper[0])) return;
    if (typeof formData.delete == 'function') formData.delete('teachers[]');
    wrapper.find('input[name="teachers[]"]').each(function(){
        let value = $(this).val();
        let subject = $(this).prev('select').val();
        if (value) formData.append('teachers[]', subject ? (subject + '&' + value) : value);
    });
  };
});