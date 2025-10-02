<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2019 Oct 21 Modified in v1.5.7 $
 */
?>
<script title="zcJS.ajax">
if (typeof zcJS == "undefined" || !zcJS) {
    window.zcJS = { name: 'zcJS', version: '0.1.1.1' };
}

zcJS.ajax = function (options) {
    options.url = options.url.replace("&amp;", unescape("&amp;"));
<?php
    // -----
    // The 'options.data' supplied by the caller can be:
    //
    // - empty/undefined. In this case, it's set to an empty object. The security
    //   token is added via jQuery.extend.
    //
?>
    if (typeof options.data === 'undefined') {
        options.data = {};
<?php
    // -----
    // - A string, presumed to be a URL-encoded string created by the javascript
    //   serialize function. In this case, the securityToken parameter is appended.
    //
?>
    } else if (typeof options.data === 'string') {
        options.data += '&securityToken=<?= $_SESSION['securityToken'] ?>';
<?php
    // -----
    // - An array, possibly created via the javascript serializeArray function on a
    //   form's variables. If the array is found to be of that form, the name/value array
    //   is converted into its object format. The security token is added via a jQuery.extend.
    //
    // - Otherwise, the input is presumed to be an object, to which the security
    //   token is added via a jQuery.extend.
    //
?>
    } else if (Array.isArray(options.data) && options.data.length !== 0) {
        const firstElement = options.data[0];
        if (typeof firstElement === 'object' && firstElement !== null && 'name' in firstElement && 'value' in firstElement) {
            const obj = {};
            for (let i = 0; i < options.data.length; i++) {
                const item = options.data[i];
                if (obj[item.name] !== undefined) {
                    if (!Array.isArray(obj[item.name])) {
                        obj[item.name] = [obj[item.name]];
                    }
                    obj[item.name].push(item.value);
                } else {
                    obj[item.name] = item.value;
                }
            }
            options.data = obj;
        }
    }
    var deferred = jQuery.Deferred(function (d) {
        var defaults = {
            cache: false,
            type: 'POST',
            traditional: true,
            dataType: 'json',
            timeout: 5000,
            data: jQuery.extend(true, {}, options.data, {securityToken: '<?= $_SESSION['securityToken'] ?>'}),
        },
        settings = jQuery.extend(true, {}, defaults, options);
        if (typeof(console.log) == 'function') {
            console.log(JSON.stringify(settings));
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
        var contentType = jqXHR.getResponseHeader('content-type');
        switch (response) {
            case '403 Forbidden':
                var jsonResponse = JSON.parse(jqXHR.responseText);
                var errorType = jsonResponse.errorType;
                switch (errorType) {
                    case 'ADMIN_BLOCK_WARNING':
                        break;
                    case 'AUTH_ERROR':
                        break;
                    case 'SECURITY_TOKEN':
                        break;
                    default:
                        alert('An Internal Error of type '+errorType+' was received while processing an ajax call. The action you requested could not be completed.');
                        break;
                }
                break;
            default:
                if (jqXHR.status === 200) {
                    if (contentType.toLowerCase().indexOf("text/html") >= 0) {
                        document.open();
                        document.write(responseHtml);
                        document.close();
                    }
                    break;
                } else if (jqXHR.status === 418) {
                    window.location.href = '<?= zen_href_link((IS_ADMIN_FLAG === true) ? FILENAME_DENIED : FILENAME_TIME_OUT, '', 'SSL') ?>';
                }
                break;
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
    this.Start = function() {
        this.enabled = new Boolean(true);

        mySelf = this;
        mySelf.settings = settings;
        if (mySelf.enabled) {
            mySelf.timerId = setInterval(
                function() {
                    if (mySelf.settings.intervalEvent) {
                        mySelf.settings.intervalEvent(mySelf);
                    }
                },
                mySelf.settings.interval
            );
            if (mySelf.settings.startEvent) {
                mySelf.settings.startEvent(mySelf);
            }
        }
    };
    this.Stop = function() {
        mySelf.enabled = new Boolean(false);
        clearInterval(mySelf.timerId);
        if (mySelf.settings.stopEvent) {
            mySelf.settings.stopEvent(mySelf);
        }
    };
};
</script>
