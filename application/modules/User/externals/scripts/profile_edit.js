"use strict";
var VK_API_VERSION = "5.25";
var NOTICE_REMOVE_TIME = 5000; //Время до исчезновения уведомления в милисекундах
var isCrimeaRussianByVK = 0;
var hadRegionSelectError = false;
var i, j, k,
    TITLE_LOAD_ERROR,
    TITLE_LOAD_ERROR_CITY,
    TITLE_PERS_INF_ERROR,
    TITLE_HOME_ADDRESS_ERROR,
    TITLE_SECONDARY_EDUCATION_ERROR,
    TITLE_HIGHER_EDUCATION_ERROR,
    TITLE_CHANGING_LANG_ERROR,
    FULL_LIST_TITLE,
    TITLE_APPLY_CHANGES,
    TITLE_OR,
    TITLE_CANCEL,
    TITLE_THUMBNAIL,
    TITLE_CIRILIC_AND_LATIN,
    TITLE_LOADING,
    _OTHER_M, _OTHER_N, _OTHER_F,
    _NONE_SELECTED_M, _NONE_SELECTED_N, _NONE_SELECTED_F,
    _NOT_SPECIFIED_M, _NOT_SPECIFIED_N, _NOT_SPECIFIED_F,
    _REAL_NAME,
    within_enter,
    socialPopup;

var datepicker_params = {
    /* Параметры календарика */
    startDate: "01.05." + String((new Date()).getFullYear() - 15),
    timepicker: false,
    format: "d.m.Y",
    formatDate: "d.m.Y",
    scrollMonth:false
};
var settingsNextSbm = false;
if (locale === "en") {
    TITLE_LOAD_ERROR = "Loading error";
    TITLE_LOAD_ERROR_CITY = "An error has occurred while loading the list of cities";
    TITLE_PERS_INF_ERROR = "An error occurred while changing the personal information";
    TITLE_HOME_ADDRESS_ERROR = "An error occurred while changing the home address information";
    TITLE_SECONDARY_EDUCATION_ERROR = "An error occurred while changing the secondary education information";
    TITLE_HIGHER_EDUCATION_ERROR = "An error occurred while changing the higher education information";
    TITLE_CIRILIC_AND_LATIN = "You can not use both the Cyrillic and Latin alphabet";
    FULL_LIST_TITLE = " - Full List - ";
    _OTHER_M = _OTHER_N = _OTHER_F = " - Other - ";
    _NONE_SELECTED_M = _NONE_SELECTED_N = _NONE_SELECTED_F = " - None selected - ";
    _NOT_SPECIFIED_M = _NOT_SPECIFIED_N = _NOT_SPECIFIED_F = " - Not specified - ";

    datepicker_params["lang"] = "en";

    TITLE_APPLY_CHANGES = "Apply Changes";
    TITLE_OR = "or";
    TITLE_CANCEL = "Cancel";
    TITLE_THUMBNAIL = "Edit Thumbnail";
    TITLE_LOADING = "Loading...";

    _REAL_NAME = "We ask our users to use their <strong>full</strong> and <strong>real name</strong> on <strong>" + window.location.host + "</strong>. <br/><br/>For example: Laurence John Fishburne, Edward Joseph Snowden.";
}
else {
    TITLE_APPLY_CHANGES = "Применить"
    TITLE_OR = "или";
    TITLE_CANCEL = "Отменить";
    TITLE_THUMBNAIL = "Обрезать аватар";
    TITLE_LOADING = "Загрузка...";

    TITLE_LOAD_ERROR = "Произошла ошибка при загрузке";
    TITLE_LOAD_ERROR_CITY = "Произошла ошибка при загрузке списка городов";
    TITLE_PERS_INF_ERROR = "Произошла ошибка при изменении личной информации";
    TITLE_HOME_ADDRESS_ERROR = "Произошла ошибка при изменении домашнего адреса";
    TITLE_SECONDARY_EDUCATION_ERROR = "Произошла ошибка при изменении информации о среднем образовании";
    TITLE_HIGHER_EDUCATION_ERROR = "Произошла ошибка при изменении информации о высшем образовании";
    TITLE_CIRILIC_AND_LATIN = "Нельзя использовать одновременно кириллицу и латиницу";
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

    _REAL_NAME = "На <strong>" + window.location.host + "</strong> принято использовать <strong>настоящие ФИО</strong>, записанные кириллицей. <br/><br/>Например: Петрова Анна Алексеевна, Иванов Пётр Николаевич.";

    datepicker_params["lang"] = "ru";
    datepicker_params["dayOfWeekStart"] = 1;
    datepicker_params["i18n"] = {
        ru: {
            months: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
            dayOfWeek: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"]
        }
    };
}

var NOTIFICATION_GENERAL, NOTIFICATION_TOOLTIP, orginalThumbSrc, originalSize, lassoCrop, upload_photo_data;

if ("jQuery" in window) {
    jQuery.noConflict();
    NOTIFICATION_GENERAL = new NotificationGeneral; /* Виджет для «уведомлений» */
    NOTIFICATION_TOOLTIP = new NotificationGeneral; /* Виджет для подсказок */

    NOTIFICATION_TOOLTIP.htmlContent = true;
    NOTIFICATION_TOOLTIP.autoremoveDuration = 0;
}


function uploadDataConstructor() {
}

if ("FileList" in window) {
    uploadDataConstructor.prototype = FileList; /* Сам по себе FileList не может быть конструктором (w3c) */
    upload_photo_data = new uploadDataConstructor;
}

window.addEventListener("load", function() {
    jQuery("[data-datepicker=1] > input").each(function(){
        jQuery(this).datetimepicker(datepicker_params);
    });
    jQuery('#profile_status').find("option[value='']").attr("disabled", 'true');
    /* Кнопки «Назад», «Далее» */
    jQuery(".form-previous").on("click", function (event) {
        var current_tab = jQuery("#main_tabs li.active");

        if (current_tab.prev().length !== 0) {
            current_tab.prev()[0].getElementsByTagName("a")[0].click();
            jQuery("input:visible:not([readonly])").focus();
        }
        event.preventDefault();
        return false;
    });
    jQuery(".form-next").on("click", function (event) {
        var current_tab, verified_tip;
        if (jQuery(event.target).hasClass("form-end")) {
            return true;
        }

        current_tab = jQuery("#main_tabs li.active");

        if (current_tab.next().length !== 0) {
            verified_tip = document.getElementsByClassName("verified_tip").item();
            if (verified_tip && verified_tip.style.display !== "none") {
                verified_tip.style.display = "none";
            }

            current_tab.next()[0].getElementsByTagName("a")[0].click();
            jQuery("input:visible:not([readonly])").focus();
        }
        event.preventDefault();
        return false;
    });

    jQuery(".person_name").on("change", function () {
        var first_name, middle_name, last_name;

        first_name = document.getElementById("first_name").value.trim();
        middle_name = document.getElementById("middle_name").value.trim();
        last_name = document.getElementById("last_name").value.trim();

    }).on("focus", function(event) {
        if (!jQuery('#smoothbox_window').length){
            NOTIFICATION_TOOLTIP.tooltipRight(jQuery(this).attr('title'), this);
        }
    }).on("blur", function(event) {
        NOTIFICATION_TOOLTIP.hide();
    });

    jQuery(".country, .region").on("click", listOpen);

    jQuery(".city").on("click", listOpen).on("input", function (event) {
        var current_prefix, country_element, region_element, result_list_element;

        current_prefix = event.target.id.replace(/city/, "");
        country_element = document.getElementById(current_prefix + "country");
        region_element = document.getElementById(current_prefix + "region");

        result_list_element = document.getElementById("result_list_" + event.target.id);

        if (event.target.value.trim() !== "") {
            cityTextchange(country_element.getAttribute("data-id"), region_element.getAttribute("data-id"), event.target.value.trim(), result_list_element);
        }
        else {
            if (event.type == 'input') return;
            changeCitiesList(country_element.getAttribute("data-id"), region_element.getAttribute("data-id"), event.target.id, false);
            if (result_list_element.style.display !== "none") {
                result_list_element.style.display = "none";
            }
        }
    });
	
	/*
		Fill city ID in case when street/school is not filled
	*/
	jQuery('.region').each(function(){
		var rid = jQuery(this).attr("data-id");
		if (!rid) return;
		
		var cityEl = jQuery('#'  + jQuery(this).attr('id').replace('region', 'city'));
		var countryEl = jQuery('#'  + jQuery(this).attr('id').replace('region', 'country'));
		var data = {
                v: VK_API_VERSION,
                lang: locale,
                count:1,
                q: cityEl.val(),
				country_id: countryEl.attr("data-id"),
                access_token: en4.core.vk_token
        };
		if (parseInt(rid)){
			data.region_id = rid;
		}
		//country_id: country_field.getAttribute("data-id"),
		jQuery.ajax({
			url: "https://api.vk.com/method/database.getCities",
            dataType: "jsonp",
			data: data,
			success: function (result) {
                cityFilterTranslate(function(result){
                    if (!result.response){
                        return;
                    }
                    if (result.response.items.length){
                        var item = result.response.items[0];
                        cityEl.attr("data-id", item.id);
                    }
                }, result);
            }
        });
	});

    jQuery("#street").on("input", function (event) {
        var current_city_id, result_list_street, autocomplete_url, autocomplete_data;

        current_city_id = parseInt(document.getElementById("city").getAttribute("data-id"));
        result_list_street = document.getElementById("result_list_street");
        autocomplete_url = "/application/simple_api.php?method=vk_street";
        autocomplete_data = {"city_id": parseInt(current_city_id), "street_query": event.target.value};


        if (event.target.value.trim() === "" || !current_city_id) {

            return;
        }
        else {
            var displayResult = function (container, result, value) {
                var regexp_value = new RegExp(regexpEscape(value), "i");

                for (i = 0; i < result.length; i++) {
                    var current_text, bold_text_position, item;

                    current_text = result[i][1].trim();
                    /* Название элемента из списка */
                    bold_text_position = current_text.search(regexp_value);

                    item = document.createElement("li");
                    item.onmousedown = selectItem;
                    item.onmouseover = highlightItem;

                    if (bold_text_position === -1) {
                        item.textContent = current_text;
                    }
                    else {
                        var autocomplete_first_title_element, autocomplete_match_title_element, autocomplete_last_title_element;

                        autocomplete_first_title_element = document.createTextNode(current_text.substring(0, bold_text_position));
                        autocomplete_match_title_element = document.createElement("b");
                        autocomplete_match_title_element.textContent = current_text.substr(bold_text_position, value.length);
                        autocomplete_last_title_element = document.createTextNode(current_text.substring(bold_text_position + value.length));
                        item.appendChild(autocomplete_first_title_element);
                        item.appendChild(autocomplete_match_title_element);
                        item.appendChild(autocomplete_last_title_element);
                    }

                    container.appendChild(item);
                }
            };

            autocomleteAction(autocomplete_url, autocomplete_data, "json", "post", result_list_street, displayResult, event.target.value.trim());
        }
    });

    jQuery("#school_name").on("input", function (event) {
        if (event.target.value.trim() !== "") {
            schoolTextchange(document.getElementById("school_country").getAttribute("data-id"), document.getElementById("school_city").getAttribute("data-id"), event.target.value.trim());
        }
        else {
            changeSchoolsList(document.getElementById("school_country").getAttribute("data-id"), document.getElementById("school_city").getAttribute("data-id"));
        }
    });

    jQuery("#university_name").on("input", function (event) {
        fieldRewrite(document.getElementById("university_faculty"), "");
        fieldRewrite(document.getElementById("university_major"), "");

        if (event.target.value.trim() !== "") {
            universityTextchange(document.getElementById("university_country").getAttribute("data-id"), document.getElementById("university_city").getAttribute("data-id"), event.target.value.trim());
        }
        else {
            changeUniversitiesList(document.getElementById("university_country").getAttribute("data-id"), document.getElementById("university_city").getAttribute("data-id"));
        }
        universityGroupNumberRequire();
    });

    jQuery("#school_name, #school_class, #school_graduation, #university_name, #university_faculty, #university_major, #university_mode_study, #university_current_status, #university_graduation, #university_course").on("click", listOpen);

    jQuery(".custom-combobox-toggle").on("click", function (event) {
        jQuery(this).parent().find('input:first').click();
    });

    /* Отправка основных форм */
    jQuery("#global_content form.profile_form").on("submit", sendForm);

    var edit_photo_form = document.getElementById("EditPhoto");
    if(edit_photo_form){
        edit_photo_form.onsubmit = uploadAvatar;
    }

    var file_choose_block = document.getElementsByClassName("file_choose_block").item(0);
    var element_file_choose_title = document.getElementsByClassName("file_choose_title").item(0);

    jQuery(file_choose_block).on("dragenter dragstart dragend dragleave dragover drag drop", function (event) {
        var max_size, size_text;
        switch (event.type) {
            case "dragenter": {
                within_enter = true;
                setTimeout(function () {
                    within_enter = false;
                }, 0);

                if (!/filedrag/.test(file_choose_block.className)) {
                    file_choose_block.className += " filedrag";
                }

                if (element_file_choose_title.textContent !== "Отпустите для добавления") {
                    element_file_choose_title.textContent = "Отпустите для добавления";
                }
            }
                break;

            case "dragleave": {
                if (!within_enter) {
                    if (/filedrag/.test(file_choose_block.className)) {
                        file_choose_block.className = "file_choose_block";
                    }

                    if (element_file_choose_title.textContent !== "Загрузить фото") {
                        element_file_choose_title.textContent = "Загрузить фото";
                    }
                }

                within_enter = false;
            }
                break;

            case "drop": {
                if (/filedrag/.test(file_choose_block.className)) {
                    file_choose_block.className = "file_choose_block";
                }

                if (element_file_choose_title.textContent !== "Загрузить фото") {
                    element_file_choose_title.textContent = "Загрузить фото";
                }

                within_enter = false;

                if(event.originalEvent.dataTransfer.files.length === 0)
                {
                    event.originalEvent.dataTransfer.items[0].getAsString(function(url){
                        var dragUrl, dragExtension;

                        dragUrl = String(url);

                        dragExtension = dragUrl.split(".");

                        if(dragExtension[dragExtension.length - 1] === "jpg" || dragExtension[dragExtension.length - 1] === "png" || dragExtension[dragExtension.length - 1] === "gif") {
                            previewURLPicture(dragUrl);
                        } else {
                            NOTIFICATION_GENERAL.warning("Не получается прочитать как изображение");
                        }
                    });
                } else {
                    if (event.originalEvent.dataTransfer.files[0].type !== "image/jpeg" && event.originalEvent.dataTransfer.files[0].type !== "image/png" && event.originalEvent.dataTransfer.files[0].type !== "image/gif") {
                        NOTIFICATION_GENERAL.warning("Допустимые форматы: jpeg, jpg, png, gif");
                        return;
                    }

                    max_size = parseInt(document.getElementById("MAX_FILE_SIZE").value);

                    if (max_size > 0 && upload_photo_data.size > max_size) {
                        NOTIFICATION_GENERAL.warning("Загружаемое изображение должно быть не более 15 MB!");
                        return;
                    }

                    upload_photo_data = event.originalEvent.dataTransfer.files[0];
                    document.getElementById("Filedata").value = null;

                    previewUploadPicture();
                }
            }
                break;
        }
        event.preventDefault();
        return false;
    }).on("click", function () {
        var file_label = document.getElementsByClassName("file_label").item(0);
        if (file_label !== null) {
            file_label.click();
        }
    });

    jQuery(document).on("dragenter dragstart dragend dragleave dragover drag drop", function (event) {
        event.preventDefault();
        return false;
    });

    jQuery(".photo-cancel").on("click", changeCancel);
    jQuery("#Filedata").on("change", changeFilePhoto);
    jQuery(".lasso-button").on("click", lassoStart);

    jQuery(".vk-photo").on("click", function(event) {
        var vkPhotoButton = event.target;

        socialPopup = window.open(vkPhotoButton.href, "linking", "scrollbars=no,status=no,toolbar=no,menubar=no");
        socialPopup.focus();

        return false;
    });

    jQuery(".social_remove_button").on("click", socialUnlinkAction); /* Кнопки для отвязки аккаунтов */

    jQuery('#profile_status').change(function(){
        jQuery('#personal_information').data('reload', 'yes');
    });
    /*goncharov - finish button*/
    if (!jQuery('#main_tabs li b').length)
    {
        jQuery('button[name^=finish]').removeAttr('disabled');
    }

}  /*initialization*/, false);


