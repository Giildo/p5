$(function () {
    var body = $('body').height() - 220 + 66;
    var windows = $(window).height() - 220;
    if (body < windows) {
        $('footer').css('position', 'fixed')
            .css('bottom', '0')
            .css('width', '100%');
    }
});
