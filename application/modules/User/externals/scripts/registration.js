"use strict";
var NOTIFICATION,
    NOTIFICATION_TOOLTIP,
    _REAL_NAME,
    last_focused, //Последний элемент, на котором был фокус
    datepickerParams = {
        startDate: "01.05." + String((new Date()).getFullYear() - 15),
        maxDate: "01.05." + String((new Date()).getFullYear() - 5),
        timepicker: false,
        format: "d.m.Y",
        formatDate: "d.m.Y",
        scrollMonth:false
    },
    form_status = {};
    if (locale === "ru") {
        datepickerParams["lang"] = "ru";
        datepickerParams["dayOfWeekStart"] = 1;
        datepickerParams["i18n"] = {
            ru: {
                months: [
                    "Январь", "Февраль", "Март", "Апрель",
                    "Май", "Июнь", "Июль", "Август",
                    "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"
                ],
                dayOfWeek: [
                    "Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"
                ]
            }
        };
        _REAL_NAME = "На <strong>" + window.location.host + "</strong> принято использовать <strong>настоящие ФИО</strong>, записанные кириллицей. <br><br>Например: Петрова Анна Алексеевна, Иванов Пётр Николаевич.";
    }
    else {
        datepickerParams["lang"] = "en";
        _REAL_NAME = "We ask our users to use their <strong>full</strong> and <strong>real name</strong> on <strong>" + window.location.host + "</strong>. <br><br>For example: Laurence John Fishburne, Edward Joseph Snowden.";
    }

    NOTIFICATION = new NotificationGeneral(); /* Виджет для «уведомлений» */
    NOTIFICATION_TOOLTIP = new NotificationGeneral; /* Виджет для подсказок */

    NOTIFICATION_TOOLTIP.htmlContent = true;
    NOTIFICATION_TOOLTIP.autoremoveDuration = 0;


jQuery(document).ready(function($) {
    var mainForm, formElements;

    locale = document.querySelector("html").lang;

    mainForm = document.getElementById("registration_form");
    $(mainForm).submit(registration);

    formElements = mainForm.elements;
    //registration_form

    $("#birthdate").datetimepicker(datepickerParams);
    $("#email").on("input", emailTextchange);

    for (var i = 0; i < formElements.length; i++) {
        if (formElements[i].onkeydown !== null) {
            formElements[i].oninput = formElements[i].onkeydown;
            formElements[i].onkeydown();
            /* Проверка значений по автозаполнению */
        }

        if (!formElements[i].id) {
            continue;
        }


        switch (formElements[i].id) {
            case "gender":
            case "first_name":
            case "middle_name":
            case "last_name":
            case "mobilephone":
            case "birthdate":
            case "school_class":
            case "university_course":
            case "email": {
                form_status[formElements[i].id] = true;
            }
                break;

            case "password_confirm": {
                form_status[formElements[i].id] = false;
                form_status.passwords_match = false;
            }
                break;

            case "recaptcha_challenge_field": continue;
                break;

            default: {
                form_status[formElements[i].id] = false;
            }
        }
    }
    jQuery(".person_name").on("focus", function(event) {
        if (jQuery('#smoothbox_window').length){
            _REAL_NAME  = _REAL_NAME.split('<br')[0];
        }
        var method = document.location.href.indexOf("format=smoothbox")!=-1 ? 'tooltipTop' : 'tooltipRight';
        NOTIFICATION_TOOLTIP[method]( jQuery(this).attr('id')== 'last_name' ?  _REAL_NAME : jQuery(this).attr('title'), this);
    }).on("blur", function(event) {
        NOTIFICATION_TOOLTIP.hide();
        if (event.target.value !== event.target.value.trim()) {
            event.target.value = event.target.value.trim();
        }
    });

    /*goncharov 2015-05 below*/
    jQuery('#mobilephone').css('color','#b7b7b7');
    var mobilephoneColorRev = function(){
        jQuery('#mobilephone').removeAttr('style');
        jQuery('#mobilephone').off('focus', mobilephoneColorRev);
    };
    jQuery('#mobilephone').on('focus',mobilephoneColorRev);

    //moving elements below labels
    var beginLabelUpping = false;
    jQuery('.form-label label').each(function(){
        var label = jQuery(this);
        if (label.attr('for')=='mobilephone'){
            beginLabelUpping = true;
        }
        if (beginLabelUpping){
            var cloned = label.clone().attr('class', 'formUpperLabel');
            var wholeWrapper = label.parents('.form-wrapper:first').addClass('formUpperLabelRow');
            var elementWrapper = wholeWrapper.find('.form-element:first');
            elementWrapper.prepend(cloned);
            label.hide();
        }
    });
    jQuery('#profile_status').attr('required','required');
});