function socialUnlinkAction(event) /* Отвязка профилей из соцсетей */ {
    var type, profileButton, jqProfileButton, buttonShell;

    type = event.target.id.replace(/_remove/, "");
    profileButton = document.getElementById(type + "_profile_button");
    jqProfileButton = jQuery(profileButton);

    jQuery.ajax({
        url: "/simple_api/social_auth.php?method=" + type + "_unlink",
        dataType: "json",
        beforeSend: function () {
            jqProfileButton.empty().append(getLoaderImg());
        },
        success: function(data, textStatus, jqXHR) {
            var link;

            if (data.status !== true) {
                this.error(jqXHR, textStatus, "serverError");
                return false;
            }

            NOTIFICATION_GENERAL.success("Ваш аккаунт успешно отвязан. Теперь вы не сможете использовать его для входа на " + window.location.hostname);

            setTimeout(function(){document.location.href = document.location.pathname;}, 1500);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR, textStatus, errorThrown);
            NOTIFICATION_GENERAL.error("Произошла ошибка при отвязке вашего аккаунта");
            jqProfileButton.empty().append(document.createTextNode("Произошла ошибка при отвязке вашего аккаунта"));
        }
    });

}

function displayLinkedProfile(data) {
    var socialLink, localUnlinkButton, showSuccess;
    if(data.status !== true) {
        return false;
    }

    socialLink = document.createElement("a");
    socialLink.target = "_blank";

    localUnlinkButton = document.createElement("button");
    localUnlinkButton.className = "social_remove_button";
    localUnlinkButton.onclick = socialUnlinkAction;
    localUnlinkButton.appendChild(document.createTextNode("Отвязать"));

    if("vk_user_id" in data) {
        socialLink.href = "https://vk.com/id" + data.vk_user_id;
        socialLink.appendChild(document.createTextNode("vk.com/id" + data.vk_user_id));

        localUnlinkButton.id = "vk_remove";

        jQuery("#vk_profile_button").empty().append([socialLink, "&nbsp;", localUnlinkButton]);
        showSuccess = true;
    } else if("mipt_id" in data) {
        localUnlinkButton.id = "mipt_remove";

        jQuery("#mipt_profile_button").empty().append(["Аккаунт привязан (id " + data.mipt_id + ") &nbsp;", localUnlinkButton]);
        showSuccess = true;
    } else if("yandex_user_id" in data) {
        socialLink.href = "mailto: " + data.yandex_user_email;
        socialLink.appendChild(document.createTextNode(data.yandex_user_email));

        localUnlinkButton.id = "ya_remove";

        jQuery("#ya_profile_button").empty().append([socialLink, "&nbsp;", localUnlinkButton]);
        showSuccess = true;
    } else if("google_user_id" in data) {
        socialLink.href = "mailto: " + data.google_user_email;
        socialLink.appendChild(document.createTextNode(data.google_user_email));

        localUnlinkButton.id = "google_remove";

        jQuery("#google_profile_button").empty().append([socialLink, "&nbsp;", localUnlinkButton]);
        showSuccess = true;
    } else if("mailru_user_id" in data) {
        socialLink.href = "mailto: " + data.mailru_user_email;
        socialLink.appendChild(document.createTextNode(data.mailru_user_email));

        localUnlinkButton.id = "mailru_remove";

        jQuery("#mailru_profile_button").empty().append([socialLink, "&nbsp;", localUnlinkButton]);
        showSuccess = true;
    } else if("fb_user_id" in data) {
        socialLink.href = "https://facebook.com/profile/" + data.fb_user_id;
        socialLink.appendChild(document.createTextNode("facebook.com/profile/" + data.fb_user_id));

        localUnlinkButton.id = "ok_remove";

        jQuery("#fb_profile_button").empty().append([socialLink, "&nbsp;", localUnlinkButton]);
        showSuccess = true;
    } else if("twitter_user_id" in data) {
        socialLink.href = "https://twitter.com/profile/" + data.twitter_user_id;
        socialLink.appendChild(document.createTextNode("twitter.com/profile/" + data.twitter_user_id));

        localUnlinkButton.id = "ok_remove";

        jQuery("#twitter_profile_button").empty().append([socialLink, "&nbsp;", localUnlinkButton]);
        showSuccess = true;
    } else if("ok_user_id" in data) {
        socialLink.href = "https://ok.ru/profile/" + data.ok_user_id;
        socialLink.appendChild(document.createTextNode("ok.ru/profile/" + data.ok_user_id));

        localUnlinkButton.id = "ok_remove";

        jQuery("#ok_profile_button").empty().append([socialLink, "&nbsp;", localUnlinkButton]);
        showSuccess = true;
    }

    if(showSuccess) {
        NOTIFICATION_GENERAL.success("Ваш аккаунт успешно привязан. Теперь вы можете использовать его для входа на " + window.location.hostname);
    }
}


