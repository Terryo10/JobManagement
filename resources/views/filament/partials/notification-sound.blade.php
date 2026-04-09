@auth
<script>
(function () {
    'use strict';

    /**
     * Plays a soft two-tone ping using the Web Audio API — no audio file required.
     * The browser may block autoplay until the user first interacts with the page,
     * which is fine; subsequent notifications will play normally.
     */
    function playNotificationSound() {
        try {
            var AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;

            var ctx  = new AudioCtx();
            var gain = ctx.createGain();
            gain.connect(ctx.destination);

            function tone(freq, startTime, duration) {
                var osc = ctx.createOscillator();
                osc.connect(gain);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, startTime);
                gain.gain.setValueAtTime(0.18, startTime);
                gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
                osc.start(startTime);
                osc.stop(startTime + duration);
            }

            var now = ctx.currentTime;
            tone(880, now,        0.18);
            tone(660, now + 0.12, 0.28);
        } catch (e) {
            // Silently ignore — sound is a nice-to-have
        }
    }

    /**
     * Once window.Echo is ready (loaded via echo.js Vite entry), subscribe to
     * the authenticated user's private notification channel.
     * On every broadcast notification event: play the ping sound and dispatch
     * a browser event so Livewire/Alpine components can react if needed.
     */
    function subscribeToNotifications() {
        if (!window.Echo) {
            setTimeout(subscribeToNotifications, 400);
            return;
        }

        var userId = {{ auth()->id() }};

        window.Echo
            .private('App.Models.User.' + userId)
            .notification(function (notification) {
                playNotificationSound();
                // Dispatch a named browser event — useful for any Alpine/Livewire
                // component that wants to react (e.g. show a custom toast)
                document.dispatchEvent(
                    new CustomEvent('hm:notification-received', { detail: notification })
                );
            });
    }

    // Wait for the DOM so Livewire/Alpine are fully booted before we run
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', subscribeToNotifications);
    } else {
        subscribeToNotifications();
    }
})();
</script>
@endauth
