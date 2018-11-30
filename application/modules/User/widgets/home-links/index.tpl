<style>
    #result_list_user_search_field
    {
        border-left: 1px solid #9b9c9c;
        border-right: 1px solid #9b9c9c;
        border-bottom: 1px solid #9b9c9c;
        -moz-box-shadow:0 0 2px 0 #7396CD;
        -webkit-box-shadow:0 0 2px 0 #7396CD;
        box-shadow:0 0 2px 0 #7396CD;
        margin: -21px 0 0 50px;
        width: 166px;
    }
</style>
<div class="user_menu">
   <ul>
        <li class="user-menu-li"><a class="user-menu-a" href="/messages/inbox/"><span class="message-icon"></span> Сообщения<? if($this->messages_count > 0):?> <span class="count">+<?=$this->messages_count;?></span><? endif;?></a></li>
        <li class="user-menu-li"><a class="user-menu-a" id="home_updates" href="/activity/notifications/"><span class="refresh-icon"></span>Обновления<? if($this->notification_count > 0):?> <span class="count">+<?=$this->notification_count;?></span><? endif;?></a></li>
        <li class="user-menu-li"><a class="user-menu-a" href="/events/manage/"><span class="action-icon"></span>Мои события</a></li>
        <li class="user-menu-li"><a class="user-menu-a" href="/profile/<?=$this->viewer()->getIdentity(); ?>"><span class="profile-icon"></span>Мой профиль</a> <a href="/members/edit/profile/" class="user-settings">изменить</a> </li>
        <li class="user-menu-li"><input class="user-search select_box" id="user_search_field" maxlength="32" placeholder="Поиск друзей" value=""/> <span class="search-icon" id="user_search_button"> </span></li>
    </ul>
    <div class="result_list" id="result_list_user_search_field" style="display: none;">
        <ul></ul>
    </div>
</div>