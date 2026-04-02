/**
 * Custom Select Component
 * Replaces native <select> elements with styled dropdowns
 * Works identically across all browsers
 */
(function() {
    'use strict';

    function initCustomSelect(select) {
        if (select.dataset.customSelect) return;
        select.dataset.customSelect = 'true';
        select.style.display = 'none';

        var container = document.createElement('div');
        container.className = 'custom-select';

        var trigger = document.createElement('div');
        trigger.className = 'custom-select-trigger';

        var arrow = document.createElement('span');
        arrow.className = 'custom-select-arrow';
        arrow.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';

        var selectedText = document.createElement('span');
        selectedText.className = 'custom-select-text';

        var selectedOption = select.options[select.selectedIndex];
        selectedText.textContent = selectedOption.textContent;

        trigger.appendChild(selectedText);
        trigger.appendChild(arrow);

        var dropdown = document.createElement('div');
        dropdown.className = 'custom-select-dropdown';
        dropdown.style.display = 'none';

        for (var i = 0; i < select.options.length; i++) {
            var opt = select.options[i];
            var item = document.createElement('div');
            item.className = 'custom-select-option';
            if (opt.disabled) item.classList.add('disabled');
            if (opt.selected) item.classList.add('selected');
            item.textContent = opt.textContent;
            item.dataset.value = opt.value;
            item.addEventListener('click', (function(option, textEl) {
                return function(e) {
                    e.stopPropagation();
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    textEl.textContent = option.textContent;
                    dropdown.querySelectorAll('.custom-select-option').forEach(function(o) {
                        o.classList.remove('selected');
                    });
                    this.classList.add('selected');
                    container.classList.remove('open');
                    dropdown.style.display = 'none';
                };
            })(opt, selectedText));
            dropdown.appendChild(item);
        }

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = container.classList.contains('open');
            document.querySelectorAll('.custom-select.open').forEach(function(cs) {
                cs.classList.remove('open');
                cs.querySelector('.custom-select-dropdown').style.display = 'none';
            });
            if (!isOpen) {
                container.classList.add('open');
                dropdown.style.display = 'block';
            }
        });

        container.appendChild(trigger);
        container.appendChild(dropdown);
        select.parentNode.insertBefore(container, select.nextSibling);

        select.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            selectedText.textContent = opt.textContent;
            dropdown.querySelectorAll('.custom-select-option').forEach(function(o) {
                o.classList.toggle('selected', o.dataset.value === opt.value);
            });
        });
    }

    function initAll() {
        document.querySelectorAll('select:not([data-custom-select]):not(.no-custom)').forEach(initCustomSelect);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            m.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) {
                    if (node.tagName === 'SELECT' && !node.dataset.customSelect) {
                        initCustomSelect(node);
                    }
                    node.querySelectorAll('select:not([data-custom-select]):not(.no-custom)').forEach(initCustomSelect);
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });

    document.addEventListener('click', function() {
        document.querySelectorAll('.custom-select.open').forEach(function(cs) {
            cs.classList.remove('open');
            cs.querySelector('.custom-select-dropdown').style.display = 'none';
        });
    });
})();
