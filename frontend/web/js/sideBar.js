(function($) {
    function NbeSideBar()
    {

    }

    NbeSideBar.prototype.init = function ()
    {
        $('.nbeFLeft').on('click', function () {
            var $sideBar = $('#nbeLeftSideBar');
            var $mainContainer = $('.nbeContainer');
            var $footer = $('.nbeFooter');
            var $chatBtn = $(this);

            if ($sideBar.hasClass('nbeShowedSide')) {
                $sideBar.removeClass('nbeShowedSide');
                $mainContainer.removeClass('nbeFullWidth');
                $footer.removeClass('nbeFullWidth');
                $chatBtn.removeClass('nbeSelected');
            } else {
                $sideBar.addClass('nbeShowedSide');
                $mainContainer.addClass('nbeFullWidth');
                $footer.addClass('nbeFullWidth');
                $chatBtn.addClass('nbeSelected');
            }
        });
        // first time its show on
        $('.nbeFLeft').trigger('click');
    }

    window.nbeSideBar = new NbeSideBar();

    $(document).ready(function() {
        window.nbeSideBar.init();
    });
} (jQuery));
