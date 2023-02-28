(function($)
{
    function NewMsgForm()
    {

    }

    NewMsgForm.prototype.init = function ()
    {
        this.initSendBtn();
        this.initCommands();
        this.initForm();
    }

    NewMsgForm.prototype.initSendBtn = function ()
    {
        var selfNmf = this;

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
                    selfNmf.ajaxDoneHandler();
                    return;
                }

                if (data.result === AJAX_RESPONSE_ERR) {
                    if (data.message) {
                        alert(data.message);
                        selfNmf.ajaxDoneHandler();
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
                selfNmf.ajaxDoneHandler();
            });
        });
    }

    NewMsgForm.prototype.ajaxDoneHandler = function ()
    {
        $('.nbeAddNewMsgBtn').attr('data-process', '0');
    }

    NewMsgForm.prototype.initCommands = function()
    {
        $(document).on('click', '.msgLinkCmd', function() {
            var $link = $(this);

            $('#messageaddform-message').val($link.text());
        });
    }

    NewMsgForm.prototype.initForm = function()
    {
        $(document).on('submit', '#addNewMessageForm', function(event) {
            $('.nbeAddNewMsgBtn').trigger('click');

            event.preventDefault();
            return false;
        });
    }

    window.newMsgForm = new NewMsgForm();
    $(document).ready(function () {
        window.newMsgForm.init();
    });

}) (jQuery);
