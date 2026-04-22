/**
 * ws-core-admin.js
 *
 * Admin-side JavaScript for ws-core.
 * Enqueued by ws-core.php on the WS Prompt Generator admin tool screen.
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
 

(function() {
    'use strict';

    if (!document.getElementById('ws-prompt-generator-root')) {
        return;
    }

    var wsPromptLastAutoScope = '';

    function wsPromptGetDefaultScopeForJx(jxCode) {
        var jx = (jxCode || '').toUpperCase().trim();
        var jxType = 'state';
        if (jx === 'US') {
            jxType = 'federal';
        } else if (jx === 'DC') {
            jxType = 'district';
        } else if (['AS', 'GU', 'MP', 'PR', 'VI'].indexOf(jx) !== -1) {
            jxType = 'territory';
        }
        return jxType + '-level whistleblower laws and protections';
    }

    function wsPromptSyncScopeFromJx() {
        var recordType = document.getElementById('record_type');
        var jxInput = document.getElementById('jx_id');
        var scopeNotes = document.getElementById('scope_details');
        if (!recordType || !jxInput || !scopeNotes) return;

        var type = (recordType.value || '').toLowerCase();
        if (type !== 'statute' && type !== 'common-law') return;

        var current = (scopeNotes.value || '').trim();
        var nextDefault = wsPromptGetDefaultScopeForJx(jxInput.value);
        var looksLikeDefault = /^(state|federal|district|territory)-level whistleblower laws and protections$/i.test(current);

        if (current === '' || current === wsPromptLastAutoScope || looksLikeDefault) {
            scopeNotes.value = nextDefault;
            wsPromptLastAutoScope = nextDefault;
        }
    }

    function wsPromptApplyJxFromSelect() {
        var select = document.getElementById('jx_select');
        var input = document.getElementById('jx_id');
        if (!select || !input) return;
        if (select.value) input.value = select.value.toUpperCase();
        wsPromptSyncScopeFromJx();
    }

    function wsPromptToggleFields() {
        var recordTypeEl = document.getElementById('record_type');
        if (!recordTypeEl) return;
        var type = recordTypeEl.value;
        var groups = {
            'statute':      ['ws-field-statute'],
            'common-law':   ['ws-field-statute', 'ws-field-common-law'],
            'citation':     ['ws-field-citation'],
            'construction': ['ws-field-citation', 'ws-field-construction'],
            'assist-org':   ['ws-field-assist-org']
        };
        var allClasses = ['ws-field-statute', 'ws-field-common-law', 'ws-field-citation', 'ws-field-construction', 'ws-field-assist-org'];
        allClasses.forEach(function(cls) {
            document.querySelectorAll('.' + cls).forEach(function(el) { el.style.display = 'none'; });
        });
        (groups[type] || []).forEach(function(cls) {
            document.querySelectorAll('.' + cls).forEach(function(el) { el.style.display = ''; });
        });

        var countInput = document.getElementById('records_requested');
        if (countInput) countInput.required = (type === 'statute' || type === 'common-law');
        var proposalInput = document.getElementById('proposal_count');
        if (proposalInput) proposalInput.required = (type === 'assist-org');

        wsPromptToggleExclusions();
    }

    function wsPromptToggleExclusions() {
        var disable = document.getElementById('disable_exclusion_list');
        var autoField = document.getElementById('exclusion_list_auto');
        var manualField = document.getElementById('exclusion_list_manual');
        var refreshButton = document.getElementById('ws_refresh_exclusions');
        if (!disable) return;
        var blocked = !!disable.checked;
        if (autoField) autoField.disabled = blocked;
        if (manualField) manualField.disabled = blocked;
        if (refreshButton) refreshButton.disabled = blocked;
    }

    function initPromptToolBindings() {
        var jxInput = document.getElementById('jx_id');
        var scopeNotes = document.getElementById('scope_details');
        var recordTypeEl = document.getElementById('record_type');
        var jxSelect = document.getElementById('jx_select');
        var disableExclusions = document.getElementById('disable_exclusion_list');

        if (scopeNotes) {
            var initial = (scopeNotes.value || '').trim();
            if (/^(state|federal|district|territory)-level whistleblower laws and protections$/i.test(initial)) {
                wsPromptLastAutoScope = initial;
            }
        }
        if (jxInput) {
            jxInput.addEventListener('change', wsPromptSyncScopeFromJx);
            jxInput.addEventListener('blur', wsPromptSyncScopeFromJx);
        }
        if (jxSelect) {
            jxSelect.addEventListener('change', wsPromptApplyJxFromSelect);
        }
        if (recordTypeEl) {
            recordTypeEl.addEventListener('change', wsPromptToggleFields);
        }

        var autoTextarea = document.getElementById('exclusion_list_auto');
        var autoEdited = document.getElementById('exclusion_list_auto_edited');
        if (autoTextarea && autoEdited) {
            autoTextarea.addEventListener('input', function() { autoEdited.value = '1'; });
        }
        if (disableExclusions) {
            disableExclusions.addEventListener('change', wsPromptToggleExclusions);
        }

        wsPromptToggleFields();
        wsPromptSyncScopeFromJx();
        wsPromptToggleExclusions();
    }

    window.wsPromptApplyJxFromSelect = wsPromptApplyJxFromSelect;
    window.wsPromptToggleFields = wsPromptToggleFields;

    document.addEventListener('DOMContentLoaded', initPromptToolBindings);
})();

