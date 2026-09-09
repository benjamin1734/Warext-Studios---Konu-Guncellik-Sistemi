(function () {
    'use strict';
    function placeFreshnessWidget() {
        var widget = document.getElementById('wrxt-thread-freshness');
        if (!widget) return;
        var title = document.querySelector('.p-title');
        if (title && title.parentNode && widget.dataset.wrxtPlaced !== '1') {
            title.insertAdjacentElement('afterend', widget);
            widget.dataset.wrxtPlaced = '1';
        }
        var mobile = window.matchMedia && window.matchMedia('(max-width: 650px)').matches;
        widget.style.width = '100%';
        widget.style.maxWidth = mobile ? 'none' : '360px';
        widget.style.marginLeft = mobile ? '0' : 'auto';
        widget.style.marginRight = '0';
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', placeFreshnessWidget, {once:true});
    else placeFreshnessWidget();
    window.addEventListener('load', placeFreshnessWidget, {once:true});
    window.addEventListener('resize', placeFreshnessWidget);
}());
