
/* $Id: composer_facebook.js 9576 2011-12-29 01:15:12Z john $ */



(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;



Composer.Plugin.Facebook = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'facebook',

  options : {
    title : 'Publish this on Facebook',
    lang : {
        'Publish this on Facebook': 'Publish this on Facebook'
    },
    requestOptions : false
  },

  initialize : function(options) {
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.parent(options);
  },

  attach : function() {
    this.elements.spanToggle = new Element('span', {
      'class' : 'composer_facebook_toggle',
      'href'  : 'javascript:void(0);',
      'events' : {
        'click' : this.toggle.bind(this)
      }
    });

    this.elements.formCheckbox = new Element('input', {
      'id'    : 'compose-facebook-form-input',
      'class' : 'compose-form-input',
      'type'  : 'checkbox',
      'name'  : 'post_to_facebook',
      'style' : 'display:none;'
    });
    
    this.elements.spanTooltip = new Element('span', {
      'for' : 'compose-facebook-form-input',
      'class' : 'composer_facebook_tooltip',
      'html' : this.options.lang['Publish this on Facebook']
    });

    this.elements.formCheckbox.inject(this.elements.spanToggle);
    this.elements.spanTooltip.inject(this.elements.spanToggle);
    this.elements.spanToggle.inject($('compose-menu'));

    //this.parent();
    //this.makeActivator();
    return this;
  },

  detach : function() {
    this.parent();
    return this;
  },

  toggle : function(event) {
    $('compose-facebook-form-input').set('checked', !$('compose-facebook-form-input').get('checked'));
    event.target.toggleClass('composer_facebook_toggle_active');
    composeInstance.plugins['facebook'].active=true;
    setTimeout(function(){
      composeInstance.plugins['facebook'].active=false;
    }, 300);
  }
});



})(); // END NAMESPACE
