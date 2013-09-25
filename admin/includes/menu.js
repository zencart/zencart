/** NDE CSS/JS Menu Widget **/

// combined config.js and cssjsmenu.js

// config.js


checkForUpgrade();

  function hide_dropdowns(what){
    if (window.navigator.userAgent.indexOf('MSIE 6.0') != -1)
    if (what=="in") {
      var anchors = document.getElementsByTagName("select");
      for (var i=0; i<anchors.length; i++) {
        var anchor = anchors[i];
        if (anchor.getAttribute("rel")=="dropdown") {
          anchor.style.position="relative";
          anchor.style.top="0px";
          anchor.style.left="-2000px";
        }
      }
    } else {
      var anchors = document.getElementsByTagName("select");
      for (var i=0; i<anchors.length; i++) {
        var anchor = anchors[i];
        if (anchor.getAttribute("rel")=="dropdown") {
          anchor.style.position="relative";
          anchor.style.top="0px";
          anchor.style.left="0px";
        }
      }
    }
  }

function ndeSetStyleSheet(newtitle)
{
  ndeCreateCookie('nde-style', newtitle, 365, false);
  if (ndeReadCookie('nde-style') == newtitle)
  {
    window.location.reload(true);
  }
  else
  {
    alert('You must enable Cookies in order for theme selection to work');
  }
}

function ndeSetTextSize(chgsize,rs)
{
  if (!document.documentElement || !document.body)
  {
    return;
  }

  var newSize;
  var startSize = parseInt(ndeGetDocTextSize());

  if (!startSize)
  {
    startSize = 12;
  }

  switch (chgsize)
  {
  case 'incr':
    newSize = startSize + 2;
    break;

  case 'decr':
    newSize = startSize - 2;
    break;

  case 'reset':
    if (rs)
    {
      newSize = rs;
    }
    else
    {
      newSize = 12;
    }
    break;

  default:
    newSize = parseInt(ndeReadCookie('nde-textsize', true));
    if (!newSize)
    {
      newSize = startSize;
    }
    break;

  }

  if (newSize < 10)
  {
    newSize = 10;
  }

  newSize += 'px';

  document.documentElement.style.fontSize = newSize;
  document.body.style.fontSize = newSize;

  ndeCreateCookie('nde-textsize', newSize, 365, true);
}

function ndeGetDocTextSize()
{
  if (!document.body)
  {
    return 0;
  }

  var size = 0;
  var body = document.body;

  if (body.style && body.style.fontSize)
  {
    size = body.style.fontSize;
  }
  else if (typeof(getComputedStyle) != 'undefined')
  {
    size = getComputedStyle(body,'').getPropertyValue('font-size');
  }
  else if (body.currentStyle)
  {
    size = body.currentStyle.fontSize;
  }
  return size;
}

function ndeCreateCookie(name,value,days,useLang)
{
  var langString = useLang ? ndeGetLang() : '';

  var cookie = name + langString + '=' + value + ';';

  if (days)
  {
    var date = new Date();
    var ndeMilliSecondsInDay = 86400000; // 24*60*60*1000
    date.setTime(date.getTime()+(days*ndeMilliSecondsInDay));
    cookie += ' expires=' + date.toGMTString() + ';';
  }
  cookie += ' path=/';

  document.cookie = cookie;
}

function ndeReadCookie(name, useLang)
{
  var langString = useLang ? ndeGetLang() : '';

  var nameEQ = name + langString + '=';
  var ca = document.cookie.split(';');

  for(var i = 0; i < ca.length; i++)
  {
    var c = ca[i];
    while (c.charAt(0) == ' ')
    {
      c = c.substring(1, c.length);
    }

    if (c.indexOf(nameEQ) == 0)
    {
      return c.substring(nameEQ.length,c.length);
    }
  }
  return null;
}

function ndeSetTheme()
{
  ndeSetTextSize();
  return true;
}

function ndeGetLang()
{
  var langString = '';

  if (document.documentElement){
    langString = document.documentElement.lang;
    if (langString != ''){
      langString = '-' + langString;
    }
  }
  return langString;
}

function checkForUpgrade()
{
  var rvValue = -1;

  if (navigator.product == 'Gecko')
  {
    rvValue = 0;
    var ua      = navigator.userAgent.toLowerCase();
    var rvStart = ua.indexOf('rv:');
    var rvEnd   = ua.indexOf(')', rvStart);
    var rv      = ua.substring(rvStart+3, rvEnd);
    var rvParts = rv.split('.');
    var exp     = 1;

    for (var i = 0; i < rvParts.length; i++)
    {
      var val = parseInt(rvParts[i]);
      rvValue += val / exp;
      exp *= 100;
    }
  }

  if (!document.getElementById || ( rvValue >= 0 && rvValue < 1.0))
  {
    var updateMessageShown = ndeReadCookie('upgrade');
    if (!updateMessageShown)
    {
      ndeCreateCookie('upgrade','1', 90);
      // check if cookie written. If not, don't redirect
      if (ndeReadCookie('upgrade'))
      {
        document.location = '/upgrade.html';
      }
    }
  }
}

