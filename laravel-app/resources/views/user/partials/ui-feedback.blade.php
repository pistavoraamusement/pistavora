{{--
    resources/views/partials/ui-feedback.blade.php

    Drop this in near the end of <body> on any page (admin or customer) to get:
      1. Auto-dismissing flash/alert messages (fades out after a few seconds)
      2. A top loading bar that appears the instant a form is submitted or a
         link is clicked, so users know something is happening while the
         next page loads.

    Usage:
      @include('partials.ui-feedback')

    Works with:
      - .u-alert / .alert  (success/error boxes with visible text)
      - Any element you tag with class="js-flash" (for custom admin toasts)
--}}
<style>
    #reksLoadBar {
        position: fixed;
        top: 0;
        left: 0;
        height: 3px;
        width: 0%;
        background: linear-gradient(90deg, #FF5C85, #D63E63);
        z-index: 99999;
        transition: width 0.25s ease, opacity 0.3s ease;
        opacity: 0;
        pointer-events: none;
    }
    #reksLoadBar.active {
        opacity: 1;
        width: 85%;
    }

    .u-alert, .alert, .js-flash {
        transition: opacity 0.4s ease, transform 0.4s ease;
    }
    .u-alert.reks-fade-out, .alert.reks-fade-out, .js-flash.reks-fade-out {
        opacity: 0;
        transform: translateY(-6px);
    }
</style>

<div id="reksLoadBar"></div>

<script>
(function () {
    // ---------- 1. Auto-dismiss flash / alert messages ----------
    var DISMISS_AFTER_MS = 4000; // 4 seconds

    document.querySelectorAll('.u-alert, .alert, .js-flash').forEach(function (el) {
        if (!el.textContent.trim()) return;

        setTimeout(function () {
            el.classList.add('reks-fade-out');
            setTimeout(function () {
                el.remove();
            }, 400);
        }, DISMISS_AFTER_MS);
    });

    // ---------- 2. Global loading bar on form submit / link click ----------
    var bar = document.getElementById('reksLoadBar');

    function showLoadBar() {
        bar.classList.add('active');
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.tagName === 'FORM' && !form.hasAttribute('data-no-loading')) {
            showLoadBar();
        }
    }, true);

    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[href]');
        if (!link) return;
        if (link.hasAttribute('data-no-loading')) return;
        if (link.target === '_blank') return;
        var href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

        showLoadBar();
    }, true);

    window.addEventListener('pageshow', function () {
        bar.classList.remove('active');
    });
})();
</script>