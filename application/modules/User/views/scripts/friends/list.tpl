<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: list.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Alex
 */
?>
<div class="profile_friends_lists_items">
  <?php
  // show lists associated with this friend
  $first_iteration = true;
  foreach ($this->joined_friend_lists as $sublist_id)
  {
    if ($first_iteration)
    {
      echo '<span class="profile_friends_lists_text">'.$this->translate('Lists:').'</span> ';
      $first_iteration = false;

    }
    echo '<span class="profile_friends_lists_item">';
    echo $this->friend_list_lookup[$sublist_id]->name;
    echo '<a href="#" onclick="assignFriend(' . $this->friend->user_id . ', ' . $sublist_id . ',\'remove\'); return false;">x</a>';
    echo '</span>';
  }
  ?>
  <div class="profile_friends_lists_addlink" id="profile_friends_lists_addlink_<?php echo $this->friend->user_id;?>" >
    <a href="#"  onclick="showFriendListMenu(<?php echo $this->friend->user_id;?>); return false;">
      <span class="profile_friends_lists_addlinktext">
        <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/User/externals/images/friends/add_list.png' alt='' />
        <?php echo $this->translate('add to list');?>
      </span>
    </a>
    <div class="profile_friends_lists_menu" id="profile_friends_lists_menu_<?php echo $this->friend->user_id; ?>">
      <div>
        <div class="profile_friends_lists_menu_title">
          <?php echo $this->translate('Choose Friend List');?> (<a href="javascript:void(0);" onclick="hideFriendListMenu(<?php echo $this->friend->user_id;?>)">x</a>)
        </div>
        <ul>
          <?php
              $list_even = true;
              foreach ($this->friend_list_lookup as $sublist_id=>$sublist) {
                if (array_search($sublist_id, $this->joined_friend_lists) === false)
                {
                  echo '
                    <li>
                      <a class="profile_friends_delete_friend_list" href="javascript:void(0);" onclick="deleteSublist(' . $sublist_id. ')">x</a>
                      <a class="profile_friends_assign_friend_list_link" href="javascript:void(0);" onclick="assignFriend(' . $this->friend->user_id .',' . $sublist_id . ', \'add\'); return false;">' . $sublist->name . '</a>
                    </li>
                  ';
                }
              }
          ?>
        </ul>
        <div class="profile_friends_lists_menu_input">
          <input type="text" maxlength="30" onclick="clearNewListText(<?php echo $this->friend->user_id;?>)" onchange="assignFriend(<?php echo $this->friend->user_id;?>, 0, 'add');" id="friends_lists_menu_input_<?php echo $this->friend->user_id;?>" value="<?php echo $this->translate('New list...');?>"/>
        </div>
      </div>
    </div>
  </div>
</div>