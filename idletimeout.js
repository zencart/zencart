/*
 * jQuery idleTimer plugin
 * version 0.8.092209
 * by Paul Irish. 
 *   http://github.com/paulirish/yui-misc/tree/
 * MIT license
 
 * adapted from YUI idle timer by nzakas:
 *   http://github.com/nzakas/yui-misc/
 
 
 * Copyright (c) 2009 Nicholas C. Zakas
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

(function ($) {
    $.idleTimer = function f(newTimeout) {

        //$.idleTimer.tId = -1     //timeout ID

        var idle = false,        //indicates if the user is idle
            enabled = true,        //indicates if the idle timer is enabled
            timeout = 30000,        //the amount of time (ms) before the user is considered idle
            events = 'mousemove keydown DOMMouseScroll mousewheel mousedown', // activity is one of these events
          //f.olddate = undefined, // olddate used for getElapsedTime. stored on the function

        /* (intentionally not documented)
         * Toggles the idle state and fires an appropriate event.
         * @return {void}
         */
        toggleIdleState = function () {

            //toggle the state
            idle = !idle;

            // reset timeout counter
            f.olddate = +new Date;

            //fire appropriate event
            $(document).trigger($.data(document, 'idleTimer', idle ? "idle" : "active") + '.idleTimer');
        },

        /**
         * Stops the idle timer. This removes appropriate event handlers
         * and cancels any pending timeouts.
         * @return {void}
         * @method stop
         * @static
         */
        stop = function () {

            //set to disabled
            enabled = false;

            //clear any pending timeouts
            clearTimeout($.idleTimer.tId);

            //detach the event handlers
            $(document).unbind('.idleTimer');
        },


        /* (intentionally not documented)
         * Handles a user event indicating that the user isn't idle.
         * @param {Event} event A DOM2-normalized event object.
         * @return {void}
         */
        handleUserEvent = function () {

            //clear any existing timeout
            clearTimeout($.idleTimer.tId);



            //if the idle timer is enabled
            if (enabled) {


                //if it's idle, that means the user is no longer idle
                if (idle) {
                    toggleIdleState();
                }

                //set a new timeout
                $.idleTimer.tId = setTimeout(toggleIdleState, timeout);

            }
        };


        /**
         * Starts the idle timer. This adds appropriate event handlers
         * and starts the first timeout.
         * @param {int} newTimeout (Optional) A new value for the timeout period in ms.
         * @return {void}
         * @method $.idleTimer
         * @static
         */


        f.olddate = f.olddate || +new Date;

        //assign a new timeout if necessary
        if (typeof newTimeout == "number") {
            timeout = newTimeout;
        } else if (newTimeout === 'destroy') {
            stop();
            return this;
        } else if (newTimeout === 'getElapsedTime') {
            return (+new Date) - f.olddate;
        }

        //assign appropriate event handlers
        $(document).bind($.trim((events + ' ').split(' ').join('.idleTimer ')), handleUserEvent);


        //set a timeout to toggle state
        $.idleTimer.tId = setTimeout(toggleIdleState, timeout);

        // assume the user is active for the first x seconds.
        $.data(document, 'idleTimer', "active");




    }; // end of $.idleTimer()
})(jQuery);



/*
 * jQuery Idle Timeout 1.2
 * Copyright (c) 2011 Eric Hynds
 *
 * http://www.erichynds.com/jquery/a-new-and-improved-jquery-idle-timeout-plugin/
 *
 * Depends:
 *  - jQuery 1.4.2+
 *  - jQuery Idle Timer (by Paul Irish, http://paulirish.com/2009/jquery-idletimer-plugin/)
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
*/

