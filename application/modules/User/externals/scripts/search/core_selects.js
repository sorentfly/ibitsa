function listNav(forward)
{
    var result_lists = document.getElementsByClassName("result_list");
    var active_result_lists = [];
    for(i = 0; i < result_lists.length; i++)
    {
        if(result_lists[i].style.display !== "none")
        {
            active_result_lists.push(result_lists[i].id);
        }
    }
    if(active_result_lists.length === 0)
    {
        return true;
    }
    if(active_result_lists.length > 1)
    {
        for(i = 1; i < active_result_lists.length; i++)
        {
            document.getElementById(active_result_lists[i]).style.display = "none";
        }
    }
    var current_container = document.getElementById(active_result_lists[0]);
    var hover_elements = current_container.getElementsByClassName("hover");
    if(hover_elements.length === 0)
    {
        var list_elements = current_container.getElementsByTagName("li");
        if(list_elements[0] !== undefined)
        {
            list_elements[0].className += " hover";
            if(list_elements[0].parentNode.parentNode.id === "result_list_people_search_field" && document.getElementById("people_search_field").value !== trim(list_elements[0].textContent))
            {
                document.getElementById("people_search_field").value = trim(list_elements[0].textContent);
            }
            return false;
        }
        else
        {
            return true;
        }
    }
    if(forward === true) //Движение по списку вперёд
    {
        if(hover_elements[0].nextElementSibling !== null && hover_elements[0].nextElementSibling !== undefined)
        {
            var scroll_difference = current_container.offsetHeight - (hover_elements[0].nextElementSibling.offsetTop - current_container.scrollTop);
            if(scroll_difference < hover_elements[0].nextElementSibling.offsetHeight)
            {
                current_container.scrollTop += hover_elements[0].nextElementSibling.offsetHeight - scroll_difference;
            }
            if(hover_elements[0].nextElementSibling.parentNode.parentNode.id === "result_list_people_search_field" && document.getElementById("people_search_field").value !== trim(hover_elements[0].nextElementSibling.textContent))
            {
                document.getElementById("people_search_field").value = trim(hover_elements[0].nextElementSibling.textContent);
            }
            hover_elements[0].nextElementSibling.className += " hover";
            removeClass(hover_elements[0], "hover");
        }
    }
    else  //Движение по списку назад
    {
        if(hover_elements[0].previousElementSibling !== null && hover_elements[0].previousElementSibling !== undefined)
        {
            var current_element = hover_elements[0];
            var scroll_difference = hover_elements[0].previousElementSibling.offsetTop - current_container.scrollTop;
            if(scroll_difference < 0)
            {
                current_container.scrollTop = hover_elements[0].previousElementSibling.offsetTop;
            }
            if(hover_elements[0].previousElementSibling.parentNode.parentNode.id === "result_list_people_search_field" && document.getElementById("people_search_field").value !== trim(hover_elements[0].previousElementSibling.textContent))
            {
                document.getElementById("people_search_field").value = trim(hover_elements[0].previousElementSibling.textContent);
            }
            hover_elements[0].previousElementSibling.className += " hover";
            removeClass(current_element, "hover");
        }
    }
    return false;
}

function highlightItem(element)
{
    var hover_elements = element.parentNode.parentNode.getElementsByClassName("hover");
    for(var i = 0; i < hover_elements.length; i++)
    {
        removeClass(hover_elements[i], "hover");
    }
    element.className += " hover";
}

function userSearchWindowClick(event)
{
    if(/select_box|custom-combobox-toggle/.test(event.target.className))
    {
        return false;
    }
    var result_lists = document.getElementsByClassName("result_list");
    for(i = 0; i < result_lists.length; i++)
    {
        if(result_lists[i].style.display !== "none")
        {
            result_lists[i].style.display = "none";
        }
    }
}

