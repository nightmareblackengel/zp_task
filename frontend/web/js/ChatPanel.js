(function($) {
    function ChatPanel()
    {

    }

    ChatPanel.prototype.init = function ()
    {
        $('.nbeFLeft').on('click', function () {
            var $sideBar = $('#nbeLeftSideBar');
            var $chatBtn = $(this);

            if ($chatBtn.hasClass('nbeSelected')) {
                $sideBar.addClass('nbeDisplayNone');
                $chatBtn.removeClass('nbeSelected');
            } else {
                $sideBar.removeClass('nbeDisplayNone');
                $chatBtn.addClass('nbeSelected');
            }
        });
        // first time its show on
        $('.nbeFLeft').trigger('click');
    }

    window.nbeSideBar = new ChatPanel();

    $(document).ready(function() {
        window.nbeSideBar.init();
    });
} (jQuery));
