<?php echo $this->form->render($this) ?>

<div id="blockedUserList" style="display:none;">
  <ul>
    <?php foreach ($this->blockedUsers as $user): ?>
      <?php if($user instanceof User_Model_User && $user->getIdentity()) :?>
        <li>[
          <?php echo $this->htmlLink(array('controller' => 'block', 'action' => 'remove', 'user_id' => $user->getIdentity(), 'route' => 'user_extended'), 'Unblock', array('class'=>'smoothbox')) ?>
          ] <?php echo $user->getTitle() ?></li>
      <?php endif;?>
    <?php endforeach; ?>
  </ul>
</div>

<script type="text/javascript">
<!--
window.addEvent('load', function(){
  $$('#blockedUserList ul')[0].inject($('blockList-element'));
});
// -->
</script>
