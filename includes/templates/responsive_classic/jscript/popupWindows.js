/** used by product-reviews-write */
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
}

function popupWindowCheckout(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=320,screenX=150,screenY=150,top=150,left=150');
}

function couponpopupWindow(url) {
  window.open(url,'couponpopupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=320,screenX=150,screenY=150,top=150,left=150');
}

function popupWindowAdvSearch(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}

function popupWindowShoppingCart(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=550,height=550,screenX=150,screenY=100,top=100,left=150')
}

function popupWindowPrice(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=400,screenX=150,screenY=150,top=150,left=150')
}

function popupInfoShoppingCart() {
  window.open('index.php?main_page=info_shopping_cart',"info_shopping_cart","height=460,width=430,toolbar=no,statusbar=no,scrollbars=yes").focus();
}

// window resizing
function resizeSearchHelpPopup() {
  var i=0;
  if (navigator.appName == 'Netscape') i=40;
  if (document.images[0]) window.resizeTo(document.images[0].width +30, document.images[0].height+60-i);
  self.focus();
}

// window resizing
function resizeCouponPopup() {
  var i=0;
  if (navigator.appName == 'Netscape') i=10;
  if (document.images[0]) {
    imgHeight = document.images[0].height+45-i;
    imgWidth = document.images[0].width+30;
    var height = screen.height;
    var width = screen.width;
    var leftpos = width / 2 - imgWidth / 2;
    var toppos = height / 2 - imgHeight / 2;
    window.moveTo(leftpos, toppos);
    window.resizeTo(imgWidth, imgHeight);
  }
  self.focus();
}
