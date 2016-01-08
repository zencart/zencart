<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  New in v1.5.4 $
 */
?>
<script type="text/javascript"><!--//<![CDATA[
if (typeof zcJS == "undefined" || !zcJS) {
  window.zcJS = { name: 'zcJS', version: '0.1.0.0' };
};

zcJS.ajax = function (options) {
  options.url = options.url.replace("&amp;", "&");
  var deferred = $.Deferred(function (d) {
      var securityToken = '<?php echo $_SESSION['securityToken']; ?>';
      var defaults = {
          cache: false,
          type: 'POST',
          traditional: true,
          dataType: 'json',
          timeout: 5000,
          data: $.extend(true,{
            securityToken: securityToken
        }, options.data)
      },
      settings = $.extend(true, {}, defaults, options);

      d.done(settings.success);
      d.fail(settings.error);
      d.done(settings.complete);
      var jqXHRSettings = $.extend(true, {}, settings, {
          success: function (response, textStatus, jqXHR) {
            d.resolve(response, textStatus, jqXHR);
          },
          error: function (jqXHR, textStatus, errorThrown) {
              console.log(jqXHR);
              d.reject(jqXHR, textStatus, errorThrown);
          },
          complete: d.resolve
      });
      $.ajax(jqXHRSettings);
   }).fail(function(jqXHR, textStatus, errorThrown) {
   var response = jqXHR.getResponseHeader('status');
   var responseHtml = jqXHR.responseText;
   var contentType = jqXHR.getResponseHeader("content-type");
   switch (response)
     {
       case '403 Forbidden':
         var jsonResponse = JSON.parse(jqXHR.responseText);
         var errorType = jsonResponse.errorType;
         switch (errorType)
         {
           case 'ADMIN_BLOCK_WARNING':
           break;
           case 'AUTH_ERROR':
           break;
           case 'SECURITY_TOKEN':
           break;

           default:
             alert('An Internal Error of type '+errorType+' was received while processing an ajax call. The action you requested could not be completed.');
         }
       break;
       default:
        if (jqXHR.status === 200 && contentType.toLowerCase().indexOf("text/html") >= 0) {
         document.open();
         document.write(responseHtml);
         document.close();
         } else {
           alert('An unknown response '+response+': :'+contentType+': :'+errorThrown+' was received while processing an ajax call. The action you requested could not be completed.');
         }
     }
   });

  var promise = deferred.promise();
  return promise;
};
zcJS.timer = function (options) {
  var defaults = {
    interval: 10000,
    startEvent: null,
    intervalEvent: null,
    stopEvent: null

},
  settings = $.extend(true, {}, defaults, options);

  var enabled = new Boolean(false);
  var timerId = 0;
  var mySelf;
  this.Start = function()
  {
      this.enabled = new Boolean(true);

      mySelf = this;
      mySelf.settings = settings;
      if (mySelf.enabled)
      {
          mySelf.timerId = setInterval(
          function()
          {
              if (mySelf.settings.intervalEvent)
              {
                mySelf.settings.intervalEvent(mySelf);
              }
          }, mySelf.settings.interval);
          if (mySelf.settings.startEvent)
          {
            mySelf.settings.startEvent(mySelf);
          }
      }
  };
  this.Stop = function()
  {
    mySelf.enabled = new Boolean(false);
    clearInterval(mySelf.timerId);
    if (mySelf.settings.stopEvent)
    {
      mySelf.settings.stopEvent(mySelf);
    }
  };
};
<?php if (isset($_SESSION['jscript_enabled'])) { ?>
<?php unset($_SESSION['jscript_enabled']); ?>
<?php } ?>
<?php if (PADSS_AJAX_CHECKOUT=='1' && in_array($current_page, array(FILENAME_CHECKOUT_CONFIRMATION,FILENAME_CHECKOUT_PAYMENT,FILENAME_CHECKOUT_SHIPPING,FILENAME_SHOPPING_CART, FILENAME_LOGIN))) { ?>
zcJS.ajax({
    url: "ajax.php?act=ajaxPayment&method=setNoscriptCookie",
    data: {test: '1'},
    async: false
  }).done(function( response ) {

  });
<?php } ?>
//]] --></script>
<?php if (PADSS_AJAX_CHECKOUT=='1' && ($current_page == FILENAME_CHECKOUT_CONFIRMATION || $current_page == FILENAME_CHECKOUT_PAYMENT || $current_page == FILENAME_CHECKOUT_SHIPPING) && !isset($_SESSION['jscript_enabled'])) { ?>
<?php if ($payment_modules->doesCollectsCardDataOnsite == true) { ?>
<noscript>
<meta http-equiv="refresh" content="0; url=<?php echo zen_href_link(FILENAME_SHOPPING_CART, 'jscript=no');?>" />
</noscript>
<?php }?>
<?php }?>
