<div class="users-online">
    <? foreach( $this->paginator as $user ): ?>
    <div class="user-data">
        <a title="<?=$user->getTitle();?>" href="<?=$user->getHref();?>">
            <?=$this->itemPhoto($user, 'thumb.normal', $user->getTitle());?>
            <span class="user-name"><?=$user->getTitle();?></span>
        </a>      
    </div>
  <? endforeach; ?>
</div>