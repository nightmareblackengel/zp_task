(function($) {

    function NewMsgForm()
    {

    }

    NewMsgForm.prototype.init = function ()
    {
        this.initSendBtn();
    }

    NewMsgForm.prototype.initSendBtn = function ()
    {
        $(document).on('click', '.nbeAddNewMsgBtn', function(event) {
            var $sendBtn = $(this);
            if ($sendBtn.attr('data-process') === '1') {
                return;
            }
            $sendBtn.attr('data-process', '1');

            var ajaxData = {};
            $.ajax({
                'url': '/chat/create-msg',
                'method': 'POST',
                'data': $('#addNewMessageForm').serialize(),
                'error': function (data) {
                    console.log('err', data);
                },
            }).done(function (data) {
                console.log(data);

                $sendBtn.attr('data-process', '0');
            });
            // console.log();
        });
    }

    window.newMsgForm = new NewMsgForm();
    $(document).ready(function () {
        window.newMsgForm.init();
    });

}) (jQuery);
