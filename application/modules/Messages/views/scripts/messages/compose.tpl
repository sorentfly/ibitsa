<?
  $this->headScript()
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/tinymce4/tinymce.min.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/mdetect/mdetect' . ( APPLICATION_ENV != 'development' ? '.min' : '' ) . '.js')
    ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Core/externals/scripts/composer.js')
    ->appendFile($this->baseUrl() . '/application/modules/Zftsh/externals/scripts/autocompleter.js');
  $this->headTitle('Новый диалог');
?>
<? if (!$this->isSmoothbox && $this->hasAcademyMenu){?>
    <?=$this->content()->renderWidget('zftsh.profile-tabs')?>
<? } ?>
<script>

  // Populate data
  var maxRecipients = <?=sprintf("%d", $this->maxRecipients) ?> || 10;
  var to = {
    id : false,
    type : false,
    guid : false,
    title : false
  };
  var isPopulated = false;

  <? if( $this->isPopulated && !empty($this->toObject) ){ ?>
    isPopulated = true;
    <? if (is_array($this->toObject)) {
      $to = array_map(function($item){
        return [
            'id'    => $item->getIdentity(),
            'type'  => $item->getType(),
            'guid'  => $item->getGuid(),
            'title' => $item->getTitle()
        ];
      }, $this->toObject);
    ?>
      to = <?=json_encode($to)?>;
    <? }else { ?>
      to = {
        id : <?=sprintf("%d", $this->toObject->getIdentity()) ?>,
        type : '<?=$this->toObject->getType() ?>',
        guid : '<?=$this->toObject->getGuid() ?>',
        title : '<?=$this->string()->escapeJavascript($this->toObject->getTitle()) ?>'
      };
    <? } ?>
  <? } ?>
  
  function removeFromToValue(id) {
    // code to change the values in the hidden field to have updated values
    // when recipients are removed.
    var toValues = $('toValues').value;
    var toValueArray = toValues.split(",");
    var toValueIndex = "";

    var checkMulti = id.search(/,/);

    // check if we are removing multiple recipients
    if (checkMulti!=-1){
      var recipientsArray = id.split(",");
      for (var i = 0; i < recipientsArray.length; i++){
        removeToValue(recipientsArray[i], toValueArray);
      }
    }
    else{
      removeToValue(id, toValueArray);
    }

    // hide the wrapper for usernames if it is empty
    if ($('toValues').value==""){
      $('toValues-wrapper').setStyle('height', '0');
    }

    $('to').disabled = false;
  }

  function removeToValue(id, toValueArray){
    for (var i = 0; i < toValueArray.length; i++){
      if (toValueArray[i]==id) toValueIndex =i;
    }
    toValueArray.splice(toValueIndex, 1);
    $('toValues').value = toValueArray.join();
    adjustTitleElement(toValueArray.length);
  }

  en4.core.runonce.add(function() {

    if( !isPopulated ) { // NOT POPULATED

      var toEl = jQuery('#to');
      toEl.attr('autocompleteURL', <?=json_encode($this->url(['mode' => 'friends'], 'zftsh_academy_membership_suggest', true))?>);
      window.autocompleter(toEl, function () {
        length = $('toValues').value.split(',').length;
        if( length >= maxRecipients ){
          $('to').disabled = true;
        }
        adjustTitleElement(length);
      }, {width: '400px', autocompleteType: 'message'});


      
      new Composer.OverText($('to'), {
        'textOverride' : '<?=$this->translate('Start typing...') ?>',
        'element' : 'label',
        'isPlainText' : true,
        'positionOptions' : {
          position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          offset: {
            x: ( en4.orientation == 'rtl' ? -7 : 7 ),
            y: 12
          }
        }
      });

    } else {
        var addElement = function(item){
            var myElement;
            myElement = new Element("span", {
                'id' : 'tospan' + item.id,
                'class' : 'receiver tag tag_' + item.type,
                'html' :  item.title /* + ' <a href="javascript:void(0);" ' +
                  'onclick="this.parentNode.destroy();removeFromToValue("' + toID + '");">x</a>"' */
            });
            var sendToElement = new Element('input', {
                'type'  : 'hidden',
                'name'  : 'send_to[]',
                'value' : item.id
            });
            $('messages_compose').appendChild(sendToElement);
            $('to-element').appendChild(myElement);
        };

        var receivers = 0;
        var receivers_limit = "<?= $this->receivers_limit ?>";
        if ( to instanceof Array ) {
            for ( var i = 0; i < to.length; i++ ) {
                if ( i + 1 > receivers_limit && receivers_limit > 0 ) {
                    alert('Вы не можете отправлять сообщения более ' + receivers_limit + ' пользователям за раз!');
                    break;
                }
                addElement(to[i]);
            }
            receivers = i;
        } else {
            if ( to instanceof Object ) receivers = 1;
            addElement(to);
        }

        var receiversElement = new Element('a', {
            'href' : 'javascript:void(0)',
            'id' : 'show_receivers',
            'text' : receivers + ' получателей',
            'onclick' : 'jQuery(this).hide(); jQuery(".receiver").show();'
        });

        if ( receivers < 10 ) {
            receiversElement.setStyle('display', 'none')
        } else {
            $('to-element').getElements('.receiver').setStyle('display', 'none');
        }

        $('to-element').appendChild(receiversElement);

        $('to-wrapper').setStyle('height', 'auto');

        // Hide to input?
        $('to').setStyle('display', 'none');
        $('toValues-wrapper').setStyle('display', 'none');
    }

    
  	
  });

  	var titleElementWrp = null;
	function adjustTitleElement(recipientsCount){
	  	if(recipientsCount >= 2){
			titleElementWrp.removeClass('hidden-imp');
	  	} else {
	  		titleElementWrp.addClass('hidden-imp');
	  	}
	} 

  	jQuery(function($){
  		titleElementWrp = $('#title-wrapper');
        $('#extendedSwitcher').click(function(){
  			initTinymce_full('#body');
            $(this).hide();
          $('#global_content_simple').css('min-width', '800px').css('min-height', '800px');
          var dAR = function(){if (parent.Smoothbox.instance) parent.Smoothbox.instance.doAutoResize()};
          dAR();
          setTimeout(dAR,500);
  	  	});

        var validateRecivers = function(){
          if (!$('#toValues').val()){
            $('#to')[0].setCustomValidity("Выберите пожалуйста хотя бы одного получателя сообщения.");
            return;
          }
          $('#to')[0].setCustomValidity("");
        };

        $('form#messages_compose').submit(validateRecivers);
        $('form#messages_compose').on('mousedown keypress', validateRecivers);
	});

	
</script>
<style type="text/css">
  .form-wrapper
  {
    display: flex;
    align-items: center;
  }
  #contact_information-element > p
  {
    padding-bottom: 0;
  }
</style>

<? if (!$this->isSmoothbox){ ?>
<H2><a href="<?= $this->url(array('action' => 'inbox')) ?>" class="navigation_prev_node">Все диалоги <i class="fa fa-chevron-right"></i></a> <?=$this->to ? 'Отправка нового сообщения' : 'Новый диалог'?></H2>

<? } ?>
<?=$this->form->render($this) ?>

