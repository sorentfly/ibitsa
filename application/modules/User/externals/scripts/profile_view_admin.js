window.addEventListener("load", function()
{
    var auth_into, user_remove, view_user_id;
    auth_into = document.getElementsByClassName("auth_into").item(0);
    user_remove = document.getElementsByClassName("user_remove").item(0);    
    view_user_id = +document.getElementsByClassName("view_user_id").item(0).value;

    if(auth_into !== null)
    {
        auth_into.onclick = function(e)
        {
            while(e.target.lastChild)
            {
                e.target.removeChild(e.target.lastChild);
            }

            e.target.appendChild(getLoaderImg());
            e.target.style.border = "none";

            var xhr = new XMLHttpRequest();

            xhr.open("POST", "/admin/user/manage/login/", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.setRequestHeader("X-Request", "JSON");
            xhr.setRequestHeader("Accept", "application/json");

            xhr.onreadystatechange = function(event) 
            {
                var json_response;

                if(xhr.readyState !== 4)
                {
                    return;
                }

                if(xhr.statusText !== "OK")
                {
                    console.error(event);
                    alert("Произошла ошибка! " + xhr.status);
                    return;
                }

                json_response = JSON.parse(xhr.responseText);

                if(json_response.status !== true)
                {
                    console.error(event);
                    alert("Произошла ошибка на стороне сервера!");
                    return;
                }

                window.location.replace("/");
            };
            xhr.send("format=json&id=" + view_user_id);
            return false;
        };
    }

    if(user_remove !== null)
    {
        user_remove.onclick = function(e)
        {
            while(e.target.lastChild)
            {
                e.target.removeChild(e.target.lastChild);
            }

            e.target.appendChild(getLoaderImg());
            e.target.style.border = "none";

            var xhr = new XMLHttpRequest();

            xhr.open("POST", "/admin/user/manage/delete/id/" + view_user_id, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.setRequestHeader("X-Request", "JSON");
            xhr.setRequestHeader("Accept", "application/json");

            xhr.onreadystatechange = function(event) 
            {
                if(xhr.readyState !== 4)
                {
                    return;
                }

                if(xhr.statusText !== "OK")
                {
                    console.error(event);
                    alert("Произошла ошибка! " + xhr.status);
                    return;
                }

                while(e.target.lastChild)
                {
                    e.target.removeChild(e.target.lastChild);
                }

                alert("Пользователь удалён!");
                window.location.replace("/user_search/");
            };
            xhr.send(null);
            return false;
        };
    }

}, false);