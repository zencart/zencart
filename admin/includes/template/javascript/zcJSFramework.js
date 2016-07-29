/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Modified in v1.6.0 $
 */

if (typeof zcJS == "undefined" || !zcJS) {
    window.zcJS = {name: 'zcJS', version: '0.1.0.0'};
}
;

zcJS.ajax = function (options) {
    options.url = options.url.replace("&amp;", "&");
    var deferred = $.Deferred(function (d) {
        var defaults = {
                cache: false,
                type: 'POST',
                traditional: true,
                dataType: 'json',
                timeout: 5000,
                data: $.extend(true, {
                    securityToken: securityToken
                }, options.data)
            },
            settings = $.extend(true, {}, defaults, options);
        var jqXHRSettings = $.extend(true, {}, settings, {
            success: function (response, textStatus, jqXHR) {
                d.resolve(response, textStatus, jqXHR);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var jsonResponse = JSON.parse(jqXHR.responseText);
                switch (jqXHR.status) {
                    case 403:
                        var errorType = jsonResponse.errorType;
                        switch (errorType) {
                            case 'ADMIN_BLOCK_WARNING':
                                break;
                            case 'AUTH_ERROR':
                                break;
                            case 'SECURITY_TOKEN':
                                break;
                            case 'CUSTOM_ALERT_ERROR':
                                alert(jsonResponse.errorMessage);
                                break;
                            default:
                                alert('An Internal Error of type ' + errorType + ' was received while processing an ajax call. The action you requested could not be completed.');
                        }
                }
            },
            complete: d.resolve
        });
        $.ajax(jqXHRSettings);
    })

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
    this.Start = function () {
        this.enabled = new Boolean(true);

        mySelf = this;
        mySelf.settings = settings;
        if (mySelf.enabled) {
            mySelf.timerId = setInterval(
                function () {
                    if (mySelf.settings.intervalEvent) {
                        mySelf.settings.intervalEvent(mySelf);
                    }
                }, mySelf.settings.interval);
            if (mySelf.settings.startEvent) {
                mySelf.settings.startEvent(mySelf);
            }
        }
    };
    this.Stop = function () {
        mySelf.enabled = new Boolean(false);
        clearInterval(mySelf.timerId);
        if (mySelf.settings.stopEvent) {
            mySelf.settings.stopEvent(mySelf);
        }
    };
};
