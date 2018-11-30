
/* $Id: composer_twitter.js 9572 2011-12-27 23:41:06Z john $ */



(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;



Composer.Plugin.Twitter = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'twitter',

  options : {
    title : 'Publish this on Twitter',
    lang : {
        'Publish this on Twitter': 'Publish this on Twitter'
    },
    requestOptions : false,
  },

  initialize : function(options) {
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.parent(options);
  },

  attach : function() {
    this.elements.spanToggle = new Element('span', {
      'class' : 'composer_twitter_toggle',
      'href'  : 'javascript:void(0);',
      'events' : {
        'click' : this.toggle.bind(this)
      }
    });

    this.elements.formCheckbox = new Element('input', {
      'id'    : 'compose-twitter-form-input',
      'class' : 'compose-form-input',
      'type'  : 'checkbox',
      'name'  : 'post_to_twitter',
      'style' : 'display:none;'
    });
    
    this.elements.spanTooltip = new Element('span', {
      'for' : 'compose-twitter-form-input',
      'class' : 'composer_twitter_tooltip',
      'html' : this.options.lang['Publish this on Twitter']
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
    $('compose-twitter-form-input').set('checked', !$('compose-twitter-form-input').get('checked'));
    event.target.toggleClass('composer_twitter_toggle_active');
    composeInstance.plugins['twitter'].active = true;
    setTimeout(function(){
      composeInstance.plugins['twitter'].active = false;
    }, 300);
  }
});



})(); // END NAMESPACE
