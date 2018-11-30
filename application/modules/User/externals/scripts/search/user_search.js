"use strict";
if("jQuery" in window)
{
    jQuery.noConflict();
}

var i, exception,
focusing_process,
clicking_process,
TITLE_LOAD_ERROR,
TITLE_LOAD_ERROR_CITY,
FULL_LIST_TITLE,
_OTHER_M,
_OTHER_N,
_OTHER_F,
_NONE_SELECTED_M,
_NONE_SELECTED_N,
_NONE_SELECTED_F,
_NOT_SPECIFIED_M,
_NOT_SPECIFIED_N,
_NOT_SPECIFIED_F,
SHOW_MORE_SEARCH_RESULTS,
_PEOPLE_FOUND,
_PEOPLE_NOT_FOUND,
_WRITE_MESSAGE_TITLE,
_ADD_FRIEND_TITLE,
_UNFOLLOW_FRIEND_TITLE,
_REMOVE_FRIEND_TITLE,
_BLOCK_PERSON_TITLE,
_UNBLOCK_PERSON_TITLE,
_ONLINE_TITLE,
_ALL,
_FRIENDS,
search_loader;

var all_search_result = null; //Все результаты поиска (не AJAX запрос)

var search_timer; 

var NOTIFICATION = new NotificationGeneral();

if (locale === "en")
{
    TITLE_LOAD_ERROR = "Loading error";
    TITLE_LOAD_ERROR_CITY = "An error has occurred while loading the list of cities";
    FULL_LIST_TITLE = " - Full List - ";
    _OTHER_M = _OTHER_N = _OTHER_F = " - Other - ";
    _NONE_SELECTED_M = _NONE_SELECTED_N = _NONE_SELECTED_F = " - None selected - ";
    _NOT_SPECIFIED_M = _NOT_SPECIFIED_N = _NOT_SPECIFIED_F = " - Not specified - ";
    SHOW_MORE_SEARCH_RESULTS = "Show more ↓";
    _PEOPLE_NOT_FOUND = "Did not match any people";
    _WRITE_MESSAGE_TITLE = "Message";
    _ADD_FRIEND_TITLE = "Add to friends";
    _UNFOLLOW_FRIEND_TITLE = "Cancel";
    _REMOVE_FRIEND_TITLE = "Delete";
    _BLOCK_PERSON_TITLE = "Block";
    _UNBLOCK_PERSON_TITLE = "Unblock";
    _PEOPLE_FOUND = "People found";
    _ONLINE_TITLE = "Online";
    _ALL = "All";
    _FRIENDS = "Friends";
}
else
{
    TITLE_LOAD_ERROR = "Произошла ошибка при загрузке";
    TITLE_LOAD_ERROR_CITY = "Произошла ошибка при загрузке списка городов";
    FULL_LIST_TITLE = " - Полный список - ";
    _OTHER_M = " - Другой - ";
    _OTHER_N = " - Другое - ";
    _OTHER_F = " - Другая - ";
    _NONE_SELECTED_M = " - Не выбран - ";
    _NONE_SELECTED_N = " - Не выбрано - ";
    _NONE_SELECTED_F = " - Не выбрана - ";
    _NOT_SPECIFIED_M = " - Не указан - ";
    _NOT_SPECIFIED_N = " - Не указано - ";
    _NOT_SPECIFIED_F = " - Не указана - ";
    SHOW_MORE_SEARCH_RESULTS = "Показать ещё ↓";
    _PEOPLE_NOT_FOUND = "Никого не найдено. Возможно, это плохо. Попробуйте изменить критерии поиска.";
    _WRITE_MESSAGE_TITLE = "Сообщение";
    _ADD_FRIEND_TITLE = "Дружить";
    _UNFOLLOW_FRIEND_TITLE = "Отменить";
    _REMOVE_FRIEND_TITLE = "Удалить";
    _BLOCK_PERSON_TITLE = "В чёрный список";
    _UNBLOCK_PERSON_TITLE = "Убрать из чёрного списка";
    _PEOPLE_FOUND = "Людей найдено";
    _ONLINE_TITLE = "Онлайн";
    _ALL = "Все";
    _FRIENDS = "Друзья";
}

var communicate_block = {
    messageAction: function(button, user_id)
    {
        
    },
    friendshipActionTimeout: null,
    friendshipAction: function(button, user_id)
    {
        var link_nodes = button.childNodes;
        var link_button = link_nodes[0];
        var link_title = link_nodes[1];

        switch(link_button.className)
        {
            case "item-control-friend-icon-unfollow":
            {
                this.request("/members/friends/cancel/user_id/" + user_id, "", link_title, function()
                {
                    link_button.className = "item-control-friend-icon";
                    link_title.textContent = _ADD_FRIEND_TITLE;
                    
                    button.href = "/members/friends/add/user_id/" + user_id;
                });
            }
            break;
            
            case "item-control-friend-icon-remove":
            {
                this.request("/members/friends/remove/user_id/" + user_id, "", link_title, function()
                {
                    link_button.className = "item-control-friend-icon";
                    link_title.textContent = _ADD_FRIEND_TITLE;
                    button.href = "/members/friends/add/user_id/" + user_id;
                });
            }
            break;
            
            case "item-control-friend-icon":
            default:
            {
                this.request("/members/friends/add/user_id/" + user_id, "", link_title, function()
                {
                    link_button.className = "item-control-friend-icon-unfollow";
                    link_title.textContent = _UNFOLLOW_FRIEND_TITLE;
                    button.href = "/members/friends/cancel/user_id/" + user_id;
                });
            }
        }
        
    },
    request: function(url, data, element, callback)
    {
        /*
        clearTimeout(this.friendshipActionTimeout);
        
        this.friendshipActionTimeout = setTimeout(function()
        {
            element.parentNode.replaceChild(AJAX_LOADER_ELEMENT, element);
        }, ajax_loader_latensy);
        */
        var xhr = new XMLHttpRequest();
        xhr.open("POST", url);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.onreadystatechange = function()
        {
            if (xhr.readyState !== 4) return;
            if (xhr.status !== 200) //Error
            {
                throw new Error(CONST_TITLE.SERVER_ERROR + ", " + xhr.status);
            }
            if(typeof(callback) === "function")
            {
                //clearTimeout(this.friendshipActionTimeout);
                callback();
            }
        };
        xhr.send(data);
    },
    messageOpen: function(user_id)
    {
        
    }
};

window.addEventListener("load", initialization, false);