function check() {
    for (var key in form_status) {
        if (!form_status[key]) {
            if (key=="password_match"){
                jQuery("#password_confirm").css({"background-color": FORM_ERROR_COLOR}).focus().animate({backgroundColor: "rgb(255, 255, 255)"}, 1000);
                 return false;
            }else if (key=="terms"){
                if (document.getElementById("terms_correct").textContent === "") {
                    document.getElementById("terms_correct").textContent = CONST_TITLE.TERMS_EMPTY;
                }

                document.getElementById("terms").focus();
                return false;
            }
        }
    }
    return true;
}

function password_length(input, difficulty, red_line, yellow_line, green_line, max_n) {
    var password_difficulty_button = " <img alt='" + CONST_TITLE.HELP + "' class='help' height=\"16\" id=\"password_difficulty_button\" onclick=\"passwordDifficultyBlockShow();\" title=\"" + CONST_TITLE.HELP + "\" src=\"/externals/pmd/images/help.png\" width=\"16\">";
    var current = document.getElementById(input).value;
    var result = zxcvbn(current);
    document.getElementById("password_difficulty_content").innerHTML = CONST_TITLE.CRACKING_TIME + ": " + result.crack_time_display + " (" + result.crack_time + " c)" +
    "<br/>" + CONST_TITLE.PASSWORD_ENTROPY + ": " + result.entropy;

    var number = parseInt(current.length);

    var part = number * (100 / max_n);

    if (part > 100)
        part = 100;

    var simbol_word = declension(" символ", " символа", " символов", " symbols", number, locale);
    simbol_word += password_difficulty_button;
    if (number === 0 || number < 0) {
        elementRewrite(difficulty, "");

        elementHide(red_line);
        elementHide(yellow_line);
        elementHide(green_line);
    }
    if ((number < 6) && (number > 0) && !CIRILIC_PATTERN.test(current)) {
        difficulty.innerHTML = CONST_TITLE.SHORT_PASSWORD + ", " + number + simbol_word;

        difficulty.style.color = "#AD0C0C"; //red        

        elementHide(yellow_line);
        elementHide(green_line);
        elementShow(red_line);

        red_line.style.width = part + "%";
    }
    if (number >= 6 && result.score <= 3 && !CIRILIC_PATTERN.test(current)) {
        difficulty.innerHTML = CONST_TITLE.WEAK_PASSWORD + ", " + number + simbol_word;

        if (difficulty.style.color !== "#BC7216")
            difficulty.style.color = "#BC7216"; //yellow 

        elementHide(red_line);
        elementHide(green_line);
        elementShow(yellow_line);

        yellow_line.style.width = part + "%";
    }

    if (number >= 6 && result.score > 3 && !CIRILIC_PATTERN.test(current)) {
        difficulty.innerHTML = CONST_TITLE.SECURE_PASSWORD + ", " + number + simbol_word;

        if (difficulty.style.color !== "green") {
            difficulty.style.color = "green";
        }

        elementHide(red_line);
        elementHide(yellow_line);
        elementShow(green_line);

        green_line.style.width = part + "%";
    }

    if (number >= max_n && !CIRILIC_PATTERN.test(current)) {
        //#BC7216 — yellow
        var color = (result.score > 2 && result.score <= 3) ? "#BC7216" : "green";
        difficulty.innerHTML = CONST_TITLE.MAX_LENGTH_PASSWORD + ",  " + number + simbol_word;

        if (difficulty.style.color !== color) {
            difficulty.style.color = color;
        }
    }

    if (document.getElementById("password_difficulty_block_triangle") !== null && document.getElementById("password_difficulty_button") !== null &&
        document.getElementById("password_difficulty_button").offsetLeft !== null && document.getElementById("password_difficulty_button").offsetLeft !== undefined &&
        document.getElementById("password_difficulty_block_triangle").style.left !== document.getElementById("password_difficulty_button").offsetLeft + "px") {
        document.getElementById("password_difficulty_block_triangle").style.left = document.getElementById("password_difficulty_button").offsetLeft + "px";
    }

    if (parent.Smoothbox.instance){
        parent.Smoothbox.instance.doAutoResize();
    }

}


