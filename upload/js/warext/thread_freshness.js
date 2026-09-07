(function () {
    'use strict';

    function placeFreshnessWidget() {
        var widget = document.getElementById('wrxt-thread-freshness');
        if (!widget || widget.dataset.wrxtPlaced === '1') {
  return;
        }

        var title = document.querySelector('.p-title');
        if (!title || !title.parentNode) {
  return;
        }

        title.insertAdjacentElement('afterend', widget);
        widget.dataset.wrxtPlaced = '1';
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', placeFreshnessWidget, { once: true });
    } else {
        placeFreshnessWidget();
    }

    window.addEventListener('load', placeFreshnessWidget, { once: true });
}());