window.addEventListener("keydown", function (event) {
    switch (event.which) {
        case jQuery.ui.keyCode.ENTER: {
            var result_block = jQuery(".result_list:visible")[0];

            if (result_block) {
                var item = result_block.getElementsByClassName("hover").length ? result_block.getElementsByClassName("hover")[0] : result_block.getElementsByTagName('li')[0];
                item.onmousedown({target: item});
                event.preventDefault();
                return false;
            }
        }
            break;

        case jQuery.ui.keyCode.ESCAPE: {
            if (document.activeElement.tagName === "INPUT") {
                document.activeElement.blur();
            }

            if(document.getElementById("lassoMask")) {
                lassoCancel();
            }
        }
        /* Часть действий ESC и TAB совпадает */
        case jQuery.ui.keyCode.TAB: {
            jQuery(".custom-combobox-toggle").removeClass("active");
            jQuery(".result_list:visible").hide();
        }
            break;

        case jQuery.ui.keyCode.DOWN: {
            var hover_element, next_element, scroll_difference, result_block = jQuery(".result_list:visible")[0];

            if (result_block !== undefined) {
                hover_element = result_block.querySelector(".hover");

                if (hover_element === null && result_block.getElementsByTagName("li")[0] !== undefined) {
                    result_block.getElementsByTagName("li")[0].className += " hover";
                    result_block.scrollTop = 0;
                    event.preventDefault();
                    return false;
                }

                if (hover_element === null || hover_element === undefined) {
                    event.preventDefault();
                    return false;
                }

                next_element = hover_element.nextElementSibling;

                if (next_element === null || next_element === undefined) {
                    event.preventDefault();
                    return false;
                }

                scroll_difference = result_block.offsetHeight - (next_element.offsetTop - result_block.scrollTop);
                if (scroll_difference < next_element.offsetHeight) {
                    result_block.scrollTop += next_element.offsetHeight - scroll_difference;
                }

                next_element.className += " hover";
                removeClass(hover_element, "hover");

                event.preventDefault();
                return false;
            }
        }
            break;

        case jQuery.ui.keyCode.UP: {
            var hover_element, previous_element, scroll_difference, result_block = jQuery(".result_list:visible")[0];

            if (result_block !== undefined) {
                hover_element = result_block.querySelector(".hover");

                if (hover_element === null && result_block.getElementsByTagName("li")[0] !== undefined) {
                    result_block.getElementsByTagName("li")[0].className += " hover";
                    result_block.scrollTop = 0;
                    event.preventDefault();
                    return false;
                }

                if (hover_element === null || hover_element === undefined) {
                    event.preventDefault();
                    return false;
                }

                previous_element = hover_element.previousElementSibling;

                if (previous_element === null || previous_element === undefined) {
                    event.preventDefault();
                    return false;
                }

                scroll_difference = previous_element.offsetTop - result_block.scrollTop;
                if (scroll_difference < previous_element.offsetHeight) {
                    result_block.scrollTop += scroll_difference;
                }

                previous_element.className += " hover";
                removeClass(hover_element, "hover");

                event.preventDefault();
                return false;
            }
        }
            break;
    }
}, false);

window.addEventListener("click", function (event) {
    var result_list_regexp, combobox_regexp, input_id, all_visible_elements, visible_element;

    result_list_regexp = new RegExp("result_list");
    combobox_regexp = new RegExp("custom-combobox");

    if (result_list_regexp.test(event.target.className)
        || (event.target.parentNode && (result_list_regexp.test(event.target.parentNode.className) || combobox_regexp.test(event.target.parentNode.className)))
        || (event.target.parentNode.parentNode && result_list_regexp.test(event.target.parentNode.parentNode.className))) {
        return true;
    }

    all_visible_elements = jQuery(".result_list:visible");
    visible_element = all_visible_elements[0];

    if (visible_element !== undefined) {
        input_id = visible_element.id.replace("result_list_", "");
    }

    jQuery(".custom-combobox-toggle").removeClass("active");
    all_visible_elements.hide();
    return true;

}, true);

function listOpen(event) {
    var result_list_id, current_result_block, list_items_count;
    result_list_id = "result_list_" + event.target.id;

    if (event.target.id.indexOf("country")!=-1){
        jQuery(event.target).attr('readonly', '1');
    }
    
    current_result_block = document.getElementById(result_list_id);

    if (current_result_block === null) {
        return false;
    }

    list_items_count = current_result_block.getElementsByTagName("li").length;

    jQuery(".result_list:visible:not(#" + result_list_id + ")").hide();

    if (current_result_block === null) {
        return false;
    }

    var combobox_button = event.target.parentNode.getElementsByClassName("custom-combobox-toggle")[0];

    jQuery(current_result_block).css('width', jQuery(event.target).parent().width());

    if (current_result_block.style.display === "none" && list_items_count !== 0) {
        if( !hadRegionSelectError &&  event.target.id.indexOf("region")!=-1 && current_result_block.getElementsByTagName('li').length<=1){
            listOpen({target: document.getElementById(event.target.id.replace("region", "country"))});
            return;
        }
        current_result_block.style.display = "";
        if (combobox_button !== undefined && !(/active/.test(combobox_button.className))) {
            combobox_button.className += " active";
        }

        var list_items = current_result_block.getElementsByTagName("li");

        for (i = 0; i < list_items.length; i++) {
            if (list_items[i].textContent.trim() === event.target.value) {
                list_items[i].parentNode.parentNode.scrollTop = list_items[i].offsetTop;
                jQuery(current_result_block).find("li.hover").removeClass("hover");
                list_items[i].className += " hover";
            }
        }

        if (current_result_block.getElementsByClassName("hover").length === 0 && current_result_block.getElementsByTagName("li").length !== 0) {
            current_result_block.getElementsByTagName("li")[0].className += " hover";
        }
        return false;
    }
    else {
        current_result_block.style.display = "none";

        if (combobox_button !== undefined && /active/.test(combobox_button.className)) {
            jQuery(combobox_button).removeClass("active");
        }
        return false;
    }
}

function userNamesTextchange() {}
function userNamesChange() {}

window.onFieldsSendedHook = function(){};
/**
 * @param {jQuery} sending_form
 * @param {FormData} formData
 */
