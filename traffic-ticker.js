function delayedRefresh()
{
  setTrafficFrameURL();
  setInterval(function() {window.location.reload();}, 60000);

}


function setTrafficFrameURL()
{
  var curDate=new Date();
  var curDay=curDate.getDay();
  var weekendCutOver=new Date((curDate.toLocaleDateString()) + " 15:30:00" );
  var weekdayCutOver=new Date((curDate.toLocaleDateString()) + " 20:00:00" );
  var iframeSrc="";
  if (monday and (now < weekdayCutOver)) or (
  if(((curDay==0)&&(curDate<weekdayCutOver))||(curDay==6)||((curDay==5)&&(curDate>=weekendCutOver)))
  {
    iframeSrc="/trafficreport/WeekendTrafficTicker.htm";
  }
  else
  {
    iframeSrc="/trafficreport/WeekdayTrafficTicker.htm";
  }

  var request;
  if(window.XMLHttpRequest)
    request = new XMLHttpRequest();
  else
    request = new ActiveXObject("Microsoft.XMLHTTP");
  request.open('GET', iframeSrc, false);
  request.send();
  var div = document.getElementById('TrafficDiv');

  if (request.status === 404) {

    div.innerHTML = div.innerHTML + '<br/>Traffic Report is currently unavailable.';
    iframeSrc='about:blank';

  } else {
    div.innerHTML = "";

  }
  document.getElementById("TrafficFrame").src=iframeSrc;
}
