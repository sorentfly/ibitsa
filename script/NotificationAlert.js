var nowHour=0;
var nowMinute=0;
var timeNow =0;

function nAlert(timeNotHour,timeNotMinute)
{
    var timerId = setInterval(function() {
        timeNow=new Date();
        nowHour=timeNow.getHours();
        nowMinute=timeNow.getMinutes();
        if(timeNotHour==nowHour && timeNotMinute==nowMinute)
        {
            alert('yes');
        }
        else
        {
            alert('no');
        }
    }, 60000);
    //alert(timeNowHour);
    //alert(timeNowMinute);
    //alert(timeNotHour);
    //alert(timeNotMinute);
}