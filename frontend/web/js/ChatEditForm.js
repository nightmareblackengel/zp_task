(function($) {
    function ChatEditForm()
    {

    }

    ChatEditForm.prototype.init = function ()
    {
        this.initIsChatBtn();
    }

    ChatEditForm.prototype.initIsChatBtn = function ()
    {
        $('#chatIsChannel').on('input', function(event) {
            if ($(this).is(':checked')) {
                $('.chatNameFieldWrapper').removeClass('nbeDisplayNone');
            } else {
                $('.chatNameFieldWrapper').addClass('nbeDisplayNone');
            }
        });
        // инициализация
        $('#chatIsChannel').trigger('input');
    }

    window.chatFrm = new ChatEditForm();
    $(document).ready(function() {
        window.chatFrm.init();
    });
}) (jQuery);