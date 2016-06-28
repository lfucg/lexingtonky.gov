//display functions
function showPageTimeStamps()
{
  showUpdateTime();
  showRetrieveTime();
}
function showUpdateTime()
{
  var modDate=new Date(document.lastModified);
  var hours=modDate.getHours();
  if(navigator.appVersion.indexOf('Chrome')!=-1)
  {
    hours=hours-4;
  }
  if(navigator.appVersion.indexOf('Safari')!=-1)
  {
    hours=hours-4;
  }
  var minutes=modDate.getMinutes();
  var seconds=modDate.getSeconds();
  var month=modDate.getMonth();
  var day=modDate.getDate();
  var year=modDate.getFullYear();
  modDate=new Date(year, month, day, hours, minutes, seconds, 0);
  outputDateTime('---  Last Updated: ' + formatDateTime(modDate.toLocaleString()) + '  ');
}
function showRetrieveTime()
{
  var refDate=new Date();
  outputDateTime('---  Page Refreshed: ' + formatDateTime(refDate.toLocaleString()) + '  ');
}
function outputDateTime(message)
{
  var outputElmt=document.createElement('DIV');
  var outputTxt=document.createTextNode(message);
  outputElmt.style.fontStyle="italic";
  outputElmt.appendChild(outputTxt);
  document.body.appendChild(outputElmt);
}
function formatDateTime(dateStamp)
{
  var stamp=new Date(dateStamp);
  var hours=stamp.getHours();
  var minutes=stamp.getMinutes();
  var seconds=stamp.getSeconds();
  var month=stamp.getMonth();
  var day=stamp.getDate();
  var year=stamp.getFullYear();
  var retStamp="";
  var suffix="am";
  retStamp=checkNought(month+1) + '/' + checkNought(day) + '/' + year + ' at ';
  if(hours>11)
  {
    suffix="pm";
  }
  if(hours>12)
  {
    hours+=-12;
  }
  retStamp+=(checkNought(hours) + ':' + checkNought(minutes) + ':' + checkNought(seconds) + ' ' + suffix);
  return retStamp;
}
function checkNought(i)
{
  if (i<10)
  {
    i="0" + i;
  }
  return i;
}
