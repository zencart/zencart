<?php
/**
 * jscript_main
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All 2019 Jun 03 Modified in v1.5.7 $
 */
?>
<script type="text/javascript">
var i=0;
function resize() {
  i=0;
//  if (navigator.appName == 'Netscape') i=20;
  if (window.navigator.userAgent.indexOf('MSIE 6.0') != -1 && window.navigator.userAgent.indexOf('SV1') != -1) {
      i=30; //This browser is Internet Explorer 6.x on Windows XP SP2
  } else if (window.navigator.userAgent.indexOf('MSIE 6.0') != -1) {
      i=0; //This browser is Internet Explorer 6.x
  } else if (window.navigator.userAgent.indexOf('Firefox') != -1 && window.navigator.userAgent.indexOf("Windows") != -1) {
      i=25; //This browser is Firefox on Windows
  } else if (window.navigator.userAgent.indexOf('Mozilla') != -1 && window.navigator.userAgent.indexOf("Windows") != -1) {
      i=45; //This browser is Mozilla on Windows
  } else {
      i=80; //This is all other browsers including Mozilla on Linux
  }
  if (document.documentElement && document.documentElement.clientWidth) {
//    frameWidth = document.documentElement.clientWidth;
//    frameHeight = document.documentElement.clientHeight;

  imgHeight = document.images[0].height+40-i;
  imgWidth = document.images[0].width+20;

  var height = screen.height;
  var width = screen.width;
  var leftpos = width / 2 - imgWidth / 2;
  var toppos = height / 2 - imgHeight / 2;

    frameWidth = imgWidth;
    frameHeight = imgHeight+i;

  window.moveTo(leftpos, toppos);


//  window.resizeTo(imgWidth, imgHeight);
  window.resizeTo(frameWidth,frameHeight+i);
	}
  else if (document.body) {
    window.resizeTo(document.body.clientWidth, document.body.clientHeight-i);
  }
  self.focus();
}
</script>
