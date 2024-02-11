/*
 * Left here TEMPORARILY for legacy pages that do not use the new admin_html_head.php file; the
 * file will be removed in Zen Cart 2.1.0!
 */
function cssjsmenu() {
  viewport = document.querySelector("meta[name=viewport]");
  if (viewport != undefined) {
    viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1');
  } else {
    var metaTag=document.createElement('meta');
  metaTag.name = "viewport"
    metaTag.content = "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1"
    document.getElementsByTagName('head')[0].appendChild(metaTag);
  }
}
function init() {}
