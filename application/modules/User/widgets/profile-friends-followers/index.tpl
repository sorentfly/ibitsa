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

<script type="text/javascript">
  var toggleFriendsPulldown = function(event, element, user_id) {
    event = new Event(event);
    if( $(event.target).get('tag') != 'a' ) {
      return;
    }
    
    $$('.profile_friends_lists').each(function(otherElement) {
      if( otherElement.id == 'user_friend_lists_' + user_id ) {
        return;
      }
      var pulldownElement = otherElement.getElement('.pulldown_active');
      if( pulldownElement ) {
        pulldownElement.addClass('pulldown').removeClass('pulldown_active');
      }
    });
    if( $(element).hasClass('pulldown') ) {
      element.removeClass('pulldown').addClass('pulldown_active');
    } else {
      element.addClass('pulldown').removeClass('pulldown_active');
    }
    OverText.update();
  }
  var handleFriendList = function(event, element, user_id, list_id) {
    new Event(event).stop();
    if( !$(element).hasClass('friend_list_joined') ) {
      // Add
      en4.user.friends.addToList(list_id, user_id);
      element.addClass('friend_list_joined').removeClass('friend_list_unjoined');
    } else {
      // Remove
      en4.user.friends.removeFromList(list_id, user_id);
      element.removeClass('friend_list_joined').addClass('friend_list_unjoined');
    }
  }
  var createFriendList = function(event, element, user_id) {
    var list_name = element.value;
    element.value = '';
    element.blur();
    var request = en4.user.friends.createList(list_name, user_id);
    request.addEvent('complete', function(responseJSON) {
      if( responseJSON.status ) {
        var topRelEl = element.getParent();
        $$('.profile_friends_lists ul').each(function(el) {
          var relEl = el.getElement('input').getParent();
          new Element('li', {
            'html' : '\n\
<span><a href="javascript:void(0);" onclick="deleteFriendList(event, ' + responseJSON.list_id + ');">x</a></span>\n\
<div>' + list_name + '</div>',
            'class' : ( relEl == topRelEl ? 'friend_list_joined' : 'friend_list_unjoined' ) + ' user_profile_friend_list_' + responseJSON.list_id,
            'onclick' : 'handleFriendList(event, $(this), \'' + user_id + '\', \'' + responseJSON.list_id + '\');'
          }).inject(relEl, 'before');
        });
        OverText.update();
      } else {
        //alert('whoops');
      }
    });
  }
  var deleteFriendList = function(event, list_id) {
    event = new Event(event);
    event.stop();

    // Delete
    $$('.user_profile_friend_list_' + list_id).destroy();

    // Send request
    en4.user.friends.deleteList(list_id);
  }
  en4.core.runonce.add(function(){
    $$('.profile_friends_lists input').each(function(element) { new OverText(element); });
    
    <?php if( !$this->renderOne ): ?>
    var anchor = $('user_profile_friends_followers').getParent();
    $('user_profile_friends_followers_previous').style.display = '<?php echo ( $this->friends->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
    $('user_profile_friends_followers_next').style.display = '<?php echo ( $this->friends->count() == $this->friends->getCurrentPageNumber() ? 'none' : '' ) ?>';

    $('user_profile_friends_followers_previous').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : '<?php echo $this->subject->getGuid() ?>',
          page : <?php echo sprintf('%d', $this->friends->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    $('user_profile_friends_followers_next').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : '<?php echo $this->subject->getGuid() ?>',
          page : <?php echo sprintf('%d', $this->friends->getCurrentPageNumber() + 1) ?>
        }
      }), {
        'element' : anchor
      })
    });
    <?php endif; ?>

    $$('.friends_lists_menu_input input').each(function(element){
      element.addEvent('blur', function() {
        this.getParents('.drop_down_frame')[0].style.visibility = "hidden";
      });
    });
  });
</script>

<ul class='profile_friends' id="user_profile_friends_followers">
  
  <?php foreach( $this->friends as $membership ):
    if( !isset($this->friendUsers[$membership->user_id]) ) continue;
    $member = $this->friendUsers[$membership->user_id];
    ?>

    <li id="user_friend_<?php echo $member->getIdentity() ?>">

      <?php echo $this->htmlLink($member->getHref(), $this->itemPhoto($member, 'thumb.icon'), array('class' => 'profile_friends_icon')) ?>

      <div class='profile_friends_options'>
        <?php echo $this->userFriendship($member) ?>
      </div>

      <div class='profile_friends_body'>
        <div class='profile_friends_status'>
          <span>
            <?php echo $this->htmlLink($member->getHref(), $member->getTitle()) ?>
          </span>
          <?php echo $member->status; ?>
        </div>

        <?php /* if( $this->viewer()->isSelf($this->subject()) && Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.lists')): // BEGIN LIST CODE ?>
          <div class='profile_friends_lists' id='user_friend_lists_<?php echo $member->user_id ?>'>

            <span class="pulldown" style="display:inline-block;" onClick="toggleFriendsPulldown(event, this, '<?php echo $member->user_id ?>');">
              <div class="pulldown_contents_wrapper">
                <div class="pulldown_contents">
                  <ul>
                    <?php foreach( $this->lists as $list ):
                      $inList = in_array($list->list_id, (array)@$this->listsByUser[$member->user_id]);
                      ?>
                      <li class="<?php echo ( $inList !== false ? 'friend_list_joined' : 'friend_list_unjoined' ) ?> user_profile_friend_list_<?php echo $list->list_id ?>" onclick="handleFriendList(event, $(this), '<?php echo $member->user_id ?>', '<?php echo $list->list_id ?>');">
                        <span>
                          <a href="javascript:void(0);" onclick="deleteFriendList(event, <?php echo $list->list_id ?>);">x</a>
                        </span>
                        <div>
                          <?php echo $list->title ?>
                        </div>
                      </li>
                    <?php endforeach; ?>
                    <li>
                      <input type="text" title="<?php echo $this->translate('New list...') ?>" onclick="new Event(event).stop();" onkeypress="if( new Event(event).key == 'enter' ) { createFriendList(event, $(this), '<?php echo $member->user_id ?>'); }" />
                    </li>
                  </ul>
                </div>
              </div>
              <a href="javascript:void(0);"><?php echo $this->translate('add to list') ?></a>
            </span>
            
          </div>
        
        <?php endif; */ // END LIST CODE ?>
      </div>

    </li>

  <?php endforeach ?>
    
</ul>

<div>
  <div id="user_profile_friends_followers_previous" class="paginator_previous">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
      'onclick' => '',
      'class' => 'buttonlink icon_previous'
    )); ?>
  </div>
  <div id="user_profile_friends_followers_next" class="paginator_next">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
      'onclick' => '',
      'class' => 'buttonlink_right icon_next'
    )); ?>
  </div>
</div>