<? if( count($this->paginator) ): ?>
  <ul>
    <? foreach( $this->paginator as $conversation ):
      $message = $conversation->getInboxMessage($this->viewer());
      $recipient = $conversation->getRecipientInfo($this->viewer());
      if( $conversation->hasResource() &&
                ($resource = $conversation->getResource()) ) {
        $sender = $resource;
      } else if( $conversation->recipients > 1 ) {
        $sender = $this->viewer();
      } else {
        foreach( $conversation->getRecipients() as $tmpUser ) {
          if( $tmpUser->getIdentity() != $this->viewer()->getIdentity() ) {
            $sender = $tmpUser;
          }
        }
      }
      if( !isset($sender) || !$sender ) {
        $sender = $this->viewer();
      }
      if( $resource ) {
        $author = $resource->toString();
      } else if( $conversation->recipients == 1 ) {
        $author = $this->htmlLink($sender->getHref(), $sender->getTitle());
      } else {
        $author = $this->translate(array('%s person', '%s people', $conversation->recipients),
            $this->locale()->toNumber($conversation->recipients));
      }
      ?>
      <li<? if( !$recipient->inbox_read ): ?> class="new"<? endif; ?>>
        <div class="from">
          <?=$this->translate('From %s %s', $author, $this->timestamp($message->date)) ?>
        </div>
        <p class="title">
          <?
            ( '' != ($title = trim($message->getTitle())) ||
              '' != ($title = trim($conversation->getTitle())) ||
              $title = '<em>' . $this->translate('(No Subject)') . '</em>' );
            $title = $this->string()->truncate($this->string()->stripTags($title));
          ?>
          <?=$this->htmlLink($conversation->getHref(), $title) ?>
        </p>
        <p class="body">
          <?=$this->string()->truncate($this->string()->stripTags(str_replace('&nbsp;', ' ', html_entity_decode($message->body)))) ?>
        </p>
      </li>
    <? endforeach; ?>
  </ul>
<? endif; ?>
