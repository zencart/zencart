<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2019 Oct 21 Modified in v1.5.7 $
 */
?>
<script>
if (typeof zcJS == "undefined" || !zcJS) {
  window.zcJS = { name: 'zcJS', version: '0.1.0.0' };
}

zcJS.ajax = function (options) {
  options.url = options.url.replace("&amp;", unescape("&amp;"));
  var deferred = jQuery.Deferred(function (d) {
      var securityToken = '<?php echo $_SESSION['securityToken']; ?>';
      var defaults = {
          cache: false,
          type: 'POST',
          traditional: true,
          dataType: 'json',
          timeout: 5000,
          data: jQuery.extend(true,{
            securityToken: securityToken
        }, options.data)
      },
      settings = jQuery.extend(true, {}, defaults, options);
      if (typeof(console.log) == 'function') {
          console.log( settings );
      }

      d.done(settings.success);
      d.fail(settings.error);
      d.done(settings.complete);
      var jqXHRSettings = jQuery.extend(true, {}, settings, {
          success: function (response, textStatus, jqXHR) {
            d.resolve(response, textStatus, jqXHR);
          },
          error: function (jqXHR, textStatus, errorThrown) {
              if (window.console) {
                if (typeof(console.log) == 'function') {
                  console.log(jqXHR);
                }
              }
              d.reject(jqXHR, textStatus, errorThrown);
          },
          complete: d.resolve
      });
      jQuery.ajax(jqXHRSettings);
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
        if (jqXHR.status === 200) {
            if (contentType.toLowerCase().indexOf("text/html") >= 0) {
                document.open();
                document.write(responseHtml);
                document.close();
            }
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
  settings = jQuery.extend(true, {}, defaults, options);

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

</script>
