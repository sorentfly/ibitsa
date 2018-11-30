jQuery(function($){
	'use strict';

	var form = jQuery('#global_page_messages-messages-user-list-edit form.add-user-form');
	var ul = jQuery('#global_page_messages-messages-user-list-edit ul.user-list');
	var inpSubmit = jQuery('[type=submit]', form);
	var inpUserId = jQuery('#add_user_id', form);
	
	
	new Autocompleter.Request.JSON('adduserName', '/user/friends/suggest', {
        'minLength': 1,
        'delay' : 250,
        'selectMode': 'pick',
        'autocompleteType': 'tag',
        'multiple': false,
        'className': 'message-autosuggest',
        'filterSubset' : true,
        'tokenFormat' : 'object',
        'tokenValueKey' : 'label',
        'injectChoice': function(token){
          if(token.type == 'user'){
            var choice = new Element('li', {
              'class': 'autocompleter-choices',
              'html': token.photo,
              'data-name':token.label,
              'data-id':token.id
            });
            new Element('div', {
              'html': this.markQueryValue(token.label),
              'class': 'autocompleter-choice'
            }).inject(choice);
            this.addChoiceEvents(choice).inject(this.choices);
            choice.store('autocompleteChoice', token);
          }
          else {
            var choice = new Element('li', {
              'class': 'autocompleter-choices friendlist',
              'data-name':token.label,
              'data-id':token.id
            });
            new Element('div', {
              'html': this.markQueryValue(token.label),
              'class': 'autocompleter-choice'
            }).inject(choice);
            this.addChoiceEvents(choice).inject(this.choices);
            choice.store('autocompleteChoice', token);
          }            
        },        
        onChoiceSelect : function(li){
        	submiting = false;
        	inpUserId.val(jQuery(li).data('id'));
        	form.submit();        	
        }
      });
	
	
	var submiting = false;
	var maxRecipients = ul.data('max-recipients');
	inpSubmit.data('text', inpSubmit.attr('value'));
	
	form.submit(function(e){
		e.preventDefault();
		if(submiting) return false;
		var val = inpUserId.val();
		if(val.trim() == '') {
			console.log("Пустое значение");
			return false;
		}
		if( jQuery('li[data-user-id='+val+']', ul).length != 0) {
			console.log("Пользователь уже добавлен");
			return false;
		}
		if(jQuery('li', ul).not('.deleted').length >= maxRecipients) {
			console.log("Максимум");
			return false;
		}
		submiting = true;
		inpSubmit.attr('value', inpSubmit.data('loading-text'));		
		var that = jQuery(this);		
		console.log(that.attr('action'), {'add_user_id': val});
		jQuery.post(that.attr('action'), {'add_user_id': val}, function(d){
			inpSubmit.attr('value', inpSubmit.data('text'))
			ul.prepend(d);
		})
		return false;
	});
	
	jQuery(ul).on('click', '.user-undo-link, .user-remove-link', function(e){
		e.preventDefault();
		var that = jQuery(this);
		that.addClass('fa-spinner');
		jQuery.get(that.attr('href'), function(d){
			that.removeClass('fa-spinner');
			that.parents('li').replaceWith(d);
		});
		return false;
	});
	
})