<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: request-follow.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<script type="text/javascript">
  var userWidgetRequestSend = function(action, user_id, notification_id)
  {
    var url;
    if( action == 'confirm' )
    {
      url = '<?php echo $this->url(array('controller' => 'friends', 'action' => 'confirm'), 'user_extended', true) ?>';
    }
    else if( action == 'reject' )
    {
      url = '<?php echo $this->url(array('controller' => 'friends', 'action' => 'ignore'), 'user_extended', true) ?>';
    }
    else
    {
      return false;
    }

    (new Request.JSON({
      'url' : url,
      'data' : {
        'user_id' : user_id,
        'format' : 'json',
        'token' : '<?php echo $this->token() ?>'
      },
      'onSuccess' : function(responseJSON)
      {
        if( !responseJSON.status )
        {
          $('user-widget-request-' + notification_id).innerHTML = responseJSON.error;
        }
        else
        {
          $('user-widget-request-' + notification_id).innerHTML = responseJSON.message;
        }
      }
    })).send();
  }
</script>

<li id="user-widget-request-<?php echo $this->notification->notification_id ?>">
  <?php echo $this->itemPhoto($this->notification->getSubject(), 'thumb.icon') ?>
  <div>
    <div>
      <?php echo $this->translate('%1$s has requested to follow you.', $this->htmlLink($this->notification->getSubject()->getHref(), $this->notification->getSubject()->getTitle())); ?>
    </div>
    <div>
      <button type="submit" onclick='userWidgetRequestSend("confirm", <?php echo  $this->string()->escapeJavascript($this->notification->getSubject()->getIdentity()) ?>, <?php echo $this->notification->notification_id ?>)'>
        <?php echo $this->translate('Allow');?>
      </button>
      <?php echo $this->translate('or');?>
      <a href="javascript:void(0);" onclick='userWidgetRequestSend("reject", <?php echo  $this->string()->escapeJavascript($this->notification->getSubject()->getIdentity()) ?>, <?php echo $this->notification->notification_id ?>)'>
        <?php echo $this->translate('ignore request');?>
      </a>
    </div>
  </div>
</li>