(function($)
{
    function NewMsgForm()
    {

    }

    NewMsgForm.prototype.init = function ()
    {
        this.initSendBtn();
        this.initCommands();
    }

    NewMsgForm.prototype.initSendBtn = function ()
    {
        $(document).on('click', '.nbeAddNewMsgBtn', function(event) {
            var $sendBtn = $(this);
            if ($sendBtn.attr('data-process') === '1') {
                return;
            }
            $sendBtn.attr('data-process', '1');

            $.ajax({
                'url': '/chat/create-msg',
                'method': 'POST',
                'data': $('#addNewMessageForm').serialize(),
                'error': function (data) {
                    var errMsg = 'Возникла ошибка при сохранении сообщения!';
                    alert(errMsg);
                    $sendBtn.attr('data-process', '0');
                },
            }).done(function (data) {
                if (!data || !data.result) {
                    alert('Возникла ошибка при сохранении сообщения!');
                    return;
                }

                if (data.result === AJAX_RESPONSE_ERR) {
                    if (data.message) {
                        alert(data.message);
                        return;
                    }
                    if (data.form_err) {
                        $('#addNewMessageForm').yiiActiveForm('updateMessages', data.form_err)
                    }
                } else if (data.result === AJAX_RESPONSE_OK) {
                    window.nbeClp.loadData(
                        AJAX_REQUEST_INCLUDE,
                        AJAX_REQUEST_INCLUDE,
                        AJAX_REQUEST_INCLUDE
                    );
                }
                $('.nbeAddNewMsgBtn').attr('data-process', '0');
            });
        });
    }

    NewMsgForm.prototype.initCommands = function()
    {
        $(document).on('click', '.msgLinkCmd', function() {
            var $link = $(this);

            $('#chatmessageform-message').val($link.text());
        });
    }

    window.newMsgForm = new NewMsgForm();
    $(document).ready(function () {
        window.newMsgForm.init();
    });

}) (jQuery);
