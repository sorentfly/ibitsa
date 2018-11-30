<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<script type="text/javascript">
  en4.core.runonce.add(function(){
    $$('th.admin_table_short input[type=checkbox]').addEvent('click', function(event){
      var el = $(event.target);
      $$('input[type=checkbox]:not(:disabled)').set('checked', el.get('checked'));
    });
  });

  var changeOrder =function(orderby, direction){
    $('orderby').value = orderby;
    $('orderby_direction').value = direction;
    $('filter_form').submit();
  }

  var delectSelected =function(){
    var checkboxes = $$('input[type=checkbox]');
    var selecteditems = [];

    checkboxes.each(function(item, index){
      var checked = item.get('checked');
      var value = item.get('value');
      if (checked == true && value != 'on'){
        selecteditems.push(value);
      }
    });

    $('ids').value = selecteditems;
    $('delete_selected').submit();
  }

  function setDefault(level_id) {
    (new Request.JSON({
      'format': 'json',
      'url' : '<?php echo $this->url(array('module' => 'authorization', 'controller' => 'admin-level', 'action' => 'set-default'), 'default', true) ?>',
      'data' : {
        'format' : 'json',
        'level_id' : level_id
      },
      'onRequest' : function(){
        $$('input[type=radio]').set('disabled', true);
      },
      'onSuccess' : function(responseJSON, responseText)
      {
        window.location.reload();
      }
    })).send();

  }
</script>

<h2>
  <?php echo $this->translate("Member Levels") ?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>


<p>
  <?php $link = $this->htmlLink(
    array('module' => 'user', 'controller' => 'manage', 'action' => 'index', "route"=>"admin_default"),
    $this->translate("View Members")) ?>
  <?php echo $this->translate("AUTHORIZATION_VIEWS_SCRIPTS_ADMINLEVEL_DESCRIPTION", $link) ?>
</p>
<br />
<?php
	$settings = Engine_Api::_()->getApi('settings', 'core');
	if( $settings->getSetting('user.support.links', 0) == 1 ) {
		echo 'More info: <a href="http://support.socialengine.com/questions/155/Admin-Panel-Manage-Member-Levels" target="_blank">See KB article</a>.';	
	} 
?>	
<br />
<?php echo $this->formFilter->render($this) ?>

<br />

<div class="admin_results">
  <div>
    <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), $this->translate('Add Member Level'), array(
      'class' => 'buttonlink',
      'style' => 'background-image: url(' . $this->layout()->staticBaseUrl . 'application/modules/Authorization/externals/images/admin/add.png);'
    )) ?>
  </div>
  <div>
    <?php $levelCount = $this->paginator->getTotalItemCount(); ?>
    <?php echo $this->translate(array("%d level found","%d levels found", $levelCount), $levelCount); ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
</div>

<br />

<table class='admin_table'>
  <thead>
    <tr>
      <th style="width: 1%;" class="admin_table_short"><input type='checkbox' class='checkbox' /></th>
      <th style="width: 1%;"><a href="javascript:void(0);" onclick="javascript:changeOrder('level_id', '<?php if($this->orderby == 'level_id') echo "ASC"; else echo "DESC"; ?>');">ID</a></th>
      <th>
        <a href="javascript:void(0);" onclick="javascript:changeOrder('title', '<?php if($this->orderby == 'title') echo "ASC"; else echo "DESC"; ?>');">
          <?php echo $this->translate("Level Name") ?>
        </a>
      </th>
      <th style="width: 1%;"><?php echo $this->translate("Members") ?></th>
      <th style="width: 1%;"><?php echo $this->translate("Type") ?></th>
      <th style="width: 1%;" class="admin_table_centered"><?php echo $this->translate("Default Level") ?></th>
      <th style="width: 1%;"><?php echo $this->translate("Options") ?></th>
    </tr>

  </thead>
  <tbody>
    <?php if( count($this->paginator) ): ?>
      <?php foreach( $this->paginator as $item ): ?>
        <tr>
        <td><input <?php if ($item->flag) echo 'disabled';?> type='checkbox' class='checkbox' value="<?php echo $item->level_id?>"></td>
          <td>
            <?php echo $item->level_id ?>
          </td>
          <td class="admin_table_bold">
            <?php echo $this->translate($item->title) ?>
          </td>
          <td class="nowrap">
            <?php $membershipCount = $item->getMembershipCount(); ?>
            <?php echo $this->htmlLink(array('module' => 'user', 'controller' => 'manage', 'level_id' => $item->level_id, 'reset' => false),
                          $this->translate(array("%s member", "%s members", $membershipCount), $this->locale()->toNumber($membershipCount))) ?>
          </td>
          <td>
            <?php echo $this->translate(ucfirst($item->type == 'user' ? 'normal' : $item->type)) ?>
          </td>
          <td class="admin_table_centered">
            <?php if( $item->flag == 'default' ): ?>
              <img src="application/modules/Core/externals/images/notice.png" alt="Default" />
            <?php else: ?>
              <?php echo $this->formRadio('default', $item->level_id, array('onchange' => "setDefault({$item->level_id});",'disable'=>($item->flag || $item->type != 'user')), '') ?>
            <?php endif; ?>
          </td>
          <td class="admin_table_options">
            <a href='<?php echo $this->url(array('action' => 'edit', 'id' => $item->level_id)) ?>'>
              <?php echo $this->translate("edit") ?>
            </a>
            <?php if (!$item->flag) :?>
            |
            <a href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->level_id)) ?>'>
              <?php echo $this->translate("delete") ?>
            </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>

</table>

<br/>
<div class='buttons'>
  <button onclick="javascript:delectSelected();" type='submit'>
    <?php echo $this->translate("Delete Selected") ?>
  </button>
</div>

<form id='delete_selected' method='post' action='<?php echo $this->url(array('action' =>'deleteselected')) ?>'>
  <input type="hidden" id="ids" name="ids" value=""/>
</form>