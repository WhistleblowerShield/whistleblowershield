/**
 * ws-core-admin.js
 *
 * Admin-side JavaScript for ws-core.
 * Enqueued by ws-core.php on all singular posts and pages.
 *
 * Version history:
 *   1.0.0 — Initial file. Adds "Open URL" buttons to ACF URL fields in the admin.
 * 
 */

(function() {

        'use strict';

        function cleanUrl(raw) {
            return (raw || '').trim();
        }

        function applyState(fieldEl) {
            if (!fieldEl) return;
            var input = fieldEl.querySelector('input[type="url"]');
            var btn = fieldEl.querySelector('.ws-acf-open-url-btn');
            if (!input || !btn) return;

            var url = cleanUrl(input.value);
            var hasUrl = url.length > 0;

            if (hasUrl) {
                btn.href = url;
                btn.classList.remove('is-disabled');
                btn.removeAttribute('aria-disabled');
                btn.removeAttribute('tabindex');
            } else {
                btn.href = '#';
                btn.classList.add('is-disabled');
                btn.setAttribute('aria-disabled', 'true');
                btn.setAttribute('tabindex', '-1');
            }
        }

        function bindField(fieldEl) {
            if (!fieldEl || fieldEl.dataset.wsUrlButtonBound === '1') return;
            var input = fieldEl.querySelector('input[type="url"]');
            var btn = fieldEl.querySelector('.ws-acf-open-url-btn');
            if (!input || !btn) return;

            fieldEl.dataset.wsUrlButtonBound = '1';

            input.addEventListener('input', function() { applyState(fieldEl); });
            input.addEventListener('change', function() { applyState(fieldEl); });
            applyState(fieldEl);
        }

        function scan(root) {
            var scope = root || document;
            var fields = scope.querySelectorAll('.acf-field[data-type="url"]');
            fields.forEach(bindField);
        }

        document.addEventListener('DOMContentLoaded', function() { scan(document); });

        if (window.acf && typeof window.acf.addAction === 'function') {
            window.acf.addAction('ready', function($el) { scan($el && $el[0] ? $el[0] : document); });
            window.acf.addAction('append', function($el) { scan($el && $el[0] ? $el[0] : document); });
        }
    })();
 

