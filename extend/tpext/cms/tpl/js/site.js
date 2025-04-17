/*网站js*/
$(function () {
    document.onkeydown = function (ev) {
        var event = ev || event;
        if (event.keyCode == 13) {
            var keyword = $('.search-input').val();
            if (keyword.length > 0) {
                location.href = window.__site_home__ + 'dynamic/search?kwd=' + keyword;
            }
        }
    }

    $('.search-box .search-btn').click(function () {
        var keyword = $('.search-input').val();
        if (keyword.length > 0) {
            location.href = window.__site_home__ + 'dynamic/search?kwd=' + keyword;
        }
    });
});