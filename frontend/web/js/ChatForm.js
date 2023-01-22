(function($) {
    function ChatForm()
    {

    }

    ChatForm.prototype.init = function ()
    {
        this.initIsChatBtn();
    }

    ChatForm.prototype.initIsChatBtn = function ()
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

    window.chatFrm = new ChatForm();
    $(document).ready(function() {
        window.chatFrm.init();
    });
}) (jQuery);