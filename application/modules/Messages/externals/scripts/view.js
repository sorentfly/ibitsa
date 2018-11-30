jQuery(function($){
	'use strict';

	jQuery('#extendedSwitcher').click(function(){
		initTinymce_full('#body');
	  	$(this).hide();
	});

	
	var ul = jQuery('ul.message_view');
	var newMessages = jQuery('.message_view_new', ul);
	var viewedUrl =  ul.data('viewed-url');
	setTimeout(function(){
		jQuery.get(viewedUrl);
		newMessages.removeClass('message_view_new');
	}, 5000);
	
	if(newMessages.length > 0){
		var scrollTop = newMessages[0].offset().top;
	} else {
		var scrollTop = jQuery('#messages_form_reply').offset().top;
	}
	jQuery(window).scrollTop(scrollTop);
	
	jQuery('.message_delete_link', ul).click(function(e){
		e.preventDefault();
		var deleteLink = $(this);
		var li = deleteLink.parents('li');		
		var deleteUrl = deleteLink.attr('href');
		li.addClass('deleting');		
		jQuery.get(deleteUrl, function (d){
			if(d == 'OK'){
				li.slideUp(function(){$(this).remove()});				
				return;				
			} else {
				if(d.search('Ошибка') == 0) {
					var msg = jQuery('<span class="message_delete_error"><i class="fa fa-warning"></i>' + d + '</span>');
				} else {
					var msg = jQuery('<span class="message_delete_info"><i class="fa fa-warning"></i>' + d + '</span>');
				}
				deleteLink.after(msg).hide();
				li.removeClass('deleting');
				setTimeout(function(){
					msg.fadeOut(300, function(){$(this).remove();})
				}, 3000)
			}
			
		});
		return false;
	});
	
	
	jQuery('.list-edit-link').click(function(e){
		e.preventDefault();
		Smoothbox.open(this, {width: 500, height: 500, autoResize: false, onClose: function(){
			document.location.reload();  			
		}});
		return false;
	});
	
	
	jQuery(ul).on('click', '.more-link', function(e){
		e.preventDefault();
		var that = jQuery(this);
		that.append('<i class="fa fa-spinner"></i>');
		jQuery.get(that.attr('href'), function(d){
			var moreLi = that.parents('li');
			var firstLi = moreLi.next('li');
			var offsetOld = firstLi.offset().top;
			moreLi.replaceWith(d);
			var offsetNew = firstLi.offset().top;
			jQuery(window).scrollTop(offsetNew - offsetOld);
		});
		return false;
		
	});
})