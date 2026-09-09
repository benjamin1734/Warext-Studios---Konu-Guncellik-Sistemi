(function () {
    'use strict';
    function placeFreshnessWidget() {
        var widget = document.getElementById('wrxt-thread-freshness');
        var title = document.querySelector('.p-title');
        if (!widget || !title || !title.parentNode) return;
        var mobile = window.matchMedia && window.matchMedia('(max-width: 650px)').matches;
        if (mobile) {
            if (widget.parentNode !== title.parentNode || widget.previousElementSibling !== title) title.insertAdjacentElement('afterend', widget);
            widget.style.width = '100%';
            widget.style.maxWidth = 'none';
            widget.style.margin = '7px 0 10px';
        } else {
            if (widget.parentNode !== title) title.appendChild(widget);
            widget.style.width = '100%';
            widget.style.maxWidth = '360px';
            widget.style.margin = '0 0 0 auto';
        }
        widget.dataset.wrxtPlaced = '1';
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', placeFreshnessWidget, {once:true}); else placeFreshnessWidget();
    window.addEventListener('load', placeFreshnessWidget, {once:true});
    window.addEventListener('resize', placeFreshnessWidget);
}());
