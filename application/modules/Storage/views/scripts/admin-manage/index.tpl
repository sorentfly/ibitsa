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
  <?php echo $this->translate("Manage Stored Files") ?>
</h2>

<p>
  <?php echo $this->translate("STORAGE_VIEWS_ADMIN_MANAGE_INDEX_DESCRIPTION") ?>
</p>

<br />


<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>

  <br />


  <script type="text/javascript">
    var currentOrder = '<?php echo $this->filterValues['order'] ?>';
    var currentOrderDirection = '<?php echo $this->filterValues['direction'] ?>';
    var changeOrder = function(order, default_direction){
      // Just change direction
      if( order == currentOrder ) {
        $('direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
      } else {
        $('order').value = order;
        $('direction').value = default_direction;
      }
      $('filter_form').submit();
    }
  </script>
<?php endif; ?>


<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s files found of %s files total",
      "%s files found of %s files total", $count), $this->locale()->toNumber($count),
        $this->locale()->toNumber($this->total)) ?>
  </div>
  <?php echo $this->paginationControl($this->paginator, null, null, array(
    'query' => $this->filterValues,
    'pageAsQuery' => true,
  )); ?>
</div>

<br />


<table class='admin_table' style='width: 40%;'>
  <thead>
    <tr>
      <th style='width: 1%;'><?php echo $this->translate("Thumbnail") ?></th>
      <th style='width: 1%;'><?php echo $this->translate("ID") ?></th>
      <th><?php echo $this->translate("Information") ?></th>
      <th style='width: 1%;' class='admin_table_options'><?php echo $this->translate("Options") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php if( count($this->paginator) ): ?>
      <?php foreach( $this->paginator as $item ): ?>
        <tr>
          <td class='admin_table_bold'>
            <?php echo $this->itemPhoto($item, 'thumb.normal', basename($item->name), array(
              'style' => 'max-width: 120px; max-height: 120px;',
            )) ?>
          </td>
          <td>
            <?php echo $this->locale()->toNumber($item->file_id) ?>
          </td>
          <td class="nowrap">
            <?php echo $this->translate('Original Name: %s', basename($item->name)) ?>
            <br />
            <?php echo $this->translate('MIME Type: %s', $item->mime_major . '/' . $item->mime_minor) ?>
            <br />
            <?php if( 'none' != @$this->filterValues['type'] ): ?>
              <?php echo $this->translate('Thumbnail Type: %s', trim(ucwords(str_replace(array('.', 'thumb'), array(' ', ''), $item->type)))) ?>
              <br />
            <?php endif; ?>
            <?php echo $this->translate('Belongs to: %s', !empty($this->parents[$item->file_id]) ? $this->parents[$item->file_id]->toString() : 'unknown') ?>
            <br />
            <?php echo $this->translate('Uploaded by: %s', !empty($this->users[$item->file_id]) ? $this->users[$item->file_id]->toString() : 'unknown') ?>
            <br />
            <?php echo $this->translate('Size: %s', $this->translate('%s bytes', $this->locale()->toNumber($item->size))) ?>
            <br />
            <?php echo $this->translate('Storage: %s (%d)',
                !empty($this->serviceTypes[$item->service_id]) ? $this->serviceTypes[$item->service_id]['title'] : 'unknown', $item->service_id) ?>
          </td>
          <td class='admin_table_options'>
            <a class="smoothbox" href='<?php echo $this->url(array('action' => 'view', 'file_id' => $item->file_id)) ?>'>
              <?php echo $this->translate("view") ?>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>


<br />

<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s files found of %s files total",
      "%s files found of %s files total", $count), $this->locale()->toNumber($count),
        $this->locale()->toNumber($this->total)) ?>
  </div>
  <?php echo $this->paginationControl($this->paginator, null, null, array(
    'query' => $this->filterValues,
    'pageAsQuery' => true,
  )); ?>
</div>