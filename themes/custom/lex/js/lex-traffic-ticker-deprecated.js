(function() {
  var $ = jQuery;
  function delayedRefresh()
  {
    setTrafficFrameURL();
    setInterval(setTrafficFrameURL, 60000);
  }


  function setTrafficFrameURL()
  {
    var curDate=new Date();
    var curDay=curDate.getDay();
    var weekendCutOver=new Date((curDate.toLocaleDateString()) + " 15:30:00" );
    var weekdayCutOver=new Date((curDate.toLocaleDateString()) + " 20:00:00" );
    var iframeSrc="";
    if(((curDay==0)&&(curDate<weekdayCutOver))||(curDay==6)||((curDay==5)&&(curDate>=weekendCutOver)))
    {
      iframeSrc="https://lexington-geocode-proxy.herokuapp.com/www.lexingtonky.gov/trafficreport/WeekendTrafficTicker.htm";
    }
    else
    {
      iframeSrc="https://lexington-geocode-proxy.herokuapp.com/www.lexingtonky.gov/trafficreport/WeekdayTrafficTicker.htm";
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

    $.get(iframeSrc, function(page) {
      page = page.replace('scripts/DateStampFunctions.js',
        '/themes/custom/lex/js/lex-traffic-ticker-deprecated-date-functions.js');
      console.log(page.match('DateStampFunctions'));
      $(document.getElementById("TrafficFrame")).contents().find('html').html(page);
    });
  }
  delayedRefresh();
}());

