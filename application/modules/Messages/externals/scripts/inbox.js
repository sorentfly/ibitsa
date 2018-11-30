jQuery(function($){
	'use strict';

	var win = jQuery(window);
	var wrapper = jQuery('.messages_list');
	var loadingIndicator = jQuery('.browse-filter-target-loading');
	var isLoading = false;
	var filter = jQuery('form#filterForm');
	var pageInp = jQuery('[name=page]', filter);

	/*Message body - emulating link: click, and middle mouse click*/
	wrapper.on('click', '.messages_list_info_body', function(){
		document.location = jQuery(this).data('href');
	});
	wrapper.on('mousedown', '.messages_list_info_body', function(e) {
		if(e.which != 2 ) return;
		e.preventDefault();
		window.open(location.protocol+'//' + location.host + jQuery(this).data('href'));
	});

	/*Messages vertical pagination*/
	var reloadList = function(){
		if(isLoading) return;
		
		var currentPage = parseInt(pageInp.val()) + 1;
		pageInp.val(currentPage);
					
		loadingIndicator.show();
		isLoading = true;
		var data = filter.serializeArray();
		jQuery.get(filter.attr('action'), data, function(d){
			loadingIndicator.hide();
			if(d == 'stop') {
				wrapper.find('[data-end-of-list]').data('end-of-list', 'yes');
			} else {
				wrapper.append(d);
				loadingIndicator = jQuery('.browse-filter-target-loading');
			}
			isLoading = false;
			window.history.pushState(null, null, filter.attr('action') + '?' + jQuery.param(data));
		});
	};
	
	
	win.scroll(function(e){				
		var bottomEdge = wrapper.offset().top + wrapper.height() - 400;
		if(win.scrollTop() + win.height() > bottomEdge){
			if(wrapper.find('[data-end-of-list]').data('end-of-list') == 'yes') return;
			
			reloadList();			
		}
	});


	jQuery('select#category').simpleselect	();

	var filterSubmit = function(event){
		event.preventDefault();
		pageInp.val(0);
		wrapper.find('[data-end-of-list]').data('end-of-list', '').html('');
		reloadList();
		return false;		
	};
	
	jQuery('[name=pupil_class]', filter).change(filterSubmit);	
	jQuery('[name=only_unreaded]', filter).change(filterSubmit);
});