var registration = function(event) {
    event.preventDefault();
    event.stopPropagation();
    if (window.ajaxSended) return false;
    var sendData, doneButton, mainForm, formExist = {};
    if (!check()) /* Часть полей заполнена неверно */
    {
        return false;
    }

    mainForm = document.getElementById("registration_form");
    doneButton = jQuery(mainForm.querySelector("button[type='submit']"));

    sendData = {"timezone_offset": String((new Date()).getTimezoneOffset())};

    for (var key in form_status) {if (form_status.hasOwnProperty(key)){
        switch (key) {
            case "terms": {
                sendData[key] = (+(document.getElementById("terms").checked))
                formExist[key] = true;
            }
            break;

            case "recaptcha_response_field": {
                sendData["recaptcha_challenge_field"] = document.getElementById("recaptcha_challenge_field").value.trim();
                sendData[key] = document.getElementById(key).value.trim();
                formExist[key] = true;
            }
            break;

            case "passwords_match": {
                formExist[key] = false;
            }
            break;
            default: {
                sendData[key] = document.getElementById(key).value.trim();
                formExist[key] = true;
            }
        }
    }}
    sendData['mobilephone_country'] =  document.getElementById("mobilephone_country").value;
    let bitsaCaptcha = jQuery("#bitsa_captcha");
    if (bitsaCaptcha.length > 0)
        sendData['bitsa_captcha'] =  bitsaCaptcha.val();

    window.ajaxSended = true;
    jQuery.ajax({
        url: "/signup/check/",
        dataType: "JSON",
        type: "POST",
        data: sendData,
        beforeSend: function () {
            doneButton.css({height: doneButton.innerHeight(), width: doneButton.innerWidth()}).empty().append(getLoaderImg());
        },
        success: function (response, textStatus, jqXHR) {
            switch (response.status) {
                case "success":
                {
                    if (typeof response.redirect == 'string') {
                        parent.location.href = response.redirect;
                        return;
                    }
                    var message_title, message_box;

                    message_title = document.createElement("h2");
                    message_title.setAttribute("style", "text-align:center");
                    message_title.appendChild(document.createTextNode( en4.core.language.translate('Registration')));

                    message_box = document.createElement("div");
                    message_box.className = "blue_border_message";

                    if ("message" in response) {
                        message_box.insertAdjacentHTML("afterBegin", response.message);
                    }
                    else {
                        message_box.appendChild(document.createTextNode( en4.core.language.translate('Signup complete!') ));
                    }

                    var wrapper = document.createElement("div");
                    jQuery(wrapper).append([message_title, message_box]);
                    jQuery("#global_content,#global_content_simple").empty().append(wrapper);
                    if (parent.Smoothbox.instance){
                        parent.Smoothbox.instance.doAutoResize();
                    }
                    if (response.redirect === true) {
                        parent.location.href = "/members/edit/profile/";
                    }
                }
                    break;

                case "incorrect": //Некорректно заполнена форма
                {
                    for (var key in response) {
                        if (formExist[key] && key !== "status") {
                            var obj_level2 = response[key];
                            for (var dkey in obj_level2) {
                                if (document.getElementById(key + "_correct").innerHTML !== obj_level2[dkey]) {
                                    document.getElementById(key + "_correct").innerHTML = obj_level2[dkey];
                                }

                                if (key === "recaptcha_response_field" && "Recaptcha" in window) {
                                    Recaptcha.reload();
                                }
                                break;
                            }
                        }
                    }
                }
                    break;
                case "fail": /*Ошибка при записи в базу данных*/
                {
                    NOTIFICATION.error(CONST_TITLE.REGISTRATION_ERROR);
                }
                    break;

                default: /*Ошибка на стороне сервера*/
                {
                    NOTIFICATION.error(CONST_TITLE.SERVER_ERROR);
                }
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error(jqXHR, textStatus, errorThrown);
            NOTIFICATION.error(CONST_TITLE.REGISTRATION_ERROR);
        },
        complete: function () {
            window.ajaxSended = false;
            doneButton.removeAttr("style").empty().text("Далее");
        }
    });

    return false;
};


function userNamesTextchange(current) {
    var NAMES_PATTERN = /^([A-z]+[\-]?[A-z]+)|([А-яЁё]+[\-]?[А-яЁё]+)$/;
    if (NAMES_PATTERN.test(current.value)) {
        jQuery("#" + current.id + "_correct").empty();
    }
}

function userNamesChange() {
    var first_name, middle_name, last_name;

    first_name = document.getElementById("first_name").value.trim();
    middle_name = document.getElementById("middle_name").value.trim();
    last_name = document.getElementById("last_name").value.trim();

}
var set_ajax_loader = null;
function emailTextchange(event) {
    var element_id, element_value;

    element_id = event.target.id;
    element_value = event.target.value.trim();

    if (EMAIL_PATTERN.test(element_value)) {
        jQuery.ajax({
            url: "/signup/mailcheck/",
            dataType: "JSON",
            data: {
                email: element_value
            },
            beforeSend: function () {
                clearTimeout(set_ajax_loader);
                event.target.oninput = function () {
                    return;
                };

                set_ajax_loader = setTimeout(function () {
                    jQuery("#" + element_id + "_status").empty().append(getLoaderImg());
                }, ajax_loader_latensy);
            },
            success: function (response) {
                switch (response.status) {
                    case "success":
                    {
                        elementRewrite(document.getElementById(element_id + "_status"), "<b class=\"success\">✓</b>");
                        elementRewrite(document.getElementById(element_id + "_correct"), "");
                        form_status[element_id] = true;
                    }
                        break;

                    case "incorrect": //некорректно заполнено поле
                    {
                        form_status[element_id] = false;
                        var email_status = false;

                        for (var dkey in response) {
                            if (!email_status && dkey !== "status") {
                                elementRewrite(document.getElementById(element_id + "_status"), "<i class='fa fa-remove' style='color:red;font-size:32px;' title='" + response[dkey] + "'></i>");
                                email_status = true;
                                break;
                            }
                        }
                    }
                        break;

                    case "false": //Ошибка на стороне сервера
                    default:
                    {
                        form_status[element_id] = false;
                        elementRewrite(document.getElementById(element_id + "_status"), "<b class='fail'>" + CONST_TITLE.EMAIL_EMPTY + "</b>");
                        elementRewrite(document.getElementById(element_id + "_correct"), "");
                        throw new Error(CONST_TITLE.SERVER_ERROR);
                    }
                        break;
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error(jqXHR, textStatus, errorThrown);

                form_status[element_id] = false;
                elementRewrite(document.getElementById(element_id + "_status"), "<b class='fail'>" + CONST_TITLE.EMAIL_CHECK_ERROR + "</b>");
                jQuery("#" + element_id + "_correct").empty();

                NOTIFICATION.error(CONST_TITLE.ERR_NOTICE);
            },
            complete: function () {
                clearTimeout(set_ajax_loader);
                event.target.oninput = emailTextchange;
            }
        });
    }
    else {
        clearTimeout(set_ajax_loader);
        jQuery("#" + element_id + "_status .bar_loader_small").remove();
        form_status[element_id] = false;
    }
}

function passwordTextchange(current) {
    if (NOT_PASSWORD_PATTERN.test(current.value)) {
        form_status[current.id] = false;

        if (CIRILIC_PATTERN.test(current.value)) {
            elementRewrite(document.getElementById(current.id + "_correct"), CONST_TITLE.CHANGE_LAYOUT_TITLE);
        } else {
            elementRewrite(document.getElementById(current.id + "_correct"), CONST_TITLE.INVALID_CHARACTERS);
        }

    } else {
        jQuery("#" + current.id + "_correct").empty();
        form_status[current.id] = PASSWORD_PATTERN.test(current.value);
        password_length("password", document.getElementById("password_difficulty"), document.getElementById("red_line"), document.getElementById("yellow_line"), document.getElementById("green_line"), 32);

    }

    if (current.value !== "" && document.getElementById("password_confirm").value !== "") {
        form_status.passwords_match = (document.getElementById("password_confirm").value === current.value);

        if (form_status.passwords_match && document.getElementById("password_confirm_status").innerHTML !== "<b class=\"success\">" + CONST_TITLE.PASSWORDS_MATCH + "</b>")
            document.getElementById("password_confirm_status").innerHTML = "<b class=\"success\">" + CONST_TITLE.PASSWORDS_MATCH + "</b>";

        if (!form_status.passwords_match && document.getElementById("password_confirm_status").innerHTML !== "<b class='fail'>" + CONST_TITLE.PASSWORDS_NOT_MATCH + "</b>")
            document.getElementById("password_confirm_status").innerHTML = "<b class='fail'>" + CONST_TITLE.PASSWORDS_NOT_MATCH + "</b>";
    } else {
        form_status.passwords_match = false;
        jQuery("#password_confirm_status").empty();
    }
    if (parent.Smoothbox.instance){
        parent.Smoothbox.instance.doAutoResize();
    }
}

function passwordConfirmTextchange(current) {
    if (NOT_PASSWORD_PATTERN.test(current.value)) {
        form_status[current.id] = false;
        elementRewrite(document.getElementById(current.id + "_correct"), CIRILIC_PATTERN.test(current.value) ? CONST_TITLE.CHANGE_LAYOUT_TITLE : CONST_TITLE.INVALID_CHARACTERS);
    }
    else {
        elementRewrite(document.getElementById(current.id + "_correct"), "");
        form_status[current.id] = PASSWORD_PATTERN.test(current.value);
    }

    if (document.getElementById("password").value !== "" && current.value !== "") {
        form_status.passwords_match = (current.value === document.getElementById("password").value);

        if (form_status.passwords_match && document.getElementById(current.id + "_status").innerHTML !== "<b class=\"success\">" + CONST_TITLE.PASSWORDS_MATCH + "</b>")
            document.getElementById(current.id + "_status").innerHTML = "<b class=\"success\">" + CONST_TITLE.PASSWORDS_MATCH + "</b>";

        if (!form_status.passwords_match && document.getElementById(current.id + "_status").innerHTML !== "<b class='fail'>" + CONST_TITLE.PASSWORDS_NOT_MATCH + "</b>")
            document.getElementById(current.id + "_status").innerHTML = "<b class='fail'>" + CONST_TITLE.PASSWORDS_NOT_MATCH + "</b>";
    }
    else {
        form_status.passwords_match = false;
        elementRewrite(document.getElementById(current.id + "_status"), "");
    }
    if (parent.Smoothbox.instance){
        parent.Smoothbox.instance.doAutoResize();
    }
}


function mobilephoneTextchange(current) {
    if (current.value !== "") {
        form_status[current.id] = MOBILEPHONE_PATTERN.test(current.value);
    } else {
        form_status[current.id] = true;
    }

}

function passwordShowHide(current) {
    if (document.getElementById("password").type === "password") {
        document.getElementById("password").type = "text";
        document.getElementById("password_confirm").type = "text";
        current.innerHTML = CONST_TITLE.HIDE_PASSWORDS_TITLE;
    }
    else {
        document.getElementById("password").type = "password";
        document.getElementById("password_confirm").type = "password";
        current.innerHTML = CONST_TITLE.SHOW_PASSWORDS_TITLE;
    }
}

function termsChange(current) {
    form_status[current.id] = current.checked;
    elementRewrite(document.getElementById(current.id + "_correct"), "");
}

function toggleLicenseAgreement() {
    var element_agreement = document.getElementById("license_agreement");
    if (element_agreement === null) {
        return;
    }
    jQuery(element_agreement).slideToggle(2000);
}

function recaptchaTextchange(current) {
    form_status[current.id] = !CIRILIC_PATTERN.test(current.value) && current.value !== "";

    elementRewrite(document.getElementById("recaptcha_response_field_correct"), CIRILIC_PATTERN.test(current.value) ? CONST_TITLE.CHANGE_LAYOUT_TITLE : "");
}

function recaptchaReload() {
    elementRewrite(document.getElementById("recaptcha_response_field_correct"), "");
    if("Recaptcha" in window) {
        Recaptcha.reload();
    }

    form_status.recaptcha_response_field_correct = false;
}

function passwordDifficultyBlockShow() {
    var triangle_tooltip, parentElem;
    if (jQuery('.profile-message').length){
        NOTIFICATION_TOOLTIP.hide();
        return;
    }
    NOTIFICATION_TOOLTIP.autoremoveDuration = 5000;
    NOTIFICATION_TOOLTIP.tooltipBottom(jQuery('#password_difficulty_content').html(), document.getElementById("password_difficulty"));
    setTimeout(function() {
        var blurNotify = function () {
            NOTIFICATION_TOOLTIP.hide();
            jQuery(document.documentElement).off('click', blurNotify);
        };
        jQuery(document.documentElement).click(blurNotify);
    }, 500);
}

function passwordDifficultyBlockHide() {
    /* DEPRECATED */
}

function signupKeyboadOpen() {
    virtual_keyboard_open();
    if (document.activeElement.tagName === "INPUT") {
        document.activeElement.focus();
    } else {
        document.querySelector(".global_form input").focus();
    }
}