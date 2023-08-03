/* this is only for backward compatibility - will be removed in future version */
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
