<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<h2>
  <?php echo $this->translate("Manage Storage Services") ?>
</h2>

<p>
  <?php echo $this->translate("STORAGE_VIEWS_ADMIN_SERVICES_INDEX_DESCRIPTION") ?>
  <a class="admin help" href="http://support.socialengine.com/questions/188/How-to-use-the-CDN-Storage-Feature" target="_blank"> </a>
</p>
<br />
<?php
$settings = Engine_Api::_()->getApi('settings', 'core');
if( $settings->getSetting('user.support.links', 0) == 1 ) {
	echo 'More info: <a href="http://support.socialengine.com/questions/224/Admin-Panel-Settings-Storage-System" target="_blank">See KB article</a>.';	
} 
?>	
<br />	
<br />


<div>
  <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), $this->translate('Add Service'), array(
    'class' => 'buttonlink',
    'style' => 'background-image: url(' . $this->layout()->staticBaseUrl . 'application/modules/Storage/externals/images/admin/add.png);'
  )) ?>
</div>

<br />



<?php if( !empty($this->activeJobs) && $this->activeJobs->count() > 0 ): ?>
  <ul class="form-notices">
    <?php foreach( $this->activeJobs as $activeJob ): ?>
      <li>
        <?php echo $this->translate('File are currently being transferred.') ?>
        <?php echo $this->translate('%1$s%% complete.', $this->locale()->toNumber(100 * $activeJob->progress)) ?>
      </li>
    <?php endforeach ?>
  </ul>

  <br />
<?php endif; ?>



<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s service found",
        "%s services found", $count), $this->locale()->toNumber($count)) ?>
  </div>
  <?php echo $this->paginationControl($this->paginator, null, null, array(
    'query' => $this->filterValues,
    'pageAsQuery' => true,
  )); ?>
</div>

<br />


<script type="text/javascript">
  function setDefaultStorageService(service_id) {
    $$('input[type=radio]').set('disabled', true);
    var req = new Request.JSON({
      'format': 'json',
      'url' : '<?php echo $this->url(array('action' => 'set-default')) ?>',
      'data' : {
        'format' : 'json',
        'service_id' : service_id
      },
      'onSuccess' : function(responseJSON, responseText) {
        window.location.reload();
      }
    });
    
    req.send();
  }
</script>


<table class='admin_table'>
  <thead>
    <tr>
      <th style='width: 1%;'>
        <?php echo $this->translate("ID") ?>
      </th>
      <th>
        <?php echo $this->translate("Title") ?>
      </th>
      <th style='width: 1%;'>
        <?php echo $this->translate('Files') ?>
      </th>
      <th>
        <?php echo $this->translate('Storage Used') ?>
      </th>
      <th style='width: 1%;' class='admin_table_centered'>
        <?php echo $this->translate("Enabled") ?>
      </th>
      <th style='width: 1%;' class='admin_table_centered'>
        <?php echo $this->translate("Default") ?>
      </th>
      <th style='width: 1%;' class='admin_table_options'><?php echo $this->translate("Options") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php if( count($this->paginator) ): ?>
      <?php foreach( $this->paginator as $item ): ?>
        <tr>
          <td>
            <?php echo $this->locale()->toNumber($item->service_id) ?>
          </td>
          <td class='admin_table_bold'>
            <?php echo $this->translate($this->serviceTypes[$item->servicetype_id]['title']) ?>
          </td>
          <td class='admin_table_centered'>
            <?php echo $this->locale()->toNumber($this->serviceFileInfo[$item->service_id]['count']) ?>
          </td>
          <td style="width: 1%; white-space: nowrap;">
            <?php echo $this->translate('%1$s bytes',
                $this->locale()->toNumber((integer) $this->serviceFileInfo[$item->service_id]['size'])) ?>
          </td>
          <td class='admin_table_centered'>
            <?php echo ( $item->enabled ? $this->translate('Yes') : $this->translate('No') ) ?>
          </td>
          <td class='admin_table_centered'>
            <?php if( $item->default ): ?>
              <img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Core/externals/images/notice.png" alt="Default" />
            <?php else: ?>
              <?php echo $this->formRadio('default', $item->service_id, array('onchange' => "setDefaultStorageService({$item->service_id});",'disable'=>!$item->enabled), '') ?>
            <?php endif; ?>
          </td>
          <td class='admin_table_options'>
            <a href='<?php echo $this->url(array('action' => 'edit', 'service_id' => $item->service_id)) ?>'>
              <?php echo $this->translate("edit") ?>
            </a>
            <?php if( empty($this->serviceFileInfo[$item->service_id]['count']) && !$item->default ): ?>
              <span class="sep">|</span>
              <a href='<?php echo $this->url(array('action' => 'delete', 'service_id' => $item->service_id)) ?>'>
                <?php echo $this->translate("delete") ?>
              </a>
            <?php endif ?>
            <?php if( $item->enabled ): ?>
              <span class="sep">|</span>
              <a href='<?php echo $this->url(array('action' => 'transfer', 'service_id' => $item->service_id)) ?>'>
                <?php echo $this->translate("transfer") ?>
              </a>
            <?php endif ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