function printAlert()
{
  alert('Thanks to the use of a print-media stylesheet, this page is already printer-friendly!  Just print the article from a CSS-capable browser to get the print styles on paper.');
}

function cssjsmenuinit()
{
  cssjsmenu('navbar');
  if (document.getElementById)
  {
    var kill = document.getElementById('hoverJS');
    kill.disabled = true;
  }
}

// csjsmenu.js

function elementContains(elmOuter, elmInner)
{
  while (elmInner && elmInner != elmOuter)
  {
    elmInner = elmInner.parentNode;
  }
  if (elmInner == elmOuter)
  {
    return true;
  }
  return false;
}

function getPageXY(elm)
{
  var point = { x: 0, y: 0 };
  while (elm)
  {
    point.x += elm.offsetLeft;
    point.y += elm.offsetTop;
    elm = elm.offsetParent;
  }
  return point;
}

function setPageXY(elm, x, y)
{
  var parentXY = {x: 0, y: 0 };

  if (elm.offsetParent)
  {
    parentXY = getPageXY(elm.offsetParent);
  }

  elm.style.left = (x - parentXY.x) + 'px';
  elm.style.top  = (y - parentXY.y) + 'px';
}

/* ------------------------------------------------------------ */
/* file boundary */

function cssjsmenu(menuid)
{
  var i;
  var j;
  var node;
  var child;
  var parent;

  // if the browser doesn't even support
  // document.getElementById, give up now.
  if (!document.getElementById)
  {
    return true;
  }

  // check for downlevel browsers
  // Opera 6, IE 5/Mac are not supported

  var version = '';
  var offset;

  offset = navigator.userAgent.indexOf('Opera');
  if (offset != -1)
  {
    version = parseInt('0' + navigator.userAgent.substr(offset + 6), 10);
    if (version < 7)
    {
      return true;
    }
    offset = navigator.userAgent.indexOf('Version/');
    version = parseInt('0' + navigator.userAgent.substr(offset + 8), 10);
    if (version >= 12 && navigator.userAgent.indexOf('Windows') != -1) version = 'Opera12win';
  }

  offset = navigator.userAgent.indexOf('MSIE');
  if (offset != -1)
  {
    if (navigator.userAgent.indexOf('Mac') != -1)
    {
      return true;
    }
  }

  var menudiv = document.getElementById(menuid);

  // ul
  var ul = new Array();

  for (i = 0; i < menudiv.childNodes.length; i++)
  {
    node = menudiv.childNodes[i];
    if (node.nodeName.toUpperCase() == 'UL')
    {
      ul[ul.length] = node;
    }
  }

  // ul > li
  var ul_gt_li = new Array();

  for (i = 0; i < ul.length; i++)
  {
    node = ul[i];
    for (j = 0; j < node.childNodes.length; j++)
    {
      child = node.childNodes[j];
      if (child.nodeName.toUpperCase() == 'LI')
      {
        ul_gt_li[ul_gt_li.length] = child;
        child.style.display = 'inline';
        child.style.listStyle = 'none';
        if (version != 'Opera12win') child.style.position = 'static';
      }
    }
  }

  // ul > li > ul
  var ul_gt_li_gt_ul = new Array();

  for (i = 0; i < ul_gt_li.length; i++)
  {
    node = ul_gt_li[i];
    for (j = 0; j < node.childNodes.length; j++)
    {
      child = node.childNodes[j];
      if (child.nodeName.toUpperCase() == 'UL')
      {
        ul_gt_li_gt_ul[ul_gt_li_gt_ul.length] = child;
        child.style.position = 'absolute';
        if (version != 'Opera12win') child.style.left = '-13em';
        child.style.visibility = 'hidden';

        // attach hover to parent li
        parent = child.parentNode;
        parent.onmouseover = function (e)
        {
          var i;
          var child;
          var point;

          // stop the pure css hover effect
          this.style.paddingBottom = '0';

          for (i = 0; i < this.childNodes.length; i++)
          {
            child = this.childNodes[i];
            if (child.nodeName.toUpperCase() == 'UL')
            {
              point = getPageXY(this);
              if (version != 'Opera12win') setPageXY(child, point.x, point.y + this.offsetHeight);
              child.style.visibility = 'visible';
            }
          }
          return false;
        };
        parent.onmouseout = function (e)
        {
          var relatedTarget = null;
          if (e)
          {
            relatedTarget = e.relatedTarget;
            // work around Gecko Linux only bug where related target is null
            // when clicking on menu links or when right clicking and moving
            // into a context menu.
            if (navigator.product == 'Gecko' && navigator.platform.indexOf('Linux') != -1 && !relatedTarget)
            {
              relatedTarget = e.originalTarget;
            }
          }
          else if (window.event)
          {
            relatedTarget = window.event.toElement;
          }

          if (elementContains(this, relatedTarget))
          {
            return false;
          }

          var i;
          var child;
          for (i = 0; i < this.childNodes.length; i++)
          {
            child = this.childNodes[i];
            if (child.nodeName.toUpperCase() == 'UL')
            {
                child.style.visibility = 'hidden';
            }
          }
          return false;
        };
      }
    }
  }
  return true;
}

