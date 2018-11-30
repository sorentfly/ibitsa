<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<h2>
  <?php echo $this->translate("Login History") ?>
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
  <?php echo $this->translate("USER_VIEWS_SCRIPTS_ADMINLOGINS_INDEX_DESCRIPTION") ?>
</p>

<br />


<?php if( $this->formFilter ): ?>
  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>

  <script type="text/javascript">
    var currentOrder = '<?php echo $this->order ?>';
    var currentOrderDirection = '<?php echo $this->order_direction ?>';
    var changeOrder = function(order, default_direction){
      // Just change direction
      if( order == currentOrder ) {
        $('order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
      } else {
        $('order').value = order;
        $('order_direction').value = default_direction;
      }
      $('filter_form').submit();
    }
  </script>
  
  <br />
<?php endif ?>

<div>
  <?php echo $this->htmlLink(array(
    'action' => 'clear',
    'reset' => false,
  ), 'Clear History', array(
    'class' => 'buttonlink smoothbox admin_referrers_clear',
  )) ?>
</div>

<br />

<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s sign-in found", "%s sign-ins found", $count), $this->locale()->toNumber($count)) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'pageAsQuery' => true,
      'query' => $this->formValues,
      //'params' => $this->formValues,
    )); ?>
  </div>
</div>

<br />


<table class='admin_table'>
  <thead>
    <tr>
      <th style='width: 1%;'>
        <a href="javascript:void(0);" onclick="changeOrder('login_id', 'DESC');">
          <?php echo $this->translate("ID") ?>
        </a>
      </th>
      <th>
        <a href="javascript:void(0);" onclick="changeOrder('user_id', 'ASC');">
          <?php echo $this->translate("Member") ?>
        </a>
      </th>
      <th>
        <a href="javascript:void(0);" onclick="changeOrder('email', 'ASC');">
          <?php echo $this->translate("Email Address") ?>
        </a>
      </th>
      <th style='width: 1%;'>
        <a href="javascript:void(0);" onclick="changeOrder('ip', 'ASC');">
          <?php echo $this->translate("IP Address") ?>
        </a>
      </th>
      <th style='width: 1%;'>
        <a href="javascript:void(0);" onclick="changeOrder('state', 'ASC');">
          <?php echo $this->translate("State") ?>
        </a>
      </th>
      <th style='width: 1%;'>
        <a href="javascript:void(0);" onclick="changeOrder('source', 'ASC');">
          <?php echo $this->translate("Source") ?>
        </a>
      </th>
      <th style='width: 1%;'>
        <a href="javascript:void(0);" onclick="changeOrder('timestamp', 'DESC');">
          <?php echo $this->translate("Timestamp") ?>
        </a>
      </th>
      <?php /*
      <th style='width: 1%;' class='admin_table_options'>
        <?php echo $this->translate("Options") ?>
      </th>
       */ ?>
    </tr>
  </thead>
  <tbody>
    <?php if( count($this->paginator) ): ?>
      <?php foreach( $this->paginator as $item ): ?>
        <tr class="admin_logins_<?php echo ( $item->state == 'success' ? 'okay' : 'error' ) ?> admin_logins_type_<?php echo str_replace('-', '_', $item->state) ?>">
          <td>
            <?php echo $this->locale()->toNumber($item->login_id) ?>
          </td>
          <td>
            <?php if( isset($this->users[$item->user_id]) ): ?>
              <?php echo $this->users[$item->user_id]->__toString() ?>
            <?php else: ?>
              <?php echo $this->translate('N/A') ?>
            <?php endif ?>
          </td>
          <td>
            <?php if( !_ENGINE_ADMIN_NEUTER ): ?>
              <?php echo $item->email ?>
            <?php else: ?>
              <?php echo $this->translate('(hidden)') ?>
            <?php endif ?>
          </td>
          <td class="nowrap">
            <?php if( !_ENGINE_ADMIN_NEUTER ): ?>
              <?php
                $ipObj = new Engine_IP($item->ip);
                echo $ipObj->toString()
              ?>
            <?php else: ?>
              <?php echo $this->translate('(hidden)') ?>
            <?php endif ?>
          </td>
          <td class="nowrap">
            <?php echo $this->translate(ucwords(str_replace('-', ' ', $item->state))) ?>
          </td>
          <td class="nowrap">
            <?php if( empty($item->source) ): ?>
              -
            <?php else: ?>
              <?php echo $this->translate(ucwords(str_replace('-', ' ', $item->source))) ?>
            <?php endif ?>
          </td>
          <td class="nowrap">
            <?php echo $this->locale()->toDateTime($item->timestamp) ?>
          </td>
      <?php /*
          <td>
            
          </td>
       */ ?>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
