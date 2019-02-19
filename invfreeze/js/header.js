$(document).ready(function () {

    $.preloadImages = function () {
        for (var i = 0; i < arguments.length; i++) {
            $("<img />").attr("src", arguments[i]);
        }
    }

    $.preloadImages("img/toparrow.png", "img/bottomarrow.png");

    var $top1 = $('#minibar').offset().top + 20;
    var $mid1 = Math.floor($(window).height() / 2);
    $('#dirbutton').data('mode', 'scrollup');


    $('#dirbutton').click(function () {
        $mode = $(this).data('mode');

        switch ($mode) {
            case 'scrolldown':
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                return false;
                break;
            case 'scrollup':
                $scrollpos = $('body').outerHeight();
                $('html, body').animate({ scrollTop: $scrollpos }, 'slow');
                return false;
                break;
        }


    });

    $(window).scroll(function () {

        if ($(window).scrollTop() > $top1) {
            $('#floatingbar').hide();
            $('#minibar').show();
            $('#minibar').addClass('floater');
        }
        else {
            $('#minibar').hide();
            $('#floatingbar').show();

        }


        if ($(window).scrollTop() > $mid1) {
            $('#dirbutton').find('img').attr('src', 'img/toparrow.png');
            $('#dirbutton').data('mode', 'scrolldown');
            $('#dirbutton').show();
        } else {
            $('#dirbutton').find('img').attr('src', 'img/bottomarrow.png');
            $('#dirbutton').data('mode', 'scrollup');
        }
    });
});