(function ($, win) {

    var idleTimeout = {
        init: function (element, resume, options) {
            var self = this, elem;

            this.warning = elem = $(element);
            this.resume = $(resume);
            this.options = options;
            this.countdownOpen = false;
            this.failedRequests = options.failedRequests;
            if (this.options.pollingInterval > 0) this._startTimer();
            this.title = document.title;

            // expose obj to data cache so peeps can call internal methods
            $.data(elem[0], 'idletimeout', this);

            // start the idle timer
            $.idleTimer(options.idleAfter * 1000);

            // once the user becomes idle
            $(document).bind("idle.idleTimer", function () {

                // if the user is idle and a countdown isn't already running
                if ($.data(document, 'idleTimer') === 'idle' && !self.countdownOpen) {
                    self._stopTimer();
                    self.countdownOpen = true;
                    self._idle();
                }
            });

            // bind continue link
            this.resume.bind("click", function (e) {
                e.preventDefault();

                win.clearInterval(self.countdown); // stop the countdown
                self.countdownOpen = false; // stop countdown
                self._startTimer(); // start up the timer again
                self._keepAlive(false); // ping server
                options.onResume.call(self.warning); // call the resume callback
            });
        },

        _idle: function () {
            var self = this,
				options = this.options,
				warning = this.warning[0],
				counter = options.warningLength;

            // fire the onIdle function
            options.onIdle.call(warning);

            // set inital value in the countdown placeholder
            options.onCountdown.call(warning, counter);

            // create a timer that runs every second
            this.countdown = win.setInterval(function () {
                if (--counter === 0) {
                    window.clearInterval(self.countdown);
                    options.onTimeout.call(warning);
                } else {
                    options.onCountdown.call(warning, counter);
                    document.title = options.titleMessage.replace('%s', counter) + self.title;
                }
            }, 1000);
        },

        _startTimer: function () {
            var self = this;

            this.timer = win.setTimeout(function () {
                self._keepAlive();
            }, this.options.pollingInterval * 1000);
        },

        _stopTimer: function () {
            // reset the failed requests counter
            this.failedRequests = this.options.failedRequests;
            win.clearTimeout(this.timer);
        },

        _keepAlive: function (recurse) {
            var self = this,
				options = this.options;

            //Reset the title to what it was.
            document.title = self.title;

            // assume a startTimer/keepAlive loop unless told otherwise
            if (typeof recurse === "undefined") {
                recurse = true;
            }

            // if too many requests failed, abort
            if (!this.failedRequests) {
                this._stopTimer();
                options.onAbort.call(this.warning[0]);
                return;
            }

            $.ajax({
                timeout: options.AJAXTimeout,
                url: options.keepAliveURL,
                type: 'POST',
                headers: { "__RequestVerificationToken": $('[name=__RequestVerificationToken]').val() },
                error: function () {
                    self.failedRequests--;
                },
                success: function (response) {
                    if ($.trim(response) !== options.serverResponseEquals) {
                        self.failedRequests--;
                    }
                },
                complete: function () {
                    if (recurse) {
                        self._startTimer();
                    }
                }
            });
        }
    };

    // expose
    $.idleTimeout = function (element, resume, options) {
        idleTimeout.init(element, resume, $.extend($.idleTimeout.options, options));
        return this;
    };

    // options
    $.idleTimeout.options = {
        // number of seconds after user is idle to show the warning
        warningLength: 30,

        // url to call to keep the session alive while the user is active
        keepAliveURL: "",

        // the response from keepAliveURL must equal this text:
        serverResponseEquals: "OK",

        // user is considered idle after this many seconds.  10 minutes default
        idleAfter: 600,

        // a polling request will be sent to the server every X seconds
        pollingInterval: 60,

        // number of failed polling requests until we abort this script
        failedRequests: 5,

        // the $.ajax timeout in MILLISECONDS!
        AJAXTimeout: 250,

        // %s will be replaced by the counter value
        titleMessage: 'Warning: %s seconds until log out | ',

        /*
			Callbacks
			"this" refers to the element found by the first selector passed to $.idleTimeout.
		*/
        // callback to fire when the session times out
        onTimeout: $.noop,

        // fires when the user becomes idle
        onIdle: $.noop,

        // fires during each second of warningLength
        onCountdown: $.noop,

        // fires when the user resumes the session
        onResume: $.noop,

        // callback to fire when the script is aborted due to too many failed requests
        onAbort: $.noop
    };

})(jQuery, window);