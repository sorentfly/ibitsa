<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: network.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Alex
 */
?>

<?php
  $this->headScript()
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js');
?>

<script type="text/javascript">
  function joinNetwork(network_id)
  {
    $('join_id').value = network_id;
    $('network-form').submit();
    $('avaliable_networks').innerHTML = "<div style='margin:15px 0;'><img class='loading_icon' src='" + en4.core.staticBaseUrl + "application/modules/Core/externals/images/loading.gif'/><?php echo $this->translate('Joining Network...')?></div>";
  }

  function leaveNetwork(network_id)
  {
    $('current_networks').innerHTML = "<div><img class='loading_icon' src='" + en4.core.staticBaseUrl + "application/modules/Core/externals/images/loading.gif'/><?php echo $this->translate('Leaving Network...')?></div>";
    $('leave_id').value = network_id;
    $('network-form').submit();
  }
  en4.core.runonce.add(function()
  {
    var availableNetworks = <?php echo $this->action('suggest', 'network', 'network', array('sendNow' => false, 'includeSelf' => true)) ?>;
    var loader = new Element('img',{ src: en4.core.staticBaseUrl + 'application/modules/Core/externals/images/loading.gif'});
    var networkAutocomplete = new Autocompleter.Local('title', availableNetworks, {
      'postVar'        : 'text',
      'alwaysOpen'     : true,
      'prefetchOnInit' : true,
      'tokenValueKey'  : 'title',
      'minLength': 0,
      'selectMode': false,
      'selectFirst': false,
      'autocompleteType': 'tag',
      'className': 'networks',
      'width' : '',
      'overflow' : true,
      'filterSubset' : true,
      'ignoreKeys':true,
      'ignoreOverlayFix':true,
      'injectChoice': function(token){
        var choice = new Element('li');
        new Element('div', {
          'html': token.title
        }).inject(choice);
        new Element('a',{
          'href': 'javascript:void(0);',
          'events': {
          	click : function(){
            	joinNetwork(token.id);
            }
          },
          'html': '<?php echo $this->translate('Join Network') ?>'
        }).inject(choice);
        choice.inject(this.choices);
        choice.store('autocompleteChoice', token);
      },
      'emptyChoices': function(){
        var choice = new Element('div', {
          'class': 'tip'
        });
        new Element('span', {
            'html': <?php echo Zend_Json::encode($this->translate("There are no networks containing that keyword."))?>
        }).inject(choice);
        choice.inject(this.choices);
      }
    });

    $('network-form').addEvent('submit', function(event) {
      event.stop();
    });

    new OverText($('title'), {
      'textOverride' : '<?php echo $this->translate('Start typing to filter...') ?>',
      'element' : 'label',
      'positionOptions' : {
        position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
        edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
        offset: {
          x: ( en4.orientation == 'rtl' ? -4 : 4 ),
          y: 2
        }
      }
    });
  });
</script>

<div class="headline">
  <h2>
    <?php echo $this->translate('My Settings');?>
  </h2>
  <div class="tabs">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render();
    ?>
  </div>
</div>

<div class='layout_middle'>
<div class='networks_left'>
<h3><?php echo $this->translate('Available Networks');?></h3>


<?php if(!empty($this->network_suggestions)):?>
<p>
  <?php echo $this->translate('To add a new network, begin typing its name below.');?>
</p>
<div id='avaliable_networks'>
  <br/>
  <?php echo $this->form->render($this) ?>
</div>

  
<?php if(false):?>
  <ul class='networks'>
  <?php foreach ($this->network_suggestions as $network): ?>
    <li>
      <div>
        <?php echo $network->title ?> <span>(<?php echo $this->translate(array('%s member.', '%s members.', $network->membership()->getMemberCount()),$this->locale()->toNumber($network->membership()->getMemberCount())) ?>)</span>
      </div>
      <?php if( $network->assignment == 0 ): ?>
        <a href='javascript:void(0);' onclick="joinNetwork(<?php echo $network->network_id;?>)"><?php echo $this->translate('Join Network');?></a>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
  </ul>
<?php endif;?>

<?php else:?>
  <div class="tip">
    <span><?php echo $this->translate('There are currently no avaliable networks to join.');?></span>
  </div>
  
  <div style='display:none;'>
    <?php echo $this->form->render($this) ?>
  </div>
<?php endif; ?>


</div>


<div class='networks_right'>
<h3><?php echo $this->translate('My Networks');?></h3>
<p>
  <?php echo $this->translate(array('You belong to %s network.', 'You belong to %s networks.', count($this->networks)),$this->locale()->toNumber(count($this->networks))) ?>
</p>

<ul id='current_networks' class='networks'>
<?php foreach ($this->networks as $network): ?>
  <li>
    <div>
      <?php echo $network->title ?> <span>(<?php echo $this->translate(array('%s member.', '%s members.', $network->membership()->getMemberCount()),$this->locale()->toNumber($network->membership()->getMemberCount())) ?>)</span>
    </div>
    <?php if( $network->assignment == 0 ): ?>
      <a href='javascript:void(0);' onclick="leaveNetwork(<?php echo $network->network_id;?>)"><?php echo $this->translate('Leave Network');?></a>
    <?php endif; ?>
  </li>
<?php endforeach; ?>
</ul>
</div>
</div>










