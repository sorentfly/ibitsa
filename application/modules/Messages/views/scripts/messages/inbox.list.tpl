<? if (!count($this->paginator)) { ?>
    <div class="item_display_no_even">
        <? if (!$this->filter['only_unreaded'] && $this->filter['pupil_class'] == 'all') { ?>
            <?= $this->translate('Tip: %1$sClick here%2$s to send your first message!', "<a href='" . $this->url(array('action' => 'compose'), 'messages_general') . "'>", '</a>'); ?>
        <? } else { ?>
            Нет сообщений по заданному фильтру.
        <? } ?>
    </div>
<? } else { ?>
        <ul class="browse-filter-target"
            data-end-of-list="<?= $this->paginator->getPages()->pageCount > 1 ? 'no' : 'yes' ?>" data-page="<?=$this->page?>">


            <?
            /* @var $paginator Zend_Paginator */
            /* @var $conversation Messages_Model_Conversation */
            /* @var $message Messages_Model_Message */
            /* @var $recipient Messages_Model_Recipient */
            /* @var $userApi User_Api_Core */

            $paginator = $this->paginator;
            foreach ($paginator->getIterator() as $i => $conversation) {
                $message = $conversation->getLastMessage($this->viewer());
                if ($message->user_id == $this->viewer()->getIdentity()) {
                    //тогда берём recipient не юзера, если их много, то приоритет с флагом inbox_read
                    $recipient = $conversation->getRecipientExcept($this->viewer());
                }else{
                    $recipient = $conversation->getRecipientInfo($this->viewer());
                }
                /* @var User_Model_User $sender */
                $sender = Engine_Api::_()->getItem('user', $message->user_id);
                if ($conversation->hasResource() && $conversation->user_id == $message->user_id) {
                    $sender = $conversation->getResource();
                }
                ?>
                <li <? if (!$recipient->inbox_read): ?> class='messages_list_new' <? endif; ?> id="message_conversation_<?= $conversation->conversation_id ?>">
                    <?= $this->partial('messages/inbox.recipients.tpl', array('conversation' => $conversation)) ?>

                    <a class="messages_list_info" href="<?= $conversation->getHref() ?>">
                        <? if ($sender && $sender->getIdentity() && ($conversation->recipients > 1 || $sender->getIdentity() == $this->viewer()->getIdentity())) { ?>
                            <span class="messages_sender_info">
                                <?=$this->itemPhoto($sender, 'thumb.icon', $sender->getTitle(), array('title' => $sender->getTitle()))?>
                            </span>
                        <? } ?>
                        <?
                        $title = trim($message->getTitle());
                        if (empty($title)) {
                            $title = trim($conversation->getTitle());
                        }
                        if (!empty($title)) { ?>
                        <span class="messages_list_info_header">
                            <span class="messages_list_info_title">
                            <?= $this->htmlLink($conversation->getHref(), $title) ?>
                            </span>
                        </span>
                        <? } ?>
                        <span class="messages_list_info_body">
                            <?= html_entity_decode($message->body) ?>
                            <br>
                            <span class="messages_list_from_date">
                                <?= $this->timestamp($message->date) ?>
                            </span>

                        </span>
                    </a>
                </li>
            <? } ?>

        </ul>
        <div class="browse-filter-target-loading" style="display:none;"><i class="fa fa-circle-o-notch fa-spin"></i>
        </div>
<? } ?>
