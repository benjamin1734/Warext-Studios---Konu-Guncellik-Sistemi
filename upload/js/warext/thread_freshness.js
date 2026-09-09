(function () {
    'use strict';

    var detailBackdrop = null;
    var activeDetail = null;

    function isSheetMode() {
        return window.matchMedia && window.matchMedia('(max-width: 900px)').matches;
    }

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
            widget.style.maxWidth = '340px';
            widget.style.margin = '0 0 0 auto';
        }
        widget.dataset.wrxtPlaced = '1';
    }

    function removeBackdrop() {
        if (detailBackdrop && detailBackdrop.parentNode) detailBackdrop.parentNode.removeChild(detailBackdrop);
        detailBackdrop = null;
        document.documentElement.classList.remove('wrxtFreshness-detailSheetOpen');
        document.body.classList.remove('wrxtFreshness-detailSheetOpen');
    }

    function closeDetail(detail) {
        if (detail) detail.removeAttribute('open');
        if (activeDetail === detail) activeDetail = null;
        removeBackdrop();
    }

    function syncDetail(detail) {
        if (!detail.open || !isSheetMode()) {
            if (activeDetail === detail) activeDetail = null;
            removeBackdrop();
            return;
        }
        if (activeDetail && activeDetail !== detail) activeDetail.removeAttribute('open');
        activeDetail = detail;
        if (!detailBackdrop) {
            detailBackdrop = document.createElement('div');
            detailBackdrop.className = 'wrxtFreshness-detailBackdrop';
            detailBackdrop.setAttribute('aria-hidden', 'true');
            detailBackdrop.addEventListener('click', function () { closeDetail(activeDetail); });
            document.body.appendChild(detailBackdrop);
        }
        document.documentElement.classList.add('wrxtFreshness-detailSheetOpen');
        document.body.classList.add('wrxtFreshness-detailSheetOpen');
    }

    function initDetailSheets() {
        document.querySelectorAll('details.wrxtFreshness-voteDetails').forEach(function (detail) {
            if (detail.dataset.wrxtDetailInit) return;
            detail.dataset.wrxtDetailInit = '1';
            detail.addEventListener('toggle', function () { syncDetail(detail); });
        });
    }

    document.addEventListener('click', function (event) {
        var closer = event.target.closest ? event.target.closest('[data-wrxt-detail-close]') : null;
        if (!closer) return;
        event.preventDefault();
        closeDetail(closer.closest('details.wrxtFreshness-voteDetails'));
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && activeDetail) closeDetail(activeDetail);
    });

    function refreshLayout() {
        placeFreshnessWidget();
        initDetailSheets();
        if (activeDetail) syncDetail(activeDetail);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', refreshLayout, {once:true}); else refreshLayout();
    window.addEventListener('load', refreshLayout, {once:true});
    window.addEventListener('resize', refreshLayout);
}());