window.beforeFieldsSendedHook = function(sending_form, formData){};
window.fieldsEditSubmiting = false;
function sendForm(e) {
    if (e) e.preventDefault();
    if (window.fieldsEditSubmiting) return false;

    var sending_form, editing_user_element, user_id, current_field, send_fields, count, form_exist, submit_button;

    sending_form = jQuery("#global_content .generic_layout_container:not(.layout_core_container_tabs):visible form");

    editing_user_element = document.getElementById("editing_user_id");

    if (editing_user_element !== null) {
        user_id = editing_user_element.value;
    }

    if (user_id === undefined || sending_form[0] === undefined) { /* !fatal error */
        NOTIFICATION_GENERAL.error("Не указана редактируемая форма или id пользователя! Перезагрузите страницу и попробуйте снова");
        return false;
    }

    if (sending_form[0].id === "personal_information") {
        send_fields = sending_form.find("input:visible, input[name='gender']:checked, select:visible, textarea");
    }
    else {
        send_fields = sending_form.find("input, select, textarea");
    }


    var formData = new FormData(sending_form[0]);
    send_fields.each(function(){
        var id = jQuery(this).attr('id');
        if (jQuery(this).is('[type="checkbox"]') && !jQuery(this).prev().is('[type="hidden"][name="'+jQuery(this).attr('name')+'"]')){
            formData.set(id, jQuery(this).is(':checked') ? '1' : '0');
            return;
        }
        if (["university_mode_study", "university_current_status"].indexOf(jQuery(this).attr('id'))!=-1){
            formData.set(id, jQuery(this).attr('data-id'));
        }
    });
    window.beforeFieldsSendedHook(sending_form, formData);

    submit_button = sending_form.find("button[type='submit']");
    var submitHtmlOriginal = sending_form.find("button[id^=submit]").html();
    var nextHtmlOriginal = sending_form.find("button[id^=next]").html();

    jQuery.ajax({
        url: "/user_edit/" + sending_form[0].id + "/" + user_id + "/",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        type: "post",
        beforeSend: function () {
            submit_button.css({
                width: (submit_button.innerWidth()),
                height: submit_button.innerHeight()
            }).empty().append(getLoaderImg());

            window.fieldsEditSubmiting = true;
        },
        success: function(result, textStatus, jqXHR) {
            window.onFieldsSendedHook();
            var nextBtnReplace = function()
            {
                jQuery('button[name^=finish]').removeAttr('disabled');
            };
            if ( (typeof result.requiredAlert != 'undefined') && !result.requiredAlert){
                jQuery('.form-errors').remove();
                jQuery('#main_tabs li b').remove();
                nextBtnReplace();
            }else if(typeof result.requiredAlert != 'undefined' ){
                jQuery('.requied_fields_alert_flabels').html( result.requiredAlert.label ); 
                jQuery('li > a > b').remove();
                var anyAlert = false;
                for(var i in result.requiredAlert.categories){if (result.requiredAlert.categories.hasOwnProperty(i)){
                    jQuery('a[data-name='+result.requiredAlert.categories[i]+']').append(' <b style="color:red">*</b>');
                    anyAlert = true;
                }}
                if (!anyAlert){
                    nextBtnReplace();
                }
            }
            switch (result.status) {
                case "success": {
		            if(sending_form.data('reload') == 'yes'){
		            	document.location.reload();
		            	NOTIFICATION_GENERAL.success("Подождите пока страница обновится");
		            } else {
		            	NOTIFICATION_GENERAL.success(result.message);	
		            }
                }
                if (settingsNextSbm){
                    profileSettingsNextTabOpen();
                    settingsNextSbm = false;
                }
                break;

                case "incorrect": {
                    for (var key in result) {
                        if (formData.has(key)) {
                            var obj_level2 = result[key];
                            for (var dkey in obj_level2) {
                                var correct = document.getElementById(key + "_correct");
                                if (correct && correct.innerHTML !== obj_level2[dkey]) {
                                    correct.innerHTML = obj_level2[dkey];
                                }else{
                                    NOTIFICATION_GENERAL.error(obj_level2[dkey]);
                                    return;
                                }
                                break;
                            }
                        }
                    }

                    if (result.message) {
                        NOTIFICATION_GENERAL.error(result.message);
                    }
                    else {
                        NOTIFICATION_GENERAL.error("Некорректно заполнена форма");
                    }
                }
                    break;

                case "fail": {
                    if (result.reason === "access denied") {
                        NOTIFICATION_GENERAL.error("Доступ запрещён");
                    } else if (result.reason === "verified values") {
                        NOTIFICATION_GENERAL.error("Невозможно изменение подтверждённых данных");
                    } else {
                        NOTIFICATION_GENERAL.error(result.reason);
                    }
                }
                    break;

                default: {
                    NOTIFICATION_GENERAL.error(result.message);
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            NOTIFICATION_GENERAL.error(TITLE_PERS_INF_ERROR);
        },
        complete: function () {
            window.fieldsEditSubmiting = false;
            sending_form.find("button[id^=submit]").html(submitHtmlOriginal).removeAttr("style");
            sending_form.find("button[id^=next]").html(nextHtmlOriginal).removeAttr("style");
        }
    });

    return false;
}

function showAllCountries(current_element) {
    var result_list_container = current_element.parentNode;

    jQuery.ajax(
        {
            url: "https://api.vk.com/method/database.getCountries",
            dataType: "jsonp",
            data: {
                v: VK_API_VERSION,
                lang: locale,
                count: "250",
                need_all: "1",
                access_token: en4.core.vk_token,
            },
            success: function (countriesList) {
                jQuery(result_list_container).empty();

                for (i = 0; i < countriesList.response.count; i++) {
                    var country_item = document.createElement("li");

                    country_item.onmouseover = highlightItem;
                    country_item.setAttribute("onmousedown", "selectCountry(" + countriesList.response.items[i].id + ", this)");
                    country_item.textContent = countriesList.response.items[i].title.trim();
                    result_list_container.appendChild(country_item);
                }
            },
            error: function () {
                NOTIFICATION_GENERAL.error(TITLE_LOAD_ERROR);
            }
        });
}

function showAnotherItem(event) {
    var result_container, result_list, field_id, field_element;

    result_list = event.target.parentNode;
    result_container = result_list.parentNode;
    field_id = result_container.id.replace(/result_list_/, "");
    field_element = document.getElementById(field_id);

    fieldRewrite(field_element, "");
    elementHide(result_container);

    while (result_list.lastChild) {
        result_list.removeChild(result_list.lastChild);
    }

    field_element.focus();
    return false;
}

function selectCountry(country_id, current_element) {
    var city_field_id, region_field_id, id_prefix;
    var result_list = current_element.parentNode.parentNode;
    var result_field_id = result_list.id.replace(/result_list_/, "");

    var current_field = document.getElementById(result_field_id);
    /* Заполняемое поле */

    current_field.value = current_element.textContent.trim();
    current_field.setAttribute("data-id", country_id);
    elementHide(result_list);

    id_prefix = result_field_id.replace(/country/, "");
    city_field_id = id_prefix + "city";
    region_field_id = id_prefix + "region";

    changeRegionsList(country_id, region_field_id, true);
    changeCitiesList(country_id, null, city_field_id, true);

    removeClass(document.getElementById("toggle_" + result_field_id), "active");

    return false;
}

function selectRegion(region_id, current_element, disableCityChange) {
    var result_list = current_element.parentNode.parentNode;

    var region_field_id = result_list.id.replace(/result_list_/, "");
    var region_field = document.getElementById(region_field_id);
    var form_prefix = region_field_id.replace(/region/, "");

    var country_field = document.getElementById(form_prefix + "country");
    var country_id =  country_field.getAttribute("data-id");
    if (!isCrimeaRussianByVK && region_id == '1500001'){
        country_id = 2;
    }
    
    if (region_id === "") {
        region_field.value = "";
        region_field.removeAttribute("data-id");
    }
    else {
        var newVal = current_element.textContent.trim();
        if (newVal!=region_field.value){
            region_field.value = current_element.textContent.trim();
            region_field.setAttribute("data-id", region_id);
        }
    }

    elementHide(result_list);

    removeClass(document.getElementById("toggle_" + region_field_id), "active");
    if (!disableCityChange){
        var city_field_id = form_prefix + "city";
        changeCitiesList(country_id, region_id, city_field_id, false);
    }
    return false;
}

/*КОСТЫЛЬ: для добавления в список регионов несколких городов-регионов (ищи по коду этого файла #regioncities)*/
function getRealyCityId(city_id)
{
    if (city_id >= 0) return city_id;
    var realid_map = {'-10':1, '-11': 2, '-12': 185 };
    return realid_map[city_id] ? realid_map[city_id] : 0;
}
/*КОСТЫЛЬ END*/

function selectCity(city_id, city_name, region_name, area_name, current_element) {
    var current_container = current_element.parentNode.parentNode;
    var city_field_id = current_container.id.replace(/result_list_/, "");
    var city_field = document.getElementById(city_field_id);

    if (city_name === undefined || city_id === undefined) {
        current_element.removeClass("hover");
        elementHude(result_list_container);
        changeCitiesList(document.getElementById("country").getAttribute("data-id"), document.getElementById("region").getAttribute("data-id"), city_field_id, false); //перезагрузка списка городов        
        return false;
    }

    city_id = getRealyCityId(city_id);

    city_field.value = city_name;
    city_field.setAttribute("data-id", city_id);
    city_field.blur();
    removeClass(current_element, "hover");
    elementHide(current_container);

    var region_field, country_field;
    var area_field = null;
    switch (city_field_id) {
        case "school_city":
        {
            country_field = document.getElementById("school_country");
            region_field = document.getElementById("school_region");
            area_field = document.getElementById("school_area");

            setTimeout(function () {
                changeSchoolsList(document.getElementById("school_country").getAttribute("data-id"), getRealyCityId(city_id));
            }, 150);

            removeClass(document.getElementById("toggle_school_city"), "active");
        }
            break;
        case "university_city":
        {
            country_field = document.getElementById("university_country");
            region_field = document.getElementById("university_region");
            area_field = document.getElementById("university_area");

            setTimeout(function () {
                changeUniversitiesList(document.getElementById("university_country").getAttribute("data-id"), getRealyCityId(city_id));
            }, 150);

            removeClass(document.getElementById("toggle_university_city"), "active");
        }
            break;
        case "city":
        default:
        {
            country_field = document.getElementById("country");
            region_field = document.getElementById("region");
            area_field = document.getElementById("area");
            removeClass(document.getElementById("toggle_city"), "active");
        }
            break;
    }


    if (region_name !== undefined && region_name !== null && region_name !== "") {

        jQuery.ajax({
            url: "https://api.vk.com/method/database.getRegions",
            dataType: "jsonp",
            data: {
                v: VK_API_VERSION,
                lang: locale,
                access_token: en4.core.vk_token,
                country_id: country_field.getAttribute("data-id"),
                q:region_name.split(' ')[0],
            },
            success: function (region) {
                cityFilterTranslate(function(region){
                    var region = region.response.items[0];
                    if (!region) return;
                    var regions_list = document.getElementById("result_list_" + city_field_id.replace(/city/, "") + "region").getElementsByTagName("li");

                    if (regions_list.length <= 1){
                        fillRegionListIfVoid( jQuery(city_field).parents('form:first') );
                        jQuery(region_field).val(region.title);
                    }else{
                        for (i = 0; i < regions_list.length; i++) {
                            if (parseInt(regions_list[i].getAttribute("data-region-id")) == region.id) {
                                selectRegion(region.id, regions_list[i], true);
                            }
                        }
                    }
                }, region);
            }
        });
    }else{
        // название_региона_по_городу()->id_региона_по_названию()->английский перевод региона
        let countryId = country_field.getAttribute("data-id");
        jQuery.ajax({
            url: "https://api.vk.com/method/database.getCities",
            dataType: "jsonp",
            data: {
                v: VK_API_VERSION,
                lang: locale,
                country_id: countryId,
                count:1000,
                q:city_name,
                access_token: en4.core.vk_token,
            },
            success: function (result) {
                if (result.response.items.length){
                    var item = result.response.items[0];
                    region_field.setAttribute("data-id", "");
                    var regionValue = typeof item.region != 'undefined' ? item.region : item.title;

                    jQuery.ajax({
                        url: "https://api.vk.com/method/database.getRegions",
                        dataType: "jsonp",
                        data: {
                            v: VK_API_VERSION,
                            lang: locale,
                            access_token: en4.core.vk_token,
                            country_id: countryId,
                            q: countryId <= 3 ? regionValue.split(' ')[0] : regionValue,
                        },
                        success: function (region) {
                            cityFilterTranslate(function(region){
                                if (region.response.items.length) region_field.value = region.response.items[0].title;
                            }, region);
                        }
                    });
                }
            }
        });
    }
    
    if (area_field){
        area_field.value = area_name.trim();
    }
    city_field.focus();
    return false;
}

function profileSettingsNextTabOpen()
{
    tab_change(jQuery('#main_tabs li.active').next().find('a')[0], true);
    //.click();
}

function selectSchool(school_id, value_title) {
    document.getElementById("school_name").value = value_title;
    document.getElementById("school_name").setAttribute("data-id", school_id);
    elementHide(document.getElementById("result_list_school_name"));
    removeClass(document.getElementById("toggle_school_name"), "active");

    document.getElementById("school_name").focus();
    return false;
}

function universityGroupNumberRequire(university_id) {
    let $ = jQuery;
    let university = $('#university_name');
    let ugField = $('#university_group_number');
    let ugLabel = $('label[for="university_group_number"]');
    if (!ugLabel.length) return;

    if (university_id == 297
        || ['мфти', 'mipt'].includes(university.val().toLocaleLowerCase())
        || university.val().toLocaleLowerCase().indexOf('мфти ')==0
        || university.val().toLowerCase().indexOf('mipt ')==0
    ){
        ugField.attr('required', 'required');
        if (!ugLabel.find('b').length) ugLabel.append(' <b>*</b>');
    }else{
        ugField.removeAttr('required');
        ugLabel.find('b').remove();
    }
}

function selectUniversity(university_id, value_title) {
    document.getElementById("university_name").value = value_title;
    document.getElementById("university_name").setAttribute("data-id", university_id);
    changeFacultiesList(university_id);
    elementHide(document.getElementById("result_list_university_name"));
    removeClass(document.getElementById("toggle_university_name"), "active");
    document.getElementById("university_name").focus();
    universityGroupNumberRequire(university_id);
    return false;
}

function selectFaculty(faculty_id, value_title) {
    document.getElementById("university_faculty").value = value_title;
    document.getElementById("university_faculty").setAttribute("data-id", faculty_id);
    elementHide(document.getElementById("result_list_university_faculty"));
    removeClass(document.getElementById("toggle_university_faculty"), "active");

    document.getElementById("university_faculty").focus();
    return false;
}

function selectCurrentStatus(status_id, value_title) {
    document.getElementById("university_current_status").value = value_title;
    document.getElementById("university_current_status").setAttribute("data-id", status_id);
    elementHide(document.getElementById("result_list_university_current_status"));
    removeClass(document.getElementById("toggle_university_current_status"), "active");

    document.getElementById("university_current_status").focus();
    return false;
}

function selectStudyMode(study_mode_id, value_title) {
    document.getElementById("university_mode_study").value = value_title;
    document.getElementById("university_mode_study").setAttribute("data-id", study_mode_id);
    elementHide(document.getElementById("result_list_university_mode_study"));
    removeClass(document.getElementById("toggle_university_mode_study"), "active");

    document.getElementById("university_mode_study").focus();
    return false;
}

var fillRegionListIfVoid = function(activeContent){
    var activeContent = activeContent || jQuery('.layout_core_container_tabs .generic_layout_container:visible');
    activeContent.find('input.country').each(function(){
        var countryVkId = parseInt(jQuery(this).attr('data-id'));
        var regionId = jQuery(this).attr('id').replace("country", "region");
        var regionListLen = jQuery('#'+regionId).parents('.form-wrapper').find('.result_list li').length;
        if (countryVkId && regionListLen <=2){
            changeRegionsList(countryVkId, regionId, 1);
        }
    });
};

function changeRegionsList(country_id, region_field_id, justListSet) {
    justListSet = justListSet==undefined ? 0 : 1;
    jQuery.ajax({
        url: "https://api.vk.com/method/database.getRegions",
        dataType: "jsonp",
        data: {
            v: VK_API_VERSION,
            lang: locale,
            country_id: country_id,
            count: 1000,
            access_token: en4.core.vk_token,
        },
        beforeSend: function () {

        },
        success: function (regionsList) {
            cityFilterTranslate(function(regionsList){
                var region_block = document.getElementById("result_list_" + region_field_id);
                var region_container = region_block.getElementsByTagName("ul")[0];

                if (region_container === undefined) {
                    region_container = document.createElement("ul");
                    region_block.appendChild(region_container);
                } else {
                    jQuery(region_container).empty();
                }
                if (justListSet) {
                    var reg = jQuery('#' + region_field_id);
                    for (i = 0; i < regionsList.response.items.length; i++) {
                        if (regionsList.response.items[i].title.trim() == reg.val()) {
                            let id = regionsList.response.items[i].id;
                            reg.attr('data-id', id).data('id', id);
                        }
                    }
                } else {
                    document.getElementById(region_field_id).value = "";
                    document.getElementById(region_field_id).removeAttribute("data-id");
                }
                var not_specified_item = document.createElement("li");
                not_specified_item.appendChild(document.createTextNode(_NOT_SPECIFIED_M));
                not_specified_item.setAttribute("onmousedown", "selectRegion('', this);");
                not_specified_item.setAttribute("data-region-id", "");
                not_specified_item.onmouseover = highlightItem;

                region_container.appendChild(not_specified_item);

                //Хак - добавляем России Крым, Добавляем несколько регионов
                if (country_id == 1){
                    var krasnodPos = 0;

                    for (var i = 0; i < regionsList.response.count; i++) {
                        var regionName = regionsList.response.items[i].title;
                        if (regionName.indexOf("Краснод")!=-1 || regionName.indexOf("Krasnod")!=-1){
                            krasnodPos = i;
                        }else if (regionName.indexOf("Крым")!=-1 || regionName.indexOf("Crimea")!=-1 ){
                            isCrimeaRussianByVK = 1;
                        }
                    }
                    if (!isCrimeaRussianByVK){
                        regionsList.response.items.splice(krasnodPos + 1, 0, {
                            id: 1500001,
                            title: locale == 'en' ? 'Crimea' : 'Крым'
                        });
                    }
                    /*КОСТЫЛЬ: для добавления в список регионов несколких городов-регионов (ищи по коду этого файла #regioncities)*/
                    if (locale == 'en')
                        regionsList.response.items.splice(0, 0,
                            { id: -10, title: 'Moscow'},
                            { id: -11, title: 'Saint Petersburg'},
                            { id: -12, title: 'Sevastopol' }
                        );
                    else
                        regionsList.response.items.splice(0, 0,
                            { id: -10, title: 'Москва'},
                            { id: -11, title: 'Санкт-Петербург'},
                            { id: -12, title: 'Севастополь' }
                        );
                    /*КОСТЫЛЬ END*/
                }

                for (i = 0; i < regionsList.response.items.length; -i++) {
                    let id = regionsList.response.items[i].id;

                    if(id == 5471696){
                        continue;//баг ВК
                    }
                    var region_item = document.createElement("li");
                    region_item.setAttribute("onmousedown", "selectRegion(" + id + ", this)");
                    region_item.setAttribute("data-region-id", id);
                    region_item.onmouseover = highlightItem;
                    region_item.appendChild(document.createTextNode(regionsList.response.items[i].title.trim()));
                    region_container.appendChild(region_item);
                }
            }, regionsList);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR, textStatus, errorThrown);
            //no error - just enable manual input
            jQuery('#' + region_field_id).removeAttr('readonly');
            hadRegionSelectError = true;
        },
        complete: function () {

        }
    });
}

function changeCitiesList(country_id, region_id, city_field_id, change_city) /* Параметр «change_city» — менять ли город в поле при изменении списка */ {
    var sending_data = {
        v: VK_API_VERSION,
        lang: locale,
        country_id: country_id,
        count:100,
        access_token: en4.core.vk_token,
    };

    if (region_id !== undefined && region_id !== null && parseInt(region_id) > 0) {
        sending_data["region_id"] = region_id;
        sending_data["need_all"] = 1;
    }
    else {
        sending_data["need_all"] = 0;
    }
    var fillCities = function (result) {
        var result_list = document.getElementById("result_list_" + city_field_id).getElementsByTagName("ul")[0];
        jQuery(result_list).empty();
		jQuery(result_list).addClass('city_selector');
		
        var textValueNow = document.getElementById(city_field_id).value.trim();
        var activeOpt = null;
        for (i = 0; i < result.response.items.length; i++) {
			var one = result.response.items[i];
			var cityName = one.title.trim();
			var itemContent = '<div class="city">'+cityName+'</div>';
			if (one.area){
			    if (locale == 'en'){
                    itemContent += '<div class="area">'+transliterate(one.area.replace('район', 'area'), true)+',</div>';
                    itemContent += '<div class="region">'+transliterate(one.region.replace('область', ''), true)+'</div>';
                }else{
				    itemContent += '<div class="area">'+one.area+',</div>';
				    itemContent += '<div class="region">'+one.region+'</div>';
                }
			}
			var item = jQuery('<li class="'+(one.important === 1 ? 'bold' : '')+'">'+itemContent+'</li>');

            /*КОСТЫЛЬ: для добавления в список регионов несколких городов-регионов (ищи по коду этого файла #regioncities)*/
            if (one.id == 1/*москва*/){
                one.id = -10;
                one.region = locale=='en' ? 'Moscow' : 'Москва';
            }else if (result.response.items[i].id == 2/*питер*/){
                one.id = -11;
                one.region = locale=='en' ? 'Saint Petersburg' : 'Санкт-Петербург';
            }
            /*КОСТЫЛЬ END*/
            item.attr("onmousedown", "selectCity(" + one.id + ", '" + one.title + "', '" + (one.region?one.region:'') + "', '', this);");
			item.attr("onmouseover", "highlightItem(event)");
			if (textValueNow == cityName){
                activeOpt = item;
            }
			
            jQuery(result_list).append(item);
        }

        if (activeOpt){
            jQuery(result_list).parent()[0].scrollTop = activeOpt[0].offsetTop;
        }else if (change_city){
            jQuery('#' + city_field_id).val(result.response.items[0].title).attr('data-id', getRealyCityId(result.response.items[0].id));
        }

        if (city_field_id === "school_city") {
            if(result.response.count > 0){
                changeSchoolsList(country_id, getRealyCityId(result.response.items[0].id));
            } else {
                changeSchoolsList(country_id, null);
            }
        }
        else if (city_field_id === "university_city") {
            if(result.response.count > 0){
                changeUniversitiesList(country_id, getRealyCityId(result.response.items[0].id));
            } else {
                changeUniversitiesList(country_id, null);
            }
        }
    };
    var fakeResponse = function(items){
        return {
            response : {
                items: items,
                count: items.length
            }
        };
    };
    /*КОСТЫЛЬ: для добавления в список регионов несколких городов-регионов (ищи по коду этого файла #regioncities)*/
    if ([-10,-11,-12].indexOf(parseInt(region_id))!=-1){
        var fakeRegions = locale == 'en' ?
        {
            '-10' : [{'important':1,'title':'Moscow', 'id':1 , 'region': 'Moscow'}, {'important':0,'title':'Zelenograd', 'id':1 , 'region': 'Moscow'}, {'important':0,'title':'Troitsk', 'id':1 , 'region': 'Moscow'}, {'important':0,'title':'Novomoskovsk', 'id':1 , 'region': 'Moscow'} ],
            '-11' : [{'important':1,'title':'Saint Petersburg', 'id':2 , 'region': 'Saint Petersburg'}],
            '-12' : [{'important':1,'title':'Sevastopol', 'id':185 , 'region': 'Sevastopol'}]
        }
        :{
            '-10' : [{'important':1,'title':'Москва', 'id':1 , 'region': 'Москва'}, {'important':0,'title':'Зеленоград', 'id':1 , 'region': 'Москва'}, {'important':0,'title':'Троицк', 'id':1 , 'region': 'Москва'}, {'important':0,'title':'Новомосковск', 'id':1 , 'region': 'Москва'} ],
            '-11' : [{'important':1,'title':'Санкт-Петербург', 'id':2 , 'region': 'Санкт-Петербург'}],
            '-12' : [{'important':1,'title':'Севастополь', 'id':185 , 'region': 'Севастополь'}]
        };
        fillCities( fakeResponse( fakeRegions[region_id] ) );
    /*КОСТЫЛЬ END*/
    }else{
        jQuery.ajax({
            url: "https://api.vk.com/method/database.getCities",
            dataType: "jsonp",
            data: sending_data,
            success: function(result){
                cityFilterTranslate(function(result){
                    fillCities(result);
                }, result);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
                NOTIFICATION_GENERAL.error(TITLE_LOAD_ERROR);
            }
        });
    }
}

function changeSchoolsList(country_id, city_id) {
    document.getElementById("school_name").value = "";
    if(city_id == null) return;
    jQuery.ajax(
        {
            url: "https://api.vk.com/method/database.getSchools",
            dataType: "jsonp",
            data: {
                v: VK_API_VERSION,
                lang: locale,
                country_id: country_id,
                city_id: city_id,
                access_token: en4.core.vk_token,
            },
            success: function (result) {
                var result_list, result_count;

                result_list = document.getElementById("result_list_school_name").getElementsByTagName("ul")[0];
                result_count = 100 > result.response.count ? result.response.count : "100";

                jQuery(result_list).empty();

                for (i = 0; i < result_count; i++) {
                    var result_item = document.createElement("li");
                    result_item.setAttribute("onmousedown", "selectSchool(" + result.response.items[i].id + ", '" + result.response.items[i].title.replace("'", "\\'") + "');");
                    result_item.onmouseover = highlightItem;
                    result_item.appendChild(document.createTextNode(result.response.items[i].title.trim()));
                    result_list.appendChild(result_item);
                }

                if (result.response.count >= 100) {
                    var result_item = document.createElement("li");
                    result_item.setAttribute("onmousedown", "showAnotherItem(event);");
                    result_item.onmouseover = highlightItem;
                    result_item.appendChild(document.createTextNode(_OTHER_F));
                    result_list.appendChild(result_item);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
                NOTIFICATION_GENERAL.error(TITLE_LOAD_ERROR);
            }
        });
}

function universityListHacks(response_real, country_id, city_id, query){
    let indianHackCollege = 'JIS College of Engineering';
    if (country_id == 80 /*India*/ && city_id == 1969 /*Kolkata*/
     && (!query || indianHackCollege.toLowerCase().indexOf(query.toLowerCase())!=-1)
    ){
        response_real.items = [{id: -101, title: indianHackCollege}].concat(response_real.items);
        response_real.count++;
    }
    return response_real;
};

function changeUniversitiesList(country_id, city_id) {
    document.getElementById("university_name").value = "";
    if(city_id == null) return;
    jQuery.ajax(
        {
            url: "https://api.vk.com/method/database.getUniversities",
            dataType: "jsonp",
            data: {
                v: VK_API_VERSION,
                lang: locale,
                country_id: country_id,
                city_id: city_id,
                count: "100",
                access_token: en4.core.vk_token,
            },
            success: function (result) {
                var result_list, result_count;

                result.response = universityListHacks(result.response, country_id, city_id);

                result_list = document.getElementById("result_list_university_name").getElementsByTagName("ul")[0];
                result_list.innerHTML = '';

                result_count = 100 > result.response.count ? result.response.count : "100";

                for (i = 0; i < result_count; i++) {
                    var result_item = document.createElement("li");
                    result_item.onmouseover = highlightItem;
                    result_item.setAttribute("onmousedown", "selectUniversity(" + result.response.items[i].id + ", '" + result.response.items[i].title.replace("'", "\\'") + "')");
                    result_item.appendChild(document.createTextNode(result.response.items[i].title.trim()));
                    result_list.appendChild(result_item);
                }

                if (result.response.count >= 100) {
                    var result_item = document.createElement("li");
                    result_item.onmouseover = highlightItem;
                    result_item.setAttribute("onmousedown", "showAnotherItem(event);");
                    result_item.appendChild(document.createTextNode(_OTHER_M));
                    result_list.appendChild(result_item);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
                NOTIFICATION_GENERAL.error(TITLE_LOAD_ERROR);
            }
        });
}

function changeFacultiesList(university_id) {
    if (university_id === undefined || university_id === null) {
        university_id = document.getElementById("university_name").getAttibute("data-id");
    }

    document.getElementById("university_faculty").value = "";
    if (university_id < 0) return;

    jQuery.ajax(
        {
            url: "https://api.vk.com/method/database.getFaculties",
            dataType: "jsonp",
            data: {
                v: VK_API_VERSION,
                lang: locale,
                university_id: university_id,
                count: 1000,
                access_token: en4.core.vk_token,
            },
            success: function (result) {
                var result_list = document.getElementById("result_list_university_faculty").getElementsByTagName("ul")[0];

                for (i = 0; i < result.response.count; i++) {
                    var item = document.createElement("li");
                    item.onmouseover = highlightItem;
                    item.setAttribute("onmousedown", "selectFaculty(" + result.response.items[i].id + ", '" + result.response.items[i].title.trim() + "')");
                    item.appendChild(document.createTextNode(result.response.items[i].title.trim()));
                    result_list.appendChild(item);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
                NOTIFICATION_GENERAL.error(TITLE_LOAD_ERROR);
            }
        });
}

function changeChairsList(faculty_id) /* Изменение списка базовых кафедр в зависимости от факультета */ {
    if (faculty_id === undefined || faculty_id === null) {
        faculty_id = document.getElementById("faculty_name").getAttibute("data-id");
    }

    document.getElementById("university_major").value = "";

    jQuery.ajax(
        {
            url: "https://api.vk.com/method/database.getChairs",
            dataType: "jsonp",
            data: {
                v: VK_API_VERSION,
                lang: locale,
                faculty_id: faculty_id,
                count: 1000,
                access_token: en4.core.vk_token,
            },
            success: function (result) {
                var result_list = document.getElementById("result_list_university_major").getElementsByTagName("ul")[0];

                for (i = 0; i < result.response.count; i++) {
                    var item = document.createElement("li");
                    item.onmouseover = highlightItem;
                    item.setAttribute("onmousedown", "selectItem(" + result.response.items[i].id + ", '" + result.response.items[i].title.replace("'", "\\'") + "')");
                    item.appendChild(document.createTextNode(result.response.items[i].title.trim()));
                    result_list.appendChild(item);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
                NOTIFICATION_GENERAL.error(TITLE_LOAD_ERROR);
            }
        });
}

function cityTextchange(country_id, region_id, city_query, current_container, selectFirst) {
    var autocomplete_data = {
        v: VK_API_VERSION,
        lang: locale,
        country_id: country_id,
        need_all: "1",
        q: city_query.trim()
    };

    if (region_id !== null && region_id !== undefined && region_id.trim() !== "") {
        autocomplete_data["region_id"] = region_id;
    }

    var displayResult = function (container, result, value) {
        var regexp_value = new RegExp(regexpEscape(value), "i");
        if(result.response == undefined) return; 
        var result_сount = 50 > result.response.count ? result.response.count : 50;
		var firstValue = "";
        for (i = 0; i < result_сount; i++) {
            var region_node, area_node, city_info_element, item, current_text, bold_text_position,
                autocomplete_first_title_element, autocomplete_match_title_element, autocomplete_last_title_element;

            item = document.createElement("li");
            item.className = "border";
            item.addEventListener("mouseover", highlightItem, false);

            current_text = result.response.items[i].title.trim();
            /* Название элемента из списка */
            bold_text_position = current_text.search(regexp_value);

            if (bold_text_position === -1) {
                item.appendChild(document.createTextNode(current_text));
            }
            else {
                autocomplete_first_title_element = document.createTextNode(current_text.substring(0, bold_text_position));
                autocomplete_match_title_element = document.createElement("b");
                autocomplete_match_title_element.appendChild(document.createTextNode(current_text.substr(bold_text_position, value.length)));
                autocomplete_last_title_element = document.createTextNode(current_text.substring(bold_text_position + value.length));
                item.appendChild(autocomplete_first_title_element);
                item.appendChild(autocomplete_match_title_element);
                item.appendChild(autocomplete_last_title_element);
            }


            item.setAttribute("onmousedown", "selectCity(" + result.response.items[i].id + ", '" + result.response.items[i].title.trim() + "', '"+(result.response.items[i].region?result.response.items[i].region:'')+"', '', this)");
			
			if (!firstValue){
				firstValue = result.response.items[i].id ;
			}
            container.appendChild(item);
        }
		if (selectFirst){
			current_container.setAttribute("style", "display:none;");
			jQuery(current_container).parent().find('.city:first').attr("data-id", firstValue);
			if (typeof selectFirst == "function"){
				selectFirst(firstValue);
			};
			return;
		}
    };
    autocomleteAction("https://api.vk.com/method/database.getCities", autocomplete_data, "jsonp", "get", current_container, displayResult, city_query);
}

function schoolTextchange(country_id, city_id, school_query) {
    var current_container = document.getElementById("result_list_school_name");

    var autocomplete_data = {
        v: VK_API_VERSION,
        lang: locale,
        country_id: country_id,
        city_id: city_id,
        q: school_query.trim()
    };


    var displayResult = function (container, result, value) {
        var regexp_value, result_сount;

        regexp_value = new RegExp(regexpEscape(value), "i");
        result_сount = 100 > result.response.count ? result.response.count : 100;

        for (i = 0; i < result_сount; i++) {
            var item, current_text, bold_text_position, autocomplete_first_title_element, autocomplete_match_title_element, autocomplete_last_title_element;

            item = document.createElement("li");
            item.onmouseover = highlightItem;
            item.setAttribute("onmousedown", "selectSchool(" + result.response.items[i].id + ", '" + result.response.items[i].title.replace("'", "\\'") + "')");

            current_text = result.response.items[i].title.trim();
            /* Название элемента из списка */
            bold_text_position = current_text.search(regexp_value);

            if (bold_text_position === -1) {
                item.appendChild(document.createTextNode(current_text));
            }
            else {
                autocomplete_first_title_element = document.createTextNode(current_text.substring(0, bold_text_position));
                autocomplete_match_title_element = document.createElement("b");
                autocomplete_match_title_element.appendChild(document.createTextNode(current_text.substr(bold_text_position, value.length)));
                autocomplete_last_title_element = document.createTextNode(current_text.substring(bold_text_position + value.length));
                item.appendChild(autocomplete_first_title_element);
                item.appendChild(autocomplete_match_title_element);
                item.appendChild(autocomplete_last_title_element);
            }

            container.appendChild(item);
        }

        if (result.response.count > 100) {
            var other_item = document.createElement("li");
            other_item.onmouseover = highlightItem;
            other_item.setAttribute("onmousedown", "showAnotherItem(event);");
            other_item.appendChild(document.createTextNode(_OTHER_F));
            container.appendChild(other_item);
        }
    };
    //autocomleteAction(autocomplete_url, autocomplete_data, data_type, request_type, result_list, displayResult, value)
    autocomleteAction("https://api.vk.com/method/database.getSchools", autocomplete_data, "jsonp", "get", current_container, displayResult, school_query);
}

function universityTextchange(country_id, city_id, university_query) {
    var current_container = document.getElementById("result_list_university_name");

    var autocomplete_data = {
        v: VK_API_VERSION,
        lang: locale,
        country_id: country_id,
        city_id: city_id,
        q: university_query.trim()
    };

    var displayResult = function (container, result, value) {
        var regexp_value = new RegExp(regexpEscape(value), "i");

        result.response = universityListHacks(result.response, country_id, city_id, university_query);
        var result_сount = 100 > result.response.count ? result.response.count : 100;

        for (i = 0; i < result_сount; i++) {
            var item, current_text, bold_text_position, autocomplete_first_title_element, autocomplete_match_title_element, autocomplete_last_title_element;

            item = document.createElement("li");
            item.onmouseover = highlightItem;
            item.setAttribute("onmousedown", "selectUniversity(" + result.response.items[i].id + ", '" + result.response.items[i].title.replace("'", "\\'") + "')");

            current_text = result.response.items[i].title.trim();
            /* Название элемента из списка */
            bold_text_position = current_text.search(regexp_value);

            if (bold_text_position === -1) {
                item.appendChild(document.createTextNode(current_text));
            }
            else {
                autocomplete_first_title_element = document.createTextNode(current_text.substring(0, bold_text_position));
                autocomplete_match_title_element = document.createElement("b");
                autocomplete_match_title_element.appendChild(document.createTextNode(current_text.substr(bold_text_position, value.length)));
                autocomplete_last_title_element = document.createTextNode(current_text.substring(bold_text_position + value.length));
                item.appendChild(autocomplete_first_title_element);
                item.appendChild(autocomplete_match_title_element);
                item.appendChild(autocomplete_last_title_element);
            }

            container.appendChild(item);
        }

        if (result.response.count > 100) {
            var other_item = document.createElement("li");
            other_item.onmouseover = highlightItem;
            other_item.setAttribute("onmousedown", "showAnotherItem(event);");
            other_item.appendChild(document.createTextNode(_OTHER_M));
            container.appendChild(other_item);
        }
    };
    autocomleteAction("https://api.vk.com/method/database.getUniversities", autocomplete_data, "jsonp", "get", current_container, displayResult, university_query);
}

function highlightItem(event) {
    var element, hover_elements;

    element = event.target;

    while (element && element.tagName !== "LI") {
        element = element.parentNode;
    }

    hover_elements = element.parentNode.parentNode.getElementsByClassName("hover");
    jQuery(hover_elements).removeClass("hover");

    element.className += " hover";
}

function selectItem(event, data_id) {
    var target_element;
    if (event.target.tagName === "LI") {
        target_element = event.target;
    }
    else {
        target_element = event.target.parentNode;
    }
    var container = target_element.parentNode.parentNode;
    container.style.display = "none";
    var field_id = container.id.replace(/result_list_/, "");
    var field_element = document.getElementById(field_id);

    if (field_element === null) {
        return false;
    }

    if (data_id !== undefined && data_id !== null) {
        field_element.setAttribute("data-id", data_id);
    }
    else {
        field_element.removeAttribute("data-id");
    }

    switch (target_element.textContent.trim()) {
        case _NONE_SELECTED_M.trim():
        case _NONE_SELECTED_N.trim():
        case _NONE_SELECTED_F.trim():
        case _NOT_SPECIFIED_M.trim():
        case _NOT_SPECIFIED_N.trim():
        case _NOT_SPECIFIED_F.trim():
        {
            if (field_element.value !== "") {
                field_element.value = "";

                if (field_id === "school_class" && document.getElementById("school_graduation").value.trim() !== "") {
                    document.getElementById("school_graduation").value = "";
                }
                else if (field_id === "school_graduation" && document.getElementById("school_class").value.trim() !== "") {
                    document.getElementById("school_class").value = "";
                }
            }
        }
            break;

        default:
        {
            if (field_element.value !== target_element.textContent.trim()) {
                field_element.value = target_element.textContent.trim();

                if (field_id === "school_class") {
                    var class_number = parseInt(target_element.textContent.trim());

                    var current_date = new Date();

                    if (current_date.getMonth() > 5) /* После июня человек считается перешедшим в следующий класс */
                    {
                        document.getElementById("school_graduation").value = current_date.getFullYear() + 12 - class_number;
                    }
                    else {
                        document.getElementById("school_graduation").value = current_date.getFullYear() + 11 - class_number;
                    }
                }
                else if (field_id === "school_graduation") {
                    var graduation_year = parseInt(target_element.textContent.trim());
                    var current_date = new Date();
                    var class_value;
                    
                    if ((graduation_year < current_date.getFullYear() || (graduation_year === current_date.getFullYear() && current_date.getMonth() > 5)) && jQuery("#school_class").val() !== "") {
                        class_value = "";
                    }
                    else {
                        if (current_date.getMonth() > 5) /* После июня человек считается перешедшим в следующий класс */
                        {
                            class_value = 12 - (graduation_year - current_date.getFullYear());
                        }
                        else {
                            class_value = 11 - (graduation_year - current_date.getFullYear());
                        }
                    }

                    if (class_value >= 1 && class_value <= 11 && parseInt(jQuery("#school_class").val()) !== class_value) {
                    	jQuery("#school_class").val(class_value);
                    }
                    else if (!(class_value >= 1 && class_value <= 11) && jQuery("#school_class").val() !== "") {
                    	jQuery("#school_class").val("");
                    }

                }
            }
        }
    }

    field_element.focus();
    return false;
}

function autocomleteAction(autocomplete_url, autocomplete_data, data_type, request_type, result_list, displayResult, value) {
    if (!autocomplete_data)  autocomplete_data = {};
    autocomplete_data.access_token = en4.core.vk_token;
    jQuery.ajax({
        url: autocomplete_url,
        dataType: data_type,
        type: request_type,
        data: autocomplete_data,
        beforeSend: function (xhr) {

        },
        success: function(result) {
            var operate = function(result){
                if (result.length === 0 || "response" in result && (result.response.count === 0 || "items" in result.response && result.response.items.length === 0)) {
                    if (result_list.style.display !== "none") {
                        result_list.style.display = "none";
                    }
                    return false;
                }

                if (result_list !== null && result_list !== undefined && result_list.style.display === "none") {
                    result_list.style.display = "";
                }
                if (result_list){
                    result_list.scrollTop = 0;
                }
                var container = result_list.getElementsByTagName("ul")[0];
                if (container === undefined) {
                    container = document.createElement("ul");
                    jQuery(result_list).empty().append(container);
                }
                else {
                    jQuery(container).empty();
                }

                displayResult(container, result, value);
            };

            if (autocomplete_url.indexOf('database.getCities')==-1) operate(result);
            else cityFilterTranslate(function(result){
                operate(result);
            }, result);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR, textStatus, errorThrown);
            NOTIFICATION_GENERAL.error(jqXHR.status + " — " + textStatus);
        }
    });
}

/* Обработка аватарок */

function lassoSetCoords(coords) {
    var delta, coordinates, previewImage;

    delta = (coords.w - 48) / coords.w;
    coordinates = document.getElementById("coordinates");
    coordinates.value = coords.x + ':' + coords.y + ':' + coords.w + ':' + coords.h;
}

function lassoStart() {
    var lassoApplyElement, lassoCancelElement;
    if (!orginalThumbSrc) {
        orginalThumbSrc = document.getElementById("previewimage").src;
    }

    originalSize = document.getElementById("lassoImg").getSize();
    lassoCrop = new Lasso.Crop("lassoImg", {
        bgimage: "",
        ratio: [1, 1],
        preset: [10, 10, 58, 58],
        min: [48, 48],
        handleSize: 8,
        opacity: .6,
        color: "#7389AE",
        border: "/externals/moolasso/crop.gif",
        onResize: lassoSetCoords
    });

    lassoApplyElement = document.createElement("span");
    lassoCancelElement = document.createElement("span");

    lassoApplyElement.className = "lasso-apply";
    lassoCancelElement.className = "lasso-cancel";

    lassoApplyElement.onclick = lassoEnd;
    lassoCancelElement.onclick = lassoCancel;

    lassoApplyElement.appendChild(document.createTextNode(TITLE_APPLY_CHANGES));
    lassoCancelElement.appendChild(document.createTextNode(TITLE_CANCEL));

    jQuery("#thumbnail-controller").empty().append([lassoApplyElement, lassoCancelElement]);

    jQuery(".photo-update:visible, .photo-remove:visible").hide();

    document.getElementById("coordinates").value = "10:10:58:58";
}

/* Успешное завершение обрезки аватара */
function lassoEnd() {
    var initialThumbButton, uploadData, editingUserId, button, jqButton;

    initialThumbButton = document.createElement("span");
    initialThumbButton.className = "lasso-button";
    initialThumbButton.onclick = lassoStart;
    initialThumbButton.appendChild(document.createTextNode(TITLE_THUMBNAIL));

    button = document.querySelector(".lasso-apply");
    jqButton = jQuery(button);

    if(lassoCrop && "destroy" in lassoCrop) {
        lassoCrop.destroy();
    }


    uploadData = {
        "action": "resize",
        "coordinates": document.getElementById("coordinates").value
    };

    editingUserId = +document.getElementById("editing_user_id").value;

    if (editingUserId > 0) {
        uploadData["user_id"] = editingUserId;
    }

    /* Resize ajax request */
    jQuery.ajax({
        url: "/members/edit/photo/",
        dataType: "JSON",
        type: "POST",
        data: uploadData,
        beforeSend: function () {
            button.onclick = function() {
                return false;
            };

            jqButton.css({"border-bottom": "none", "height": jqButton.outerHeight(), "width": jqButton.outerWidth()}).empty().append(getLoaderImg("micro"));
        },
        success: function (result, textStatus, jqXHR) {
            var elementPreviewImage, element_fieldset;

            if(result.status !== true) {
                this.error(jqXHR, textStatus, "serverError");
                return false;
            }

            elementPreviewImage = document.getElementById("previewimage");

            if(result.thumb_src) {
                elementPreviewImage.className = "item_photo_user thumb_icon";
                elementPreviewImage.src = result.thumb_src;
                orginalThumbSrc = result.thumb_src;
            }

            jQuery("#thumbnail-controller").empty().append(initialThumbButton);
            jQuery(".photo-update:hidden, .photo-remove:hidden").show();
            

            NOTIFICATION_GENERAL.success("Ваша фотография успешно обрезана");
            jQuery('#previewimage').attr('src', jQuery('#previewimage').attr('src').split('?')[0]+ '?'+new Date().getTime());
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error(jqXHR, textStatus, errorThrown);
            NOTIFICATION_GENERAL.error("Произошла ошибка при изменении фото!");

            button.onclick = lassoEnd;
            button.removeAttribute("style");

            jqButton.empty().append(document.createTextNode(TITLE_APPLY_CHANGES));
        }
    });
}

function lassoCancel() {
    var initialThumbButton;

    if(orginalThumbSrc) {
        document.getElementById("previewimage").src = orginalThumbSrc; /* Сброс картинки-превью на исходную */
    }

    document.getElementById("coordinates").value = ""; /* Координаты обрезанной части аватара */

    initialThumbButton = document.createElement("span");
    initialThumbButton.className = "lasso-button";
    initialThumbButton.onclick = lassoStart;
    initialThumbButton.appendChild(document.createTextNode(TITLE_THUMBNAIL));

    jQuery("#thumbnail-controller").empty().append(initialThumbButton);
    jQuery(".photo-update:hidden, .photo-remove:hidden").show();

    if(lassoCrop && "destroy" in lassoCrop) {
        lassoCrop.destroy();
    }
}

function changeFilePhoto(event) {
    var file_reader, max_size;

    max_size = parseInt(document.getElementById("MAX_FILE_SIZE").value);

    if (event.target.files && event.target.files[0]) {
        if (event.target.files[0].type !== "image/jpeg" && event.target.files[0].type !== "image/png" && event.target.files[0].type !== "image/gif") {
            NOTIFICATION_GENERAL.warning("Допустимые форматы: jpeg, jpg, png, gif");
            event.target.value = null;
            return false;
        }

        if (max_size > 0 && event.target.files[0].size > max_size) {
            NOTIFICATION_GENERAL.warning("Загружаемое изображение должно быть не более 15 MB!");
            event.target.value = null;
            return false;
        }

        if ("FileReader" in window) {
            file_reader = new FileReader();

            file_reader.onload = function (e) {
                var newThumb, newIcon;

                if (e.target.result) {
                    newThumb = document.getElementsByClassName("new_thumb").item(0);
                    newIcon = document.getElementsByClassName("new_icon").item(0);

                    newThumb.src = e.target.result;
                    newIcon.src = e.target.result;
                }
            };

            file_reader.readAsDataURL(event.target.files[0]);

            jQuery(".new_avatar:hidden, .middle_cell:hidden").show();
        }

        upload_photo_data = event.target.files[0];
        event.target.value = null;
    }
    else {
        jQuery(".new_avatar:visible, .middle_cell:visible").hide();
    }
}

function changeCancel(event) {
    var newThumb, newIcon;
    jQuery(".new_avatar:visible, .middle_cell:visible").hide();
    newThumb = document.getElementsByClassName("new_thumb").item(0);
    newIcon = document.getElementsByClassName("new_icon").item(0);

    if (newThumb) {
        newThumb.removeAttribute("src");
    }

    if (newIcon) {
        newIcon.removeAttribute("src");
    }

    jQuery(".file-info").empty();
    upload_photo_data = new uploadDataConstructor;

    document.getElementById("imageurl").value = "";
    document.getElementById("coordinates").value = "";
    document.getElementById("Filedata").value = null;
    return false;
}

function uploadAvatar() {
    var form_element, form_data, done_button, editing_user_id;

    form_element = document.getElementById("EditPhoto");
    done_button = jQuery(".photo-update");

    if (upload_photo_data.length === 0 && document.getElementById("imageurl").value.trim() === "") {
        NOTIFICATION_GENERAL.warning("Не выбрано новое фото");
        window.onFieldsSendedHook();
        return false;
    }

    editing_user_id = +document.getElementById("editing_user_id").value;

    form_data = new FormData();

    if(document.getElementById("imageurl").value.trim() === "") {
        form_data.append("Filedata", upload_photo_data);
    } else {
        form_data.append("imageurl", document.getElementById("imageurl").value);
    }

    if (editing_user_id > 0) {
        form_data.append("user_id", editing_user_id);
    }

    jQuery.ajax({
        url: "/members/edit/photo/",
        data: form_data,
        processData: false,
        contentType: false,
        dataType: "JSON",
        type: "POST",
        beforeSend: function () {
            done_button.css({
                width: done_button.innerWidth(),
                height: done_button.innerHeight()
            }).empty().append(getLoaderImg());

            document.getElementById("EditPhoto").onsubmit = function () {
                return false;
            };
        },
        success: function (result, textStatus, jqXHR) {
            var elementLassoImg, elementPreviewImage, element_fieldset, newThumb, newIcon, button_photo_remove;

            if (result.status !== true) {
                this.error(jqXHR, textStatus, "serverError");
                return false;
            }

            elementLassoImg = document.getElementById("lassoImg");
            elementPreviewImage = document.getElementById("previewimage");

            newThumb = document.getElementsByClassName("new_thumb").item(0);
            newIcon = document.getElementsByClassName("new_icon").item(0);
            button_photo_remove = document.getElementsByClassName("photo-remove").item(0);

            if (result.photo_src) {
                elementLassoImg.src = result.photo_src;
                elementLassoImg.className = "item_photo_user thumb_profile";
            }

            if (result.thumb_src) {
                elementPreviewImage.src = result.thumb_src;
                orginalThumbSrc = result.thumb_src;
                elementPreviewImage.className = "item_photo_user thumb_icon";
            }

            if (newThumb !== null) {
                newThumb.removeAttribute("src");
            }

            if (newIcon !== null) {
                newIcon.removeAttribute("src");
            }

            if (button_photo_remove !== null && button_photo_remove.style.display === "none") {
                button_photo_remove.style.display = "";
            }

            jQuery(".new_avatar:visible, .middle_cell:visible").hide();
            jQuery(".file-info").empty();
            jQuery("#thumbnail-controller").show();

            /* Обнуляем поля при успехе */
            document.getElementById("Filedata").value = null;
            document.getElementById("coordinates").value = "";
            document.getElementById("imageurl").value = "";

            upload_photo_data = new uploadDataConstructor;

            element_fieldset = document.getElementById("fieldset-buttons");

            NOTIFICATION_GENERAL.success("Ваша фотография успешно изменена");
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("ajax_error", jqXHR, textStatus, errorThrown);
            NOTIFICATION_GENERAL.error("Произошла ошибка при изменении фото!");
        },
        complete: function () {
            window.onFieldsSendedHook();
            done_button.empty().append(document.createTextNode("Сохранить фото"));
            if (done_button[0] !== undefined) {
                done_button[0].removeAttribute("style");
            }

            document.getElementById("EditPhoto").onsubmit = uploadAvatar;
        }
    });

    return false;
}

function photoSocial(data) {
    var elementLassoImg, elementPreviewImage, newThumb, newIcon, buttonPhotoRemove;

    if(data.status !== true) {
        NOTIFICATION_GENERAL.error("Произошла ошибка при загрузке фото");
        if(socialPopup) {
            socialPopup.close();
        }
        return false;
    }

    elementLassoImg = document.getElementById("lassoImg");
    elementPreviewImage = document.getElementById("previewimage");

    newThumb = document.getElementsByClassName("new_thumb").item(0);
    newIcon = document.getElementsByClassName("new_icon").item(0);
    buttonPhotoRemove = document.getElementsByClassName("photo-remove").item(0);

    if (data.photo_src) {
        elementLassoImg.src = data.photo_src;
        elementLassoImg.className = "item_photo_user thumb_profile";
    }

    if (data.thumb_src) {
        elementPreviewImage.src = data.thumb_src;
        orginalThumbSrc = data.thumb_src;
        elementPreviewImage.className = "item_photo_user thumb_icon";
    }

    if (newThumb !== null) {
        newThumb.removeAttribute("src");
    }

    if (newIcon !== null) {
        newIcon.removeAttribute("src");
    }

    if (buttonPhotoRemove && buttonPhotoRemove.style.display === "none") {
        buttonPhotoRemove.style.display = "";
    }

    jQuery(".new_avatar:visible, .middle_cell:visible").hide();
    jQuery(".file-info").empty();
    jQuery("#thumbnail-controller").show();

    /* Обнуляем поля при успехе */
    document.getElementById("Filedata").value = null;
    document.getElementById("coordinates").value = "";
    document.getElementById("imageurl").value = "";

    upload_photo_data = new uploadDataConstructor;
    NOTIFICATION_GENERAL.success("Ваша фотография успешно изменена");
}

function RemoveUserPhoto(event) {
    var deletePhotoButton, jqDeletePhotoButton, userId, uploadData;

    deletePhotoButton = event.target;
    jqDeletePhotoButton = jQuery(deletePhotoButton);

    uploadData = {action: "remove"};

    userId = parseInt(document.getElementById("editing_user_id").value);

    if(userId > 0) {
        uploadData["user_id"] = userId;
    }

    jQuery.ajax({
        url: "/members/edit/photo",
        dataType: "JSON",
        data: uploadData,
        beforeSend: function () {
            jqDeletePhotoButton.css({
                width: jqDeletePhotoButton.outerWidth(),
                height: jqDeletePhotoButton.outerHeight()
            }).empty().append(getLoaderImg());

            deletePhotoButton.onclick = function () {
                return false;
            };
        },
        success: function (result, textStatus, jqXHR) {
            var elementLassoImg, elementPreviewImage, newThumb, newIcon;

            if (result.status !== true) {
                this.error(jqXHR, textStatus, "serverError");
                return false;
            }

            elementLassoImg = document.getElementById("lassoImg");
            elementPreviewImage = document.getElementById("previewimage");

            newThumb = document.getElementsByClassName("new_thumb").item(0);
            newIcon = document.getElementsByClassName("new_icon").item(0);

            jQuery("#thumbnail-controller").hide();
            jQuery("#remove").remove();

            jQuery(".new_avatar:visible, .middle_cell:visible").hide();

            if (elementLassoImg) {
                elementLassoImg.src = "/application/modules/User/externals/images/nophoto_user_thumb_profile.png";
                elementLassoImg.className = "item_photo_user thumb_profile item_nophoto";
            }

            if (elementPreviewImage) {
                elementPreviewImage.src = "/application/modules/User/externals/images/nophoto_user_thumb_icon.png";
                elementPreviewImage.className = "item_photo_user thumb_icon item_nophoto";

                orginalThumbSrc = elementPreviewImage.src;
            }

            if (newThumb) {
                newThumb.removeAttribute("src");
            }

            if (newIcon) {
                newIcon.removeAttribute("src");
            }

            NOTIFICATION_GENERAL.success("Ваша фотография успешно удалена");

            document.getElementById("Filedata").value = null;

        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error(jqXHR, textStatus, errorThrown);
            NOTIFICATION_GENERAL.error("Произошла ошибка при удалении фотографии");
        },
        complete: function () {
            jqDeletePhotoButton.empty().append(document.createTextNode("Удалить фото"));
            deletePhotoButton.onclick = RemoveUserPhoto;
            deletePhotoButton.removeAttribute("style");
            deletePhotoButton.style.display = "none";
        }
    });
    return false;
}

function previewURLPicture(url) {
    var newThumb, newIcon, element_file_info;

    element_file_info = document.getElementsByClassName("file-info").item(0);
    newThumb = document.getElementsByClassName("new_thumb").item(0);
    newIcon = document.getElementsByClassName("new_icon").item(0);

    newThumb.src = url;
    newIcon.src = url;

    upload_photo_data = new uploadDataConstructor;
    document.getElementById("Filedata").value = null;
    document.getElementById("imageurl").value = url;

    element_file_info.textContent = "";

    jQuery(".new_avatar:hidden, .middle_cell:hidden").show();

    lassoCancel();
}

function previewUploadPicture() {
    var file_reader, size_text, element_file_info;

    element_file_info = document.getElementsByClassName("file-info").item(0);

    if ("FileReader" in window) {
        file_reader = new FileReader();

        file_reader.onload = function (e) {
            var newThumb, newIcon;

            if (e.target.result) {
                newThumb = document.getElementsByClassName("new_thumb").item(0);
                newIcon = document.getElementsByClassName("new_icon").item(0);

                newThumb.src = e.target.result;
                newIcon.src = e.target.result;
            }
        };

        file_reader.onloadend = function (e) {};

        file_reader.onloadstart = function (e) {};

        file_reader.onprogress = function (e) {};

        file_reader.readAsDataURL(upload_photo_data);

        jQuery(".new_avatar:hidden, .middle_cell:hidden").show();
    }

    if (upload_photo_data.size > 1024) {
        size_text = upload_photo_data.size / 1024;

        if (size_text > 1024) {
            size_text = Math.round(size_text / 1024) + "Mb";
        }
        else {
            size_text = Math.round(size_text) + "Kb";
        }
    }
    else {
        size_text = Math.round(upload_photo_data.size) + "B";
    }

    element_file_info.textContent = upload_photo_data.name + ", " + size_text;
    document.getElementById("imageurl").value = "";

    lassoCancel();
}

// Settings form submittings
jQuery(function(){
	var submitForm = function(event){
		event.preventDefault();
		var form = jQuery(this);
		var data = form.serialize();
		form.addClass('blurred');
		jQuery.post(form.attr('action'), data, function(responce){
			var newForm = jQuery('<div>' + responce + '</div>').find('form');
			form.replaceWith(newForm);
            jQuery('body').append(jQuery('<div>' + responce + '</div>').find('script'));
            initFormDecoration(newForm);
		});
		return false;
	};
	
	
	jQuery('.generic_layout_container.settings_general').on('submit', 'form', submitForm);
	jQuery('.generic_layout_container.settings_privacy').on('submit', 'form', submitForm);
	jQuery('.generic_layout_container.settings_notifications').on('submit', 'form', submitForm);
	jQuery('.generic_layout_container.settings_password').on('submit', 'form', submitForm);
});

//Add label to documents tab
jQuery(function($){
    let passportLastInput = $('#passport_code,#passport_date,#passport_number').last();
    if (!passportLastInput.length) return;

    passportLastInput.parents('.form-wrapper')
        .append('<h4 style="transform: translateX(-42px);margin-top: 20px;">'+en4.core.language.translate('Other documents')+'</h4>')
        .parents('.form-elements:first').css('overflow', 'visible');
});