<h3>
  <?=$this->translate(array('%s member found.', '%s members found.', $this->totalUsers),$this->locale()->toNumber($this->totalUsers)) ?>
</h3>
<? $viewer = Engine_Api::_()->user()->getViewer();?>
<ul id="browsemembers_ul">
  <? foreach( $this->users as $user ): ?>
    <li>
      <?=$this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')) ?>
      <? 
      $table = Engine_Api::_()->getDbtable('block', 'user');
      $select = $table->select()
        ->where('user_id = ?', $user->getIdentity())
        ->where('blocked_user_id = ?', $viewer->getIdentity())
        ->limit(1);
      $row = $table->fetchRow($select);
      ?>
      <? if( $row == NULL ): ?>
        <? if( $this->viewer()->getIdentity() ): ?>
        <div class='browsemembers_results_links'>
          <?=$this->userFriendship($user) ?>
        </div>
      <? endif; ?>
      <? endif; ?>

        <div class='browsemembers_results_info'>
          <?=$this->htmlLink($user->getHref(), $user->getTitle()) ?>
          <?=$user->status; ?>
          <? if( $user->status != "" ): ?>
            <div>
              <?=$this->timestamp($user->status_date) ?>
            </div>
          <? endif; ?>
        </div>
    </li>
  <? endforeach; ?>
</ul>

<? if( $this->users ): ?>
<div class='browsemembers_viewmore' id="browsemembers_viewmore">
    <?=$this->paginationControl($this->users, null, null, array(
      'pageAsQuery' => true,
      'query' => $this->formValues,
      //'params' => $this->formValues,
    )); ?>
</div>
<? endif; ?>

<script type="text/javascript">
    page = '<?=sprintf('%d', $this->page) ?>';
    totalUsers = '<?=sprintf('%d', $this->totalUsers) ?>';
    userCount = '<?=sprintf('%d', $this->userCount) ?>';
</script>