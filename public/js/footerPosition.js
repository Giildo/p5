$(function () {
    if ($('body').height() < $(window).height()) {
        $('footer').css('position', 'fixed')
            .css('bottom', '0')
            .css('width', '100%');
    }
});
