<? if ($this->hasAcademyMenu ){ ?>
    <?=$this->content()->renderWidget('zftsh.profile-tabs')?>
<? } ?>
<? if( count($this->paginator) ): ?>
  <div class="messages_list">
    <ul>
      <? foreach( $this->paginator as $conversation ):
        $message = $conversation->getOutboxMessage($this->viewer());
        $recipient = $conversation->getRecipientInfo($this->viewer());
        $resource = "";
        $sender   = "";
        if( $conversation->hasResource() &&
                  ($resource = $conversation->getResource()) ) {
          $sender = $resource;
        } else if( $conversation->recipients > 1 ) {
          $sender = $this->viewer();
        } else {
          foreach( $conversation->getRecipients() as $tmpUser ) {
            if( $tmpUser->getIdentity() != $this->viewer()->getIdentity() ) {
              $sender = $tmpUser;
              break;
            }
          }
        }
        if( (!isset($sender) || !$sender) ){
          if( $this->viewer()->getIdentity() !== $conversation->user_id ){
            $sender = Engine_Api::_()->user()->getUser($conversation->user_id);
          } else {
            $sender = $this->viewer();
          }
        }
        if( !isset($sender) || !$sender ) {
          //continue;
          $sender = new User_Model_User(array());
        }
        ?>
        <li<? if( !$recipient->inbox_read ): ?> class='messages_list_new'<? endif; ?> id="message_conversation_<?=$conversation->getIdentity() ?>">
          <div class="messages_list_checkbox">
            <input class="checkbox" type="checkbox" value="<?=$conversation->getIdentity() ?>" />
          </div>
          <div class="messages_list_photo">
            <?=$this->htmlLink($sender->getHref(), $this->itemPhoto($sender, 'thumb.icon')) ?>
          </div>
          <div class="messages_list_from">
            <p class="messages_list_from_name">
              <? if( !empty($resource) ): ?>
                <?=$resource->toString() ?>
              <? elseif( $conversation->recipients == 1 ): ?>
                <?=$this->htmlLink($sender->getHref(), $sender->getTitle()) ?>
              <? else: ?>
                <?=$this->translate(array('%s person', '%s people', $conversation->recipients),
                    $this->locale()->toNumber($conversation->recipients)) ?>
              <? endif; ?>
            </p>
            <p class="messages_list_from_date">
              <?=$this->timestamp($message->date) ?>
            </p>
          </div>
          <div class="messages_list_info">
            <p class="messages_list_info_title">
              <?
                // ... scary
                ( (isset($message) && '' != ($title = trim($message->getTitle()))) ||
                  (isset($conversation) && '' != ($title = trim($conversation->getTitle()))) ||
                  $title = '<em>' . $this->translate('(No Subject)') . '</em>' );
              ?>
              <?=$this->htmlLink($conversation->getHref(), $title) ?>
            </p>
            <p class="messages_list_info_body">
              <?=html_entity_decode($message->body) ?>
            </p>
          </div>
        </li>
      <? endforeach; ?>
    </ul>
  </div>

  <br />
  <button id="delete"><?=$this->translate('Delete Selected');?></button>
  <script type="text/javascript">
  <!--
  $('delete').addEvent('click', function(){
    var selected_ids = new Array();
    $$('div.messages_list input[type=checkbox]').each(function(cBox) {
      if (cBox.checked)
        selected_ids[ selected_ids.length ] = cBox.value;
    });
    var sb_url = '<?=$this->url(array('action'=>'delete'), 'messages_general', true) ?>?place=outbox&message_ids='+selected_ids.join(',');
    if (selected_ids.length > 0)
      Smoothbox.open(sb_url);
  });
  //-->
  </script>
  <br />
  <br />

<? else: ?>
  <p><?=$this->translate(array('You have %s sent message total', 'You have %s sent messages total', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?></p>
  <br />
  <div class="tip">
    <span>
      <?=$this->translate('Tip: %1$sClick here%2$s to send your first message!', "<a href='".$this->url(array('action' => 'compose'), 'messages_general')."'>", '</a>'); ?>
    </span>
  </div>
<? endif; ?>

<?=$this->paginationControl($this->paginator); ?>