if(userSearchWindowKeydown !== undefined)
{
    document.onkeydown = userSearchWindowKeydown;
}

if(userSearchWindowClick !== undefined)
{
    window.addEventListener("click", userSearchWindowClick, true);
    window.addEventListener("scroll", userSearchScroll, false);
}

if("onpopstate" in window)
{
    window.addEventListener("popstate", popstateAction, false);
}

function initialization()
{
    var box_elements = document.querySelectorAll("#age_from, #toggle_age_from, #age_to, #toggle_age_to, #gender, #toggle_gender, #country, #toggle_country, #region, #toggle_region, #city, #toggle_city");
    for(var counter = 0; counter < box_elements.length; counter++)
    {
        box_elements[counter].addEventListener("click", boxMouseClick, false);
    }

    if(document.getElementById("city") !== null)
    {
        document.getElementById("city").oninput = function()
        {
            if(this.value === "")
            {
                changeCitiesList(false);
            }
            else
            {
                cityTextchange(this);
            }
        };
        
    }


    jQuery("a.advanced-search").on("click", function(event)
    {
        var advanced_search_block = document.getElementsByClassName("search-wrap")[0].getElementsByClassName("search-filters")[0];
        
        if(/inactive/.test(event.target.className))
        {
            jQuery(event.target).removeClass("inactive");
            jQuery(".search-result-wrap").empty(); //Очищаем список заблокированных пользователей
        }
        
        if(/active/.test(document.querySelector("a.btn.btn-link.lock-user").className))
        {
            removeClass(document.querySelector("a.btn.btn-link.lock-user"), "active");
        }
        
        if(window.history.pushState !== undefined && window.location.search === "?users=blocked")
        {
            window.history.replaceState(null, null, "/user_search" + window.location.hash);
            if(document.getElementsByClassName("search_count")[0].textContent !== "")
            {
                document.getElementsByClassName("search_count")[0].textContent = "";
            }
        }
        
        if(advanced_search_block !== undefined && advanced_search_block !== null)
        {
            if(advanced_search_block.style.display === "none")
            {
                document.querySelector("a.advanced-search span").className = "carat carat-top";
                jQuery(advanced_search_block).slideDown("fast");
            }
            else
            {
                document.querySelector("a.advanced-search span").className = "carat";
                jQuery(advanced_search_block).slideUp("fast");
            }
        }
        return false;
    });

    popstateAction();
    
    document.getElementById("user_search_container").onsubmit = searchAction;
    
    jQuery("#user_id, #email, #birthdate, #phone").on("keydown", function(event)
    {
        switch(event.which)
        {
            case jQuery.ui.keyCode.ENTER:
            {
                searchAction();
            }
            break;
        }
    });
}

function searchAction(event, request_url, popstate, blocked_only)
{
    var $ = jQuery;
    if(event !== null && event !== undefined)
    {
        event.preventDefault();
    }
    
    var search_count = 0;

    var advanced_search_button = document.querySelector("a.advanced-search");
    /////////////
    if(advanced_search_button && /inactive/.test(advanced_search_button.className))
    {
        jQuery(advanced_search_button).removeClass("inactive");
        jQuery(".search-result-wrap").empty();
    }

    if(/active/.test(document.querySelector("a.btn.btn-link.lock-user").className))
    {
        removeClass(document.querySelector("a.btn.btn-link.lock-user"), "active");
    }

    if(document.getElementsByClassName("search_count")[0] !== undefined && document.getElementsByClassName("search_count")[0].textContent !== "")
    {
        document.getElementsByClassName("search_count")[0].textContent = "";
    }

    /////////////
    if(request_url === null || request_url === undefined || request_url === "")
    {
        request_url = "/user/search/query";
        var filters = {};

        $('.search-wrap input, .search-wrap select').each(function(){
            if (!$.trim($(this).val())) return;
            if ($(this).attr('name') == 'gender') filters['gender'] = $(this).attr('data-value');
            else if ($(this).attr('type') == 'checkbox'){
                if (!$(this).is(':checked')){
                    return;
                }
                filters[$(this).attr('name')] = $(this).val();
            }
            else filters[$(this).attr('name')] = $(this).val();
        });
        request_url = request_url + (Object.keys(filters).length ? ('?' + jQuery.param(filters)) : '');
    }

    
    var window_request_url = request_url.replace("/user/search/query", "/user_search");
    if(window.history.pushState !== undefined && popstate !== true && window_request_url !== window.location.pathname + window.location.search)
    {
        if(blocked_only === true)
        {
            window.history.replaceState(null, null, window_request_url);
            if(document.getElementById("people_search_field").value !== "")
            {
                document.getElementById("people_search_field").value = "";
            }
        }
        else
        {
            window.history.pushState(null, null, window_request_url);
            document.getElementById("people_search_field").focus();
        }
    }   
    
    var search_container = document.getElementsByClassName("search-result-wrap").item(0);
        
    jQuery.ajax({
        url: request_url,
        dataType: "json",
        beforeSend: function()
        {
            clearTimeout(search_loader);
            
            search_loader = setTimeout(function()
            {
                jQuery(search_container).empty().append(getLoaderBar());
            }, ajax_loader_latensy);
            
            document.getElementById("user_search_container").onsubmit = function(event)
            {
                event.preventDefault();
                return false;
            };
        },
        success: function(response)
        {
            var fail_message_element, search_count_title, show_more_results_block, show_more_results_button, found_count;
            
            if(!("status" in response) || !("result" in response) || response.status !== true)
            {
                throw new Error(CONST_TITLE.SERVER_ERROR + ", " + xhr.status);
            }
            
            if(response.result.length === 0) //Ничего не найдено
            {
                jQuery(search_container).html('<div class="tip" style="flex-grow:1">'+_PEOPLE_NOT_FOUND+'</div>');
                jQuery(".settings-search .search_count").remove();
                return;
            }
            
            jQuery(search_container).empty();
            
            found_count = document.getElementsByClassName("settings-search").item(0).getElementsByClassName("search_count").item(0);
            
            if(found_count !== null)
            {
                if(response.result.length < 1000)
                {
                    if(found_count.textContent !== _PEOPLE_FOUND + ": " + response.result.length)
                    {
                        jQuery(found_count).empty().append(document.createTextNode(_PEOPLE_FOUND + ": " + response.result.length));
                    }
                }
                else
                {
                    jQuery(found_count).empty();
                }
            }
            
            if(response.result.length > 21)
            {
                all_search_result = response.result;
                search_count = 21;
            }
            else
            {
                all_search_result = null;
                search_count = response.result.length;
            }
            
            for(i = 0; i < search_count; i++)
            {
                addSearchResult(search_container, response.result[i]);
            }
            
            if(all_search_result !== null)
            {
                show_more_results_block = document.createElement("div");
                show_more_results_block.className = "show_more_block";
                
                show_more_results_button = document.createElement("span");
                show_more_results_button.className = "show_more_button";
                show_more_results_button.textContent = SHOW_MORE_SEARCH_RESULTS;
                show_more_results_button.onmousedown = function()
                {
                    showMoreAction(search_container, show_more_results_block);
                };
                
                show_more_results_block.appendChild(show_more_results_button);
                search_container.appendChild(show_more_results_block);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) 
        {
            console.error(jqXHR, textStatus, errorThrown);
            jQuery(".settings-search .search_count").remove();
            NOTIFICATION.error("Произошла ошибка при обработке результатов поиска");
        },
        complete: function() 
        {
            clearTimeout(search_loader);
            document.getElementById("user_search_container").onsubmit = searchAction;
            jQuery(search_container.getElementsByClassName("AJAX_LOADER_BLOCK")).remove();
        }
    });
    return false;
}

function addSearchResult(search_container, current_search_result, button_show_more)
{
    var search_result_item, item_info, item_photo_link, user_link, user_link_title_checked_symbol,
    online_title, item_control, item_control_message_link, item_control_friends_link, item_control_lock_link, item_control_message_icon,
    item_control_friends_icon, item_control_friends_text, item_control_lock_icon, item_control_lock_text;
    
    var user_phone, user_mail; /* Контактная инфа */
    var admin_auth, admin_edit, admin_remove;
    
    var is_admin = (document.getElementsByClassName("is_admin").item(0) !== null && document.getElementsByClassName("is_admin").item(0).value === "1");
    
    search_result_item = document.createElement("div");
    search_result_item.className = "search-result-item";
    
    if(is_admin)
    {
        if(parseInt(current_search_result.user_id) !== en4.user.viewer.id)
        {
            admin_auth = document.createElement("div");
            admin_remove = document.createElement("div");
            
            admin_auth.className = "admin_auth";
            admin_remove.className = "admin_remove";
            
            admin_auth.onclick = adminAuth;
            admin_remove.onclick = adminRemove;
            
            admin_auth.setAttribute("data-id", current_search_result.user_id);
            admin_remove.setAttribute("data-id", current_search_result.user_id);
            
            admin_auth.appendChild(document.createTextNode("Авторизоваться"));
            admin_remove.appendChild(document.createTextNode("Удалить"));

            search_result_item.appendChild(admin_auth);
            search_result_item.appendChild(admin_remove);
        }
        
        
        admin_edit = document.createElement("a");
        admin_edit.className = "admin_edit";
        admin_edit.href = "/members/edit/profile/id/" + current_search_result.user_id + "/";
        admin_edit.target = "_blank";
        
        admin_edit.appendChild(document.createTextNode("Редактировать"));
        
        search_result_item.appendChild(admin_edit);
    }
    
    item_info = document.createElement("div");
    
    item_info.className = "item-info";

    item_photo_link = document.createElement("a");
    item_photo_link.className = "result-item-user-img";
    item_photo_link.href = "profile/" + current_search_result.user_id;
    item_photo_link.target = "_blank";
    item_photo_link.title = current_search_result.displayname;
    
    if(current_search_result.user_photo === null)
    {
        if(current_search_result.gender==2){
            item_photo_link.style.background = "url('application/modules/User/externals/images/man.jpg')";
        }else{
            item_photo_link.style.background = "url('application/modules/User/externals/images/woman.jpg')";
        }
        item_photo_link.className += " nophoto";
    }
    else
    {
        item_photo_link.style.background = "url('" + current_search_result.user_photo + "')";
    }
    
    user_link = document.createElement("a");
    user_link.className = "result-item-user-name";
    user_link.href = "profile/" + current_search_result.user_id;
    user_link.target = "_blank";
    
    if (current_search_result.checked)
    {
        user_link_title_checked_symbol = document.createElement("span");
        user_link_title_checked_symbol.className = "checked_verified";
        
        //user_link_title_checked_symbol.onmouseover = pageVerifiedTipShow;
        //user_link_title_checked_symbol.onmouseout = pageVerifiedTipHide;
        
        user_link_title_checked_symbol.textContent = "✓";
        user_link.appendChild(document.createTextNode(current_search_result.displayname + " "));
        user_link.appendChild(user_link_title_checked_symbol);
    }
    else
    {
        user_link.textContent = current_search_result.displayname;
    }

    if (current_search_result.online_status)
    {
        online_title = document.createElement("div");
        online_title.className = "online";
        online_title.textContent = _ONLINE_TITLE;
    }
        
    item_info.appendChild(item_photo_link);
    item_info.appendChild(user_link);

    if (online_title !== undefined)
    {
        item_info.appendChild(online_title);
    }
    
    if(is_admin)
    {
        if(current_search_result.mobilephone)
        {
            user_phone = document.createElement("a");
            user_phone.className = "contact-user-info";
            user_phone.appendChild(document.createTextNode(current_search_result.mobilephone));
            
            item_info.appendChild(user_phone);
        }
        else if(current_search_result.email)
        {
            user_mail = document.createElement("a");
            user_mail.className = "contact-user-info";
            user_mail.href = "mailto:" + current_search_result.email;
            user_mail.target = "_blank";
            user_mail.appendChild(document.createTextNode(current_search_result.email));
            
            item_info.appendChild(user_mail);
        }
    }
    
    search_result_item.appendChild(item_info);
        
    item_control = document.createElement("div");
    item_control.setAttribute('class', 'item-control-wrapper')

    item_control_message_link = document.createElement("a");
    item_control_message_link.setAttribute('title', 'Отправить сообщение');
    item_control_friends_link = document.createElement("a");
    item_control_lock_link = document.createElement("a");

    item_control_message_link.className = "item-control-message";

    if(parseInt(current_search_result.user_id) !== en4.user.viewer.id)
    {
        item_control_message_link.href = "/messages/compose/to/" + current_search_result.user_id;
    }
    else
    {
        item_control_message_link.href = "/messages/inbox/";
    }

    item_control_message_link.target = "_blank";
    item_control_message_link.onclick = function ()
    {
        //communicate_block.messageAction(this, current_search_result.user_id);
        return true;
    };

    item_control_friends_link.className = "item-control-friend";
    item_control_friends_link.target = "_blank";

    //Кнопка отправки сообщений
    item_control_message_icon = document.createElement("span");
    item_control_message_icon.className = "item-control-message-icon";
    
    item_control_message_link.appendChild(item_control_message_icon);
    if(parseInt(current_search_result.user_id) !== en4.user.viewer.id)
    {
        item_control_message_link.appendChild(document.createTextNode(_WRITE_MESSAGE_TITLE));
    }
    else
    {
        item_control_message_link.appendChild(document.createTextNode("Сообщения"));
    }

    item_control_friends_icon = document.createElement("span"); //Кнопка «Добавить»/«Удалить» из друзей
    switch (current_search_result.friendship_status)
    {
        case "friend":
        {
            item_control_friends_icon.className = "item-control-friend-icon-remove";
            item_control_friends_text = document.createTextNode(_REMOVE_FRIEND_TITLE);
            item_control_friends_link.href = "/members/friends/remove/user_id/" + current_search_result.user_id;
            item_control_friends_link.setAttribute('title', 'Убрать из друзей');
        }
        break;

        case "follow":
        {
            item_control_friends_icon.className = "item-control-friend-icon-unfollow";
            item_control_friends_text = document.createTextNode(_UNFOLLOW_FRIEND_TITLE);
            item_control_friends_link.href = "/members/friends/cancel/user_id/" + current_search_result.user_id;
            item_control_friends_link.setAttribute('title', 'Отклонить дружбу');
        }
        break;

        default:
        {
            item_control_friends_icon.className = "item-control-friend-icon";
            item_control_friends_text = document.createTextNode(_ADD_FRIEND_TITLE);
            item_control_friends_link.href = "/members/friends/add/user_id/" + current_search_result.user_id;
            item_control_friends_link.setAttribute('title', 'Добавить в друзья');
        }
    }

    item_control_friends_link.appendChild(item_control_friends_icon);
    item_control_friends_link.appendChild(item_control_friends_text);
    item_control_friends_link.onclick = function()
    {
        if(en4.user.viewer.id === false)
        {
            return true;
        }
        communicate_block.friendshipAction(this, current_search_result.user_id);
        return false;
    };

    //Кнопка блокировки/разблокировки
    item_control_lock_icon = document.createElement("span");
    item_control_lock_icon.className = "item-control-lock-icon";

    if (current_search_result.blocked_status)
    {
        item_control_lock_text = document.createTextNode(_UNBLOCK_PERSON_TITLE);
        item_control_lock_link.setAttribute('title', _UNBLOCK_PERSON_TITLE);
    }
    else
    {
        item_control_lock_text = document.createTextNode(_BLOCK_PERSON_TITLE);
        item_control_lock_link.setAttribute('title', _BLOCK_PERSON_TITLE);
    }

    if (current_search_result.blocked_status)
    {
        item_control_lock_link.href = "/members/block/remove/user_id/" + current_search_result.user_id;
    }
    else
    {
        item_control_lock_link.href = "/members/block/add/user_id/" + current_search_result.user_id;
        item_control_lock_link.setAttribute('style', 'opacity: 0.5');
    }

    item_control_lock_link.className = "item-control-lock smoothbox";
    item_control_lock_link.target = "_blank";
    
    item_control_lock_link.appendChild(item_control_lock_icon);
    item_control_lock_link.appendChild(item_control_lock_text);
    item_control.appendChild(item_control_message_link);
    item_control.appendChild(item_control_friends_link);
    item_control.appendChild(item_control_lock_link);

    search_result_item.appendChild(item_control);
    
    if (button_show_more === undefined)
    {
        search_container.appendChild(search_result_item);
    }
    else
    {
        search_container.insertBefore(search_result_item, button_show_more);
    }
    Smoothbox.bind(item_control);
    if (typeof window.onUserSearchAddItem == 'function'){
        window.onUserSearchAddItem(jQuery(search_result_item), current_search_result);
    }
}
//Действие для кнопки «Показать еще»
function showMoreAction(search_container, show_more_results_block)
{
    var current_results_count = document.getElementsByClassName("search-result-wrap")[0].getElementsByClassName("search-result-item").length;
    if(all_search_result.length - 21 > current_results_count) //Отображаем ещё 21
    {
        for(i = current_results_count; i < (current_results_count + 21); i++)
        {
            addSearchResult(search_container, all_search_result[i], show_more_results_block);
        }
    }
    else //Отображаем оставшихся (count < 21), кнопку удаляем
    {
        for(i = current_results_count; i < all_search_result.length; i++)
        {
            addSearchResult(search_container, all_search_result[i], show_more_results_block);
        }
        show_more_results_block.parentNode.removeChild(show_more_results_block);
    }
}
function popstateAction()
{
    var search;
    var replace_url;
    if(window.location.search.indexOf("#") !== -1)
    {
        search = window.location.search.substr(1,window.location.search.indexOf("#"));
    }
    else
    {
        search = window.location.search.substr(1);
    }
    var url_keys = {};
    search.split('&').forEach(function(item)
    {
        item = item.split('=');
        url_keys[item[0]] = item[1] ? decodeURIComponent(item[1].replace(/\+/g, '%20')) : null;
    });
    
    if("name" in url_keys)
    {
        elementValueRewrite(document.getElementById("people_search_field"), decodeURIComponent(url_keys.name));
        document.getElementById("people_search_field").focus();
    }

    var standartInputs = ['email', 'phone', 'school_reference_status', 'bitsa_academy_status', 'zftsh_academy_status'];
    for(var i =0;i<standartInputs.length;i++){
        if (standartInputs[i] in url_keys){
            jQuery('.search-filters [name="' + standartInputs[i] + '"]').val(url_keys[ standartInputs[i] ]);
        }
    }
    
    if(search.length > 3 && document.getElementsByClassName("search-filters")[0].style.display == "none")
    {
        jQuery('.advanced-search').click();
    }
    jQuery('#online').prop('checked', ("online" in url_keys) && url_keys.online);
    
    if("age_from" in url_keys)
    {
        if(!(+url_keys.age_from > 6 && url_keys.age_from < 81))
        {
            url_keys.age_from = "0";
        }
        var age_from_list = document.getElementById("result_list_age_from").getElementsByTagName("li");
        for(i = 0; i < age_from_list.length; i++)
        {
            if(age_from_list[i].getAttribute("data-value") === url_keys.age_from)
            {
                age_from_list[i].onmousedown();
                break;
            }
        }
    }
    else if(document.getElementById("age_from").getAttribute("data-value") !== "0")
    {
        document.getElementById("result_list_age_from").getElementsByTagName("li")[0].onmousedown();
    }
    
    if("age_to" in url_keys)
    {
        if(!(+url_keys.age_to > 6 && url_keys.age_to < 81))
        {
            url_keys.age_to = "0";
        }
        var age_to_list = document.getElementById("result_list_age_to").getElementsByTagName("li");
        for(i = 0; i < age_to_list.length; i++)
        {
            if(age_to_list[i].getAttribute("data-value") === url_keys.age_to)
            {
                age_to_list[i].onmousedown();
                break;
            }
        }
    }
    else if(document.getElementById("age_to").getAttribute("data-value") !== "0")
    {
        document.getElementById("result_list_age_to").getElementsByTagName("li")[0].onmousedown();
    }
    
    if("gender" in url_keys)
    {
        if(url_keys.gender !== "1" && url_keys.gender !== "2")
        {
            url_keys.gender = "0";
        }
        for(i = 0; i < 3; i++)
        {
            if(document.getElementById("result_list_gender").getElementsByTagName("li")[i].getAttribute("data-value") === url_keys.gender)
            {
                document.getElementById("result_list_gender").getElementsByTagName("li")[i].onmousedown();
                break;
            }
        }
    }
    else if(document.getElementById("gender").getAttribute("data-value") !== "0")
    {
        document.getElementById("result_list_gender").getElementsByTagName("li")[0].onmousedown();
    }
    
    if("country" in url_keys)
    {
        var countries_list = document.getElementById("result_list_country").getElementsByTagName("li");
        for(i = 1; i < countries_list.length; i++)
        {
            if(countries_list[i].innerHTML === url_keys.country)
            {
                countries_list[i].onmousedown();
                break;
            }
        }
    }
    else if(document.getElementById("gender").getAttribute("data-value") !== "0")
    {
        document.getElementById("result_list_country").getElementsByTagName("li")[0].onmousedown();
    }
    
    if("region" in url_keys && url_keys.region !== "")
    {
        var current_region = decodeURIComponent(url_keys.region);
        var regions_list = document.getElementById("result_list_region").getElementsByTagName("li");
        for(i = 1; i < regions_list.length; i++)
        {
            if(regions_list[i].textContent === current_region)
            {
                regions_list[i].onmousedown();
                break;
            }
        }
        elementValueRewrite(document.getElementById("region"), current_region);
    }
    else if(document.getElementById("region").value !== "")
    {
        document.getElementById("region").value = "";
        document.getElementById("result_list_region").getElementsByTagName("li")[0].onmousedown();
    }
    
    if("city" in url_keys && url_keys.city !== "")
    {
        var current_city = decodeURIComponent(url_keys.city);
        var cities_list = document.getElementById("result_list_city").getElementsByTagName("li");
        for(i = 1; i < cities_list.length; i++)
        {
            if(cities_list[i].textContent === current_city)
            {
                cities_list[i].onmousedown();
                break;
            }
        }
        elementValueRewrite(document.getElementById("city"), current_city);
    }
    else if(document.getElementById("city").value !== "")
    {
        document.getElementById("city").value = "";
        document.getElementById("result_list_city").getElementsByTagName("li")[0].onmousedown();
    }
    if(replace_url !== undefined)
    {
        window.history.replaceState(null, null, replace_url);
    }
    searchAction(null, "/user/search/query" + window.location.search + window.location.hash, true);
}
function searchSelectToggle(element)
{
    if(element.parentNode.className === "dropdown open")
    {
        element.parentNode.className = "dropdown";
        jQuery(element.parentNode.getElementsByTagName("div")[0]).slideToggle("fast");
    }
    else
    {
        element.parentNode.className = "dropdown open";
        jQuery(element.parentNode.getElementsByTagName("div")[0]).slideToggle("fast");
    }
}

function fieldMousedown()
{
    var current_container = document.getElementById("result_list_" + this.id);
    if(current_container.style.display !== "none")
    {
        current_container.style.display = "none";
        removeClass(document.getElementById("toggle_" + this.id), "active");
    }
    else
    {
        elementShow(current_container);
        current_container.scrollTop = 0;
        if(current_container.getElementsByTagName("li").length !== 0)
        {
            if(!document.getElementById("toggle_" + this.id).className.match("active"))
                document.getElementById("toggle_" + this.id).className += " active";
        }
        var hover_elements = current_container.getElementsByClassName("hover");
        var hover_count = hover_elements.length;
        var menu_elements = current_container.getElementsByTagName("li");
        var menu_elements_count = menu_elements.length;
        if(menu_elements_count === 0)
            return;
        if(hover_count === 0)
        {
            current_container.getElementsByTagName("li")[0].className += " hover";
        }
        else if(hover_count > 1)
        {
            for(var i = 0; i < hover_count; i++)
            {
                removeClass(hover_elements[i], "hover");
            }
            current_container.getElementsByTagName("li")[0].className += " hover";
        }
    }
    return false;
}
function boxMouseClick()
{
    var instant =(this.id.replace(/toggle_/, "") === "search_type");
    var result_block = document.getElementById("result_list_" + this.id.replace(/toggle_/, ""));
    if(result_block.style.display === "none" && result_block.getElementsByTagName("li").length !== 0)
    {
        if(instant === true)
        {
            result_block.style.display = "";
        }
        else
        {
            jQuery(result_block).slideDown("fast");
        }
        if(result_block.getElementsByClassName("hover").length === 0)
        {
            result_block.getElementsByTagName("li")[0].className += "hover";
            if(result_block.scrollTop !== 0)
            {
                result_block.scrollTop = 0;
            }
        }
        else
        {
            result_block.scrollTop = result_block.getElementsByClassName("hover")[0].offsetTop;
        }
    }
    else if(instant === true)
    {
        result_block.style.display = "none";
    }
    else
    {
        jQuery(result_block).slideUp("fast");
    }
    //Все остальные списки должны быть закрыты
    var result_lists = document.getElementsByClassName("result_list");
    for(i = 0; i < result_lists.length; i++)
    {
        if(result_lists[i].style.display !== "none" && (result_block.id !== result_lists[i].id))
        {
            result_lists[i].style.display = "none";
        }
    }
    return false;
}



function showAllCountries(element)
{
    var items_container = element.parentNode.parentNode;
    var input_list = items_container.getElementsByTagName("ul")[0];
    var bold_country_name;
    var active_country_name;
    jQuery.ajax(
    {
        url: "https://api.vk.com/method/database.getCountries",
        dataType: "jsonp",
        data:
        {
            v: VK_API_VERSION,
            lang: locale,
            count: "250",
            need_all: "1",
            access_token: en4.core.vk_token,
        },
        beforeSend: function()
        {

        },
        success: function(all_countries_list)
        {
            //Удаляем дочерние элементы-страны
            while (input_list.lastChild)
            {
                if(/bold/.test(input_list.lastChild.className))
                {
                    bold_country_name = input_list.lastChild.textContent;
                }
                if(/active/.test(input_list.lastChild.className))
                {
                    active_country_name = input_list.lastChild.textContent;
                }
                input_list.removeChild(input_list.lastChild);
            }
            var non_selected_item = document.createElement("li");
            non_selected_item.setAttribute("onmousedown", "selectItem(this, '')");
            non_selected_item.onmouseover = function()
            {
                highlightItem(this);
            };
            non_selected_item.textContent = _NONE_SELECTED_F;
            input_list.appendChild(non_selected_item);
            for (i = 0; i < all_countries_list.response.count; i++)
            {
                var adding_item;
                adding_item = document.createElement("li");
                if(bold_country_name !== undefined && all_countries_list.response.items[i].title === bold_country_name)
                {
                    adding_item.className += " bold";
                }
                if(active_country_name !== undefined && all_countries_list.response.items[i].title === active_country_name)
                {
                    adding_item.className += " active";
                }
                adding_item.setAttribute("data-value", all_countries_list.response.items[i].id);
                adding_item.setAttribute("onmousedown", "selectItem(this, '" + all_countries_list.response.items[i].title.replace("'", "\\'") + "', " + all_countries_list.response.items[i].id + ");");
                adding_item.onmouseover = function()
                {
                    highlightItem(this);
                };
                adding_item.textContent = all_countries_list.response.items[i].title;
                input_list.appendChild(adding_item);
            }
            if(items_container.getElementsByClassName("active")[0] !== undefined)
            {
                items_container.scrollTop = items_container.getElementsByClassName("active")[0].offsetTop;
                if(!/hover/.test(items_container.getElementsByClassName("active")[0].className))
                {
                    items_container.getElementsByClassName("active")[0].className += " hover";
                }
            }
            else
            {
                items_container.scrollTop = 0;
                if(items_container.firstChild !== null && items_container.firstChild !== undefined)
                {
                    items_container.firstChild.className += " hover";
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            console.error(jqXHR, textStatus, errorThrown);
            NOTIFICATION.error(TITLE_LOAD_ERROR);
        },
        complete: function() 
        {

        }
    });
}
function changeRegionsList()
{
    document.getElementById("region").value = "";
    var items_container = document.getElementById("result_list_region");
    var input_list = items_container.getElementsByTagName("ul")[0];
    var current_country_id;
    if(+document.getElementById("country").getAttribute("data-value") > 0)
    {
        current_country_id = document.getElementById("country").getAttribute("data-value");
    }
    else
    {
        current_country_id = 1;
    }
    jQuery.ajax(
    {
        url: "https://api.vk.com/method/database.getRegions",
        dataType: "jsonp",
        data:
        {
            v: VK_API_VERSION,
            lang: locale,
            country_id: current_country_id,
            count: "1000",
            access_token: en4.core.vk_token,
        },
        success: function(regions_list)
        {
            cityFilterTranslate(function(regions_list){
                if("error" in regions_list)
                {
                    return;
                }

                jQuery(input_list).empty(); //Удаляем дочерние элементы-регионы

                if(regions_list.response.items.length === 0 && document.getElementsByClassName("region-search")[0] !== undefined && document.getElementsByClassName("region-search")[0].style.display !== "none")
                {
                    document.getElementsByClassName("region-search")[0].style.display = "none";
                }
                else if(document.getElementsByClassName("region-search")[0] !== undefined && document.getElementsByClassName("region-search")[0].style.display === "none")
                {
                    document.getElementsByClassName("region-search")[0].style.display = "";
                }
                var non_selected_item = document.createElement("li");
                non_selected_item.setAttribute("onmousedown", "selectItem(this, '')");
                non_selected_item.onmouseover = function()
                {
                    highlightItem(this);
                };
                non_selected_item.textContent = _NONE_SELECTED_M;
                input_list.appendChild(non_selected_item);
                for (i = 0; i < regions_list.response.count; i++)
                {
                    var adding_item;
                    adding_item = document.createElement("li");
                    adding_item.onmouseover = function()
                    {
                        highlightItem(this);
                    };
                    adding_item.setAttribute("data-value", regions_list.response.items[i].id);
                    adding_item.setAttribute("onmousedown", "selectItem(this, '" + regions_list.response.items[i].title.replace("'", "\\'") + "', " + regions_list.response.items[i].id + ");");
                    adding_item.textContent = regions_list.response.items[i].title;
                    input_list.appendChild(adding_item);
                }
                if(input_list.firstChild !== null && input_list.firstChild !== undefined)
                {
                    input_list.firstChild.className += " hover";
                    items_container.scrollTop = 0;
                }
            }, regions_list);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            console.error(jqXHR, textStatus, errorThrown);
            NOTIFICATION.error(TITLE_LOAD_ERROR);
        }
    });
}
function changeCitiesList(load_all)
{
    document.getElementById("city").value = "";
    var items_container = document.getElementById("result_list_city");
    var input_list = items_container.getElementsByTagName("ul")[0];
    var current_country_id;
    var current_region_id;
    if(+document.getElementById("country").getAttribute("data-value") > 0)
    {
        current_country_id = document.getElementById("country").getAttribute("data-value");
    }
    else
    {
        current_country_id = 1;
    }
    if(+document.getElementById("region").getAttribute("data-value") > 0 && load_all === false)
    {
        current_region_id = document.getElementById("region").getAttribute("data-value");
    }
    else
    {
        current_region_id = "";
    }
    jQuery.ajax(
    {
        url: "https://api.vk.com/method/database.getCities",
        dataType: "jsonp",
        data:
        {
           v: VK_API_VERSION,
            lang: locale,
            country_id: current_country_id,
            count: "300",
            need_all: "0",
            region_id: current_region_id,
            access_token: en4.core.vk_token,
        },
        success: function(cities_list)
        {
            cityFilterTranslate(function(cities_list){
                if("error" in cities_list)
                {
                    if("error_code" in cities_list.error)
                    {
                        if(cities_list.error_code === 14)
                        {
                            var current_request = "https://api.vk.com/method/database.getCities?v=" + VK_API_VERSION + "&lang=" + locale + "&country_id=" + current_country_id + "&need_all=0&region_id=" + current_region_id;
                            getVKCaptcha(current_request, cities_list.error.error_code.captcha_sid, cities_list.error.error_code.captcha_img, changeCitiesList);
                        }
                    }
                    return;
                }
                //Удаляем дочерние элементы-города
                while (input_list.lastChild)
                {
                    input_list.removeChild(input_list.lastChild);
                }
                var city_count;
                if(cities_list.response.count > 300)
                {
                    city_count = 300;
                }
                else
                {
                    city_count = cities_list.response.count;
                }
                var non_selected_item = document.createElement("li");
                non_selected_item.setAttribute("onmousedown", "selectItem(this, '')");
                non_selected_item.onmouseover = function()
                {
                    highlightItem(this);
                };
                non_selected_item.textContent = _NONE_SELECTED_M;
                input_list.appendChild(non_selected_item);
                for (i = 0; i < city_count; i++)
                {
                    var adding_item;
                    adding_item = document.createElement("li");
                    if(current_region_id !== "")
                    {
                        adding_item.className = "border";
                    }
                    if("important" in cities_list.response.items[i])
                    {
                        adding_item.className += " bold";
                    }
                    adding_item.onmouseover = function()
                    {
                        highlightItem(this);
                    };
                    adding_item.setAttribute("data-value", cities_list.response.items[i].id);
                    if("area" in cities_list.response.items[i])
                    {
                        adding_item.setAttribute("onmousedown", "selectItem(this, '" + cities_list.response.items[i].title.replace("'", "\\'") + "', " + cities_list.response.items[i].id + ", '" + cities_list.response.items[i].region.replace("'", "\\'") + "', '" + cities_list.response.items[i].area.replace("'", "\\'") + "');");
                        var city_title_element = document.createTextNode(cities_list.response.items[i].title);
                        var first_break_element = document.createElement("br");
                        var region_content_element = document.createElement("span");
                        var area_content_title = document.createTextNode(cities_list.response.items[i].area);
                        var second_break_element = document.createElement("br");
                        var region_content_title = document.createTextNode(cities_list.response.items[i].region);
                        region_content_element.appendChild(area_content_title);
                        region_content_element.appendChild(second_break_element);
                        region_content_element.appendChild(region_content_title);
                        adding_item.appendChild(city_title_element);
                        adding_item.appendChild(first_break_element);
                        adding_item.appendChild(region_content_element);
                    }
                    else if("region" in cities_list.response.items[i])
                    {
                        adding_item.setAttribute("onmousedown", "selectItem(this, '" + cities_list.response.items[i].title.replace("'", "\\'") + "', " + cities_list.response.items[i].id + ", '" + cities_list.response.items[i].region.replace("'", "\\'") + "');");
                        var break_element = document.createElement("br");
                        var region_content_element = document.createElement("span");
                        region_content_element.textContent = cities_list.response.items[i].region;
                        var city_title_element = document.createTextNode(cities_list.response.items[i].title);
                        adding_item.appendChild(city_title_element);
                        adding_item.appendChild(break_element);
                        adding_item.appendChild(region_content_element);
                    }
                    else
                    {
                        adding_item.setAttribute("onmousedown", "selectItem(this, '" + cities_list.response.items[i].title.replace("'", "\\'") + "', " + cities_list.response.items[i].id + ");");
                        adding_item.textContent = cities_list.response.items[i].title;
                    }
                    input_list.appendChild(adding_item);
                }
                if(input_list.firstChild !== null && input_list.firstChild !== undefined)
                {
                    input_list.firstChild.className += " hover";
                    items_container.scrollTop = 0;
                }
            }, cities_list);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            console.error(jqXHR, textStatus, errorThrown);
            NOTIFICATION.error(TITLE_LOAD_ERROR);
        }
    });
}
function cityTextchange(input_element)
{
    var items_container = document.getElementById("result_list_" + input_element.id);
    var input_list = items_container.getElementsByTagName("ul")[0];
    var current_country_id;
    var current_region_id;
    if(+document.getElementById("country").getAttribute("data-value") > 0)
    {
        current_country_id = document.getElementById("country").getAttribute("data-value");
    }
    else
    {
        current_country_id = 1;
    }
    if(+document.getElementById("region").getAttribute("data-value") > 0)
    {
        current_region_id = document.getElementById("region").getAttribute("data-value");
    }
    else
    {
        current_region_id = "";
    }
    jQuery.ajax(
    {
        url: "https://api.vk.com/method/database.getCities",
        dataType: "jsonp",
        data:
        {
           v: VK_API_VERSION,
            lang: locale,
            country_id: current_country_id,
            region_id: current_region_id,
            need_all: "1",
            q: input_element.value,
            access_token: en4.core.vk_token,
        },
        success: function(cities_list)
        {
            cityFilterTranslate(function(cities_list){
                //Удаляем дочерние элементы-города
                while (input_list.lastChild)
                {
                    input_list.removeChild(input_list.lastChild);
                }
                var city_count;
                if(cities_list.response.count > 100)
                {
                    city_count = 100;
                }
                else
                {
                    city_count = cities_list.response.count;
                }
                for (i = 0; i < city_count; i++)
                {
                    var adding_item;
                    adding_item = document.createElement("li");
                    adding_item.className = "border";
                    if("important" in cities_list.response.items[i])
                    {
                        adding_item.className += " bold";
                    }
                    adding_item.onmouseover = function()
                    {
                        highlightItem(this);
                    };
                    var searching_city = new RegExp(input_element.value, "i");
                    var thin_text_position = cities_list.response.items[i].title.search(searching_city);
                    if("area" in cities_list.response.items[i])
                    {
                        adding_item.setAttribute("onmousedown", "selectItem(this, '" + cities_list.response.items[i].title.replace("'", "\\'") + "', " + cities_list.response.items[i].id + ", '" + cities_list.response.items[i].region.replace("'", "\\'") + "', '" + cities_list.response.items[i].area.replace("'", "\\'") + "');");
                        var city_first_title_element;
                        var city_match_title_element;
                        var city_last_title_element;
                        if(thin_text_position !== -1)
                        {
                            city_first_title_element = document.createTextNode(cities_list.response.items[i].title.substring(0, thin_text_position));
                            city_match_title_element = document.createElement("b");
                            city_match_title_element.textContent = cities_list.response.items[i].title.substr(thin_text_position, input_element.value.length);
                            city_last_title_element = document.createTextNode(cities_list.response.items[i].title.substring(thin_text_position + input_element.value.length));
                        }
                        else
                        {
                            city_first_title_element = document.createTextNode(cities_list.response.items[i].title);
                        }
                        var first_break_element = document.createElement("br");
                        var region_content_element = document.createElement("span");
                        var area_content_title = document.createTextNode(cities_list.response.items[i].area);
                        var second_break_element = document.createElement("br");
                        var region_content_title = document.createTextNode(cities_list.response.items[i].region);
                        region_content_element.appendChild(area_content_title);
                        region_content_element.appendChild(second_break_element);
                        region_content_element.appendChild(region_content_title);
                        if(city_first_title_element !== undefined)
                        {
                            adding_item.appendChild(city_first_title_element);
                        }
                        if(city_match_title_element !== undefined)
                        {
                            adding_item.appendChild(city_match_title_element);
                        }
                        if(city_last_title_element !== undefined)
                        {
                            adding_item.appendChild(city_last_title_element);
                        }
                        adding_item.appendChild(first_break_element);
                        adding_item.appendChild(region_content_element);
                    }
                    else if("region" in cities_list.response.items[i])
                    {
                        adding_item.setAttribute("onmousedown", "selectItem(this, '" + cities_list.response.items[i].title.replace("'", "\\'") + "', " + cities_list.response.items[i].id + ", '" + cities_list.response.items[i].region.replace("'", "\\'") + "');");
                        var break_element = document.createElement("br");
                        var region_content_element = document.createElement("span");
                        region_content_element.textContent = cities_list.response.items[i].region;
                        if(thin_text_position !== -1)
                        {
                            var city_first_title_element = document.createTextNode(cities_list.response.items[i].title.substring(0, thin_text_position));
                            var city_match_title_element = document.createElement("b");
                            city_match_title_element.textContent = cities_list.response.items[i].title.substr(thin_text_position, input_element.value.length);
                            var city_last_title_element = document.createTextNode(cities_list.response.items[i].title.substring(thin_text_position + input_element.value.length));
                            adding_item.appendChild(city_first_title_element);
                            adding_item.appendChild(city_match_title_element);
                            adding_item.appendChild(city_last_title_element);
                        }
                        else
                        {
                            var city_title_element = document.createTextNode(cities_list.response.items[i].title);
                            adding_item.appendChild(city_title_element);
                        }
                        adding_item.appendChild(break_element);
                        adding_item.appendChild(region_content_element);
                    }
                    else
                    {
                        adding_item.setAttribute("onmousedown", "selectItem(this, '" + cities_list.response.items[i].title.replace("'", "\\'") + "', " + cities_list.response.items[i].id + ");");
                        if(thin_text_position !== -1)
                        {
                            var city_first_title_element = document.createTextNode(cities_list.response.items[i].title.substring(0, thin_text_position));
                            var city_match_title_element = document.createElement("b");
                            city_match_title_element.textContent = cities_list.response.items[i].title.substr(thin_text_position, input_element.value.length);
                            var city_last_title_element = document.createTextNode(cities_list.response.items[i].title.substring(thin_text_position + input_element.value.length));
                            adding_item.appendChild(city_first_title_element);
                            adding_item.appendChild(city_match_title_element);
                            adding_item.appendChild(city_last_title_element);
                        }
                        else
                        {
                            adding_item.textContent = cities_list.response.items[i].title;
                        }
                    }
                    input_list.appendChild(adding_item);
                }
                if(input_list.firstChild !== null && input_list.firstChild !== undefined)
                {
                    input_list.firstChild.className += " hover";
                    items_container.scrollTop = 0;
                    if(items_container.style.display === "none")
                    {
                        items_container.style.display = "";
                    }
                }
                else if(items_container.style.display !== "none")
                {
                    items_container.style.display = "none";
                }
            }, cities_list);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            console.error(jqXHR, textStatus, errorThrown);
            NOTIFICATION.error(TITLE_LOAD_ERROR);
        }
    });
}

function userSearchScroll()
{
    var show_more_block = document.querySelector(".show_more_block");
    if(show_more_block === null || show_more_block === undefined)
    {
        return;
    }
    
    if(window.scrollY + window.innerHeight - show_more_block.offsetTop > 0)
    {
        document.querySelector(".show_more_block .show_more_button").onmousedown();
    }
}

function adminAuth(event)
{
    jQuery.ajax({
        url: "/admin/user/manage/login/",
        dataType: "json",
        type: "POST",
        data:
        {
            "format": "json",
            "id": event.target.getAttribute("data-id")
        },
        beforeSend: function()
        {
            jQuery(event.target).css({"border": "none", "height": jQuery(event.target).innerHeight() + "px", "width": jQuery(event.target).innerWidth() + "px"}).empty().append(getLoaderImg());
            event.target.onclick = function()
            {
                return false;
            };
        },
        success: function(response, textStatus, jqXHR)
        {
            if(response.status !== true)
            {
                console.error(response, textStatus, jqXHR);
                return;
            }

            window.location.replace("/");
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            console.error(jqXHR, textStatus, errorThrown);
            jQuery(event.target).empty().append(document.createTextNode("Авторизоваться"));
            event.target.onclick = adminAuth;
        }
    });
    
    return false;
}

function adminRemove(event)
{
    jQuery.ajax({
        url: "/admin/user/manage/delete/id/" + event.target.getAttribute("data-id"),
        type: "POST",
        beforeSend: function()
        {
            jQuery(event.target).css({"border": "none", "height": jQuery(event.target).innerHeight() + "px", "width": jQuery(event.target).innerWidth() + "px"}).empty().append(getLoaderImg());
            event.target.onclick = function()
            {
                return false;
            };
        },
        success: function(response, textStatus, jqXHR)
        {
            jQuery(event.target.parentNode).remove();
            NOTIFICATION.warning("Все данные о пользователе удалены");
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            console.error(jqXHR, textStatus, errorThrown);
            jQuery(event.target).empty().append(document.createTextNode("Удалить"));
            event.target.onclick = adminRemove;
            
            NOTIFICATION.error("Произошла ошибка при удалении пользователя");
        }
    });
}
