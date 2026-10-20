{{--
    Shared toast notifications + flash messages.
    Save as: resources/views/partials/ui-toast.blade.php
    Usage:   @include('partials.ui-toast')  at the END of @section('content').
    Exposes: window.showToast(message, type = 'success' | 'error' | 'info', { title, duration })
--}}
<div id="toastStack" class="no-print" aria-live="polite"></div>

<script>
    (function() {
        const stack = document.getElementById('toastStack');
        const TYPES = {
            info: { icon: 'fa-solid fa-circle-info', title: 'Notice' },
            success: { icon: 'fa-solid fa-circle-check', title: 'Success' },
            error: { icon: 'fa-solid fa-circle-exclamation', title: 'Error' }
        };

        window.showToast = function(msg, type = 'success', opts = {}) {
            const cfg = TYPES[type] || TYPES.success;
            const title = opts.title || cfg.title;
            const duration = opts.duration ?? 4000;

            const el = document.createElement('div');
            el.className = `toast toast-${type}`;
            el.innerHTML = `
                <div class="toast-icon"><i class="${cfg.icon}"></i></div>
                <div class="toast-body">
                    <div class="toast-title"></div>
                    <div class="toast-msg"></div>
                </div>
                <button type="button" class="toast-close" aria-label="Dismiss"><i class="fa-solid fa-xmark"></i></button>
                <div class="toast-bar" style="animation-duration:${duration}ms;"></div>
            `;
            el.querySelector('.toast-title').textContent = title;
            el.querySelector('.toast-msg').textContent = msg;

            function dismiss() {
                if (!el.isConnected) return;
                el.classList.add('hide');
                setTimeout(() => el.remove(), 280);
            }

            el.querySelector('.toast-close').addEventListener('click', dismiss);
            stack.appendChild(el);
            if (duration > 0) setTimeout(dismiss, duration);

            return { el, dismiss };
        };

        @if (session('success'))
            window.showToast(@json(session('success')), 'success');
        @endif
        @if (session('error'))
            window.showToast(@json(session('error')), 'error');
        @endif
    })();
</script>