function selectItem(element, value, data_value, item_one, item_two)
{
    var input_container;
    var items_container = element.parentNode.parentNode;
    var active_elements = items_container.getElementsByClassName("active");
    var hover_elements = items_container.getElementsByClassName("hover");
    for(i = 0; i < active_elements.length; i++)
    {
        removeClass(active_elements[i], "hover");
        removeClass(active_elements[i], "active");
    }
    for(i = 0; i < hover_elements.length; i++)
    {
        removeClass(hover_elements[i], "active");
        removeClass(hover_elements[i], "hover");
    }
    if(items_container.id === "result_list_people_search_field" || items_container.id === "result_list_user_search_field")
    {
        element.className += " hover";
    }
    else
    {
        element.className += " active hover";
    }
    input_container = document.querySelector("input#" + items_container.id.replace("result_list_", ""));
    if(value !== undefined && value !== null)
    {
        input_container.value = value;
    }
    else
    {
        input_container.value = element.textContent;
    }
    if(data_value !== undefined && data_value !== null && data_value !== "")
    {
        input_container.setAttribute("data-value", data_value);
    }
    else if(input_container.getAttribute("data-value") !== null && input_container.getAttribute("data-value") !== undefined && input_container.getAttribute("data-value") !== "")
    {
        input_container.removeAttribute("data-value");
    }
    if(items_container.style.display !== "none")
    {
        items_container.style.display = "none";
    }
    switch(input_container.id)
    {
        case "age_from":
        {
            var min_age = +input_container.value;
            var age_to_container = document.getElementById("result_list_age_to").getElementsByTagName("ul")[0];
            var age_to_list = age_to_container.getElementsByTagName("li");
            if(input_container.value !== "" && min_age > 6)
            {
                if(+document.getElementById("age_to").value < min_age)
                {
                    document.getElementById("age_to").value = "";
                    document.getElementById("age_to").setAttribute("data-value", "");
                }
                var age_to = +age_to_list[1].textContent;
                if(age_to < min_age)
                {
                    while(+age_to_list[1].textContent < min_age)
                    {
                        age_to_container.removeChild(age_to_list[1]);
                    }
                }
                else
                {
                   var start_age = age_to_list[1].textContent;
                   for(i = start_age; i > 6; i--)
                   {
                        var current_age_element = document.createElement("li");
                        current_age_element.onmouseover = function()
                        {
                            highlightItem(this);
                        };
                        current_age_element.setAttribute("onmousedown", "selectItem(this, '" + i + "', " + i + ");");
                        current_age_element.textContent = i;
                       age_to_container.insertBefore(current_age_element, age_to_list[1]);
                   }
                }
            }
            else
            {
                var start_age = age_to_list[1].textContent;
                for(i = start_age; i > 6; i--)
                {
                     var current_age_element = document.createElement("li");
                     current_age_element.onmouseover = function()
                     {
                         highlightItem(this);
                     };
                     current_age_element.setAttribute("onmousedown", "selectItem(this, '" + i + "', " + i + ");");
                     current_age_element.textContent = i;
                    age_to_container.insertBefore(current_age_element, age_to_list[1]);
                }
            }
        }
        break;
        case "country":
        {
            if(+input_container.getAttribute("data-value") > 0)
            {
                changeRegionsList();
                changeCitiesList(true);
                if(document.getElementsByClassName("region-search")[0] !== undefined && document.getElementsByClassName("region-search")[0].style.display === "none")
                {
                    document.getElementsByClassName("region-search")[0].style.display = "";
                }
                if(document.getElementsByClassName("city-search")[0] !== undefined && document.getElementsByClassName("city-search")[0].style.display === "none")
                {
                    document.getElementsByClassName("city-search")[0].style.display = "";
                }
            }
            else
            {
                if(document.getElementsByClassName("region-search")[0] !== undefined && document.getElementsByClassName("region-search")[0].style.display !== "none")
                {
                    document.getElementsByClassName("region-search")[0].style.display = "none";
                }
                if(document.getElementsByClassName("city-search")[0] !== undefined && document.getElementsByClassName("city-search")[0].style.display !== "none")
                {
                    document.getElementsByClassName("city-search")[0].style.display = "none";
                }
            }
        }
        break;
        case "region":
        {
            if(+input_container.getAttribute("data-value") > 0)
            {
                changeCitiesList(false);
            }
        }
        break;
        case "city":
        {
            if (document.getElementById("region").value) break;

            if(item_one !== undefined )
            {
                if(document.getElementById("region") !== null)
                {
                    var region_items = document.getElementById("result_list_region").getElementsByTagName("li");
                    document.getElementById("region").value = item_one;
                    for(i = 0; i < region_items.length; i++)
                    {
                        if(region_items[i].textContent === item_one)
                        {
                            document.getElementById("region").setAttribute("data-value", region_items[i].getAttribute("data-value"));
                            for(var j = 0; j < region_items.length; j++)
                            {
                                removeClass(region_items[j], "hover");
                                removeClass(region_items[j], "active");
                            }
                            region_items[i].className += " active hover";
                        }
                    }
                }
            }
            else
            {
                if(document.getElementById("region") !== null)
                {
                    var region_items = document.getElementById("result_list_region").getElementsByTagName("li");
                    document.getElementById("region").value = "";
                    document.getElementById("region").removeAttribute("data-value");
                    for(i = 0; i < region_items.length; i++)
                    {
                        removeClass(region_items[i], "hover");
                        removeClass(region_items[i], "active");
                    }
                    region_items[0].className = " active hover";
                }
            }
            if(item_two !== undefined)
            {
                if(document.getElementById("area") !== null)
                {
                    document.getElementById("area").value = item_two;
                }
            }
        }
        break;
    }
}

function userSearchWindowKeydown(event)
{
    switch(event.which)
    {
        case ENTER_KEY_CODE:
        {
            var result_lists = document.getElementsByClassName("result_list");
            for(i = 0; i < result_lists.length; i++)
            {
                if(result_lists[i].style.display !== "none")
                {
                    if(result_lists[i].querySelector("li.hover") !== null)
                    {
                        result_lists[i].querySelector("li.hover").onmousedown();
                    }
                    break;
                }
            }
        }
        break;
        case TAB_KEY_CODE:
        {
            setTimeout(function()
            {
                if(document.activeElement.className === "select_box" && document.activeElement.tagName === "INPUT")
                {
                    var result_block = document.getElementById("result_list_" + document.activeElement.id);
                    if(result_block !== null && (result_block.style.display !== "block" || result_block.style.display !== ""))
                    {                        
                        if("jQuery" in window)
                        {
                            jQuery(result_block).slideDown("fast");
                        }
                        else
                        {
                            elementShow(result_block);
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
                    var result_lists = document.getElementsByClassName("result_list");
                    for(i = 0; i < result_lists.length; i++)
                    {
                        if(result_lists[i].style.display !== "none" && ("result_list_" + document.activeElement.id !== result_lists[i].id))
                        {
                            result_lists[i].style.display = "none";
                        }
                    }
                }
            }, 50);
        }
        break;
        case ESC_KEY_CODE:
        {
            var result_lists = document.getElementsByClassName("result_list");
            for(i = 0; i < result_lists.length; i++)
            {
                if(result_lists[i].style.display !== "none")
                {
                    result_lists[i].style.display = "none";
                }
            }
        }
        break;
        case DOWN_KEY_CODE:
        {
            if(/select_box/.test(document.activeElement.className) && document.getElementById("result_list_" + document.activeElement.id).style.display === "none")
            {
                var result_block = document.getElementById("result_list_" + document.activeElement.id);
                if("jQuery" in window)
                {
                    jQuery(result_block).slideDown("fast");
                }
                else
                {
                    elementShow(result_block);
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
                var result_lists = document.getElementsByClassName("result_list");
                for(i = 0; i < result_lists.length; i++)
                {
                    if(result_lists[i].id !== "result_list_" + document.activeElement.id && result_lists[i].style.display !== "none")
                    {
                        result_lists[i].style.display = "none";
                    }
                }
                return false;
            }
            else if(document.activeElement.id === "people_search_field" && document.getElementById("result_list_people_search_field").style.display === "none")
            {
                searchAutocompleteAction();
                return false;
            }
            return listNav(true);
        }
        break;
        case UP_KEY_CODE:
        {
            return listNav(false);
        }
        break;
    }
}