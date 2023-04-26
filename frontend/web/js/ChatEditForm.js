(function($) {
    function ChatEditForm()
    {

    }

    ChatEditForm.prototype.init = function ()
    {
        this.initIsChatBtn();
        this.initSelect2();
    }

    ChatEditForm.prototype.initSelect2 = function()
    {
        $('#userIdsSelect').select2({
            'ajax': {
                'url': '/chat/user-list',
                'dataType': 'json',
                'delay': 250,
                'data': function (params) {
                    var exceptWithUser = 0;
                    if (!$('#chatIsChannel').is(':checked')) {
                        exceptWithUser = 1;
                    }

                    var query = {
                        'q': params.term,
                        'except_with_user': exceptWithUser,
                        'chat_id': -1,
                    }
                    // Query parameters will be ?search=[term]&type=public
                    return query;
                }
            },
            'minimumInputLength': 2,
            'multiple': true,
        });
    }

    ChatEditForm.prototype.initIsChatBtn = function ()
    {
        $('#chatIsChannel').on('input', function(event) {
            $('#userIdsSelect').val('').trigger('change');
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