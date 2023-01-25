(function($)
{
    const AJAX_RESPONSE_OK = 1;
    const AJAX_RESPONSE_NOT_FILLED = 2;
    const AJAX_RESPONSE_ERR = 3;

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
                }

                console.log(data);
                $sendBtn.attr('data-process', '0');
            });
        });
    }

    window.newMsgForm = new NewMsgForm();
    $(document).ready(function () {
        window.newMsgForm.init();
    });

}) (jQuery);
