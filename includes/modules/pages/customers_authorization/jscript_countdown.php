<?php
/**
 * Customer Authorization 
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */
?>
<script id="auth-countdown" defer>
window.onload = function() {
    function startCountdown(durationInSeconds, displayElementId)
    {
        let timer = durationInSeconds;
        const display = document.getElementById(displayElementId);
        if (!display) {
            return;
        }

        const countdownInterval = setInterval(function () {
            const minutes = Math.floor(timer / 60);
            const seconds = timer % 60;

            // Format minutes and seconds to always have two digits
            const formattedMinutes = String(minutes).padStart(2, '0');
            const formattedSeconds = String(seconds).padStart(2, '0');

            display.textContent = `${formattedMinutes}:${formattedSeconds}`;

            if (--timer < 0) {
                clearInterval(countdownInterval);
                display.textContent = "<?= TEXT_EXPIRED ?>";
            }
        }, 1000); // Update every 1 second
    }

    // Start the 60-minute (3600 seconds) countdown
    startCountdown(<?= $auth_token_time_remaining ?? 0 ?>, 'countdown');
};
</script>
