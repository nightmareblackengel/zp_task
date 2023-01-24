(function ($)
{
    const AJAX_RESULT_OK = 1;
    const AJAX_RESULT_NOT_FILLED = 2;
    const AJAX_RESULT_ERR = 3;

    function ChatLoadPager()
    {
        this.ajaxObj = null;
    }

    ChatLoadPager.prototype.init = function ()
    {
        this.loadData();
    }

    ChatLoadPager.prototype.loadData = function ()
    {
        if (this.ajaxObj) {
            this.ajaxObj.abort().done(function() {
                console.log('ajaxObj aborted');
            });
            this.ajaxObj = null;
            console.log('ajaxObj nulled');
        }

        this.ajaxObj = $.ajax(this.getAjaxData()).done(this.parseAjaxResHandler);
    }

    ChatLoadPager.prototype.parseAjaxResHandler = function (data)
    {
        var errMsg = 'Возникла ошибка! ';

        if (!data || !data.result) {
            // TODO:
            // alert(errMsg);
            console.log(errMsg);
            window.nbeClp.alwaysOnAjaxDone();
            return false;
        }
        if (data.result === AJAX_RESULT_ERR) {
            if (data.message) {
                errMsg = data.message;
            }
            // TODO:
            // alert(errMsg);
            console.log(errMsg);
            window.nbeClp.alwaysOnAjaxDone();
            return false;
        }

        if (data.chats && data.chats.result === AJAX_RESULT_OK && data.chats.html) {
            $('.nbeAjaxChatContainer').html(data.chats.html);
            $('.nbeAjaxChatContainer').attr('data-chat-updated', data.chats.downloaded_at);
        }
        if (data.messages && data.messages.result === AJAX_RESULT_OK && data.messages.html) {
            $('.nbeAjaxMessageContainer').html(data.messages.html);
            if (data.messages.show_add_new_message) {
                $('.addNewMsgContainer').removeClass('nbeDisplayNone');
            }
        }
        if (data.new_message && data.new_message.result === AJAX_RESULT_OK && data.new_message.html) {
            $('.addNewMsgContainer').html(data.new_message.html);
        }

        console.log('done', data);
        window.nbeClp.alwaysOnAjaxDone();
        return true;
    }

    ChatLoadPager.prototype.alwaysOnAjaxDone = function ()
    {
        window.nbeClp.hideAjaxLoader('chats');
        window.nbeClp.hideAjaxLoader('messages');
    }

    ChatLoadPager.prototype.hideAjaxLoader = function (type)
    {
        if (!type) {
            return;
        }
        $('.loaderContainer[data-code="' + type + '"]').removeClass('nbeLoading');
    }

    ChatLoadPager.prototype.getAjaxData = function()
    {
        var $chatContainer = $('.nbeAjaxChatContainer');
        var chatId = parseInt($chatContainer.attr('data-chat-id'));
        if (!chatId) {
            chatId = 0;
        }
        var chatUpdatedAt = parseInt($chatContainer.attr('data-chat-updated'));
        if (!chatUpdatedAt) {
            chatUpdatedAt = 0;
        }

        var sendData = {
            'chats': {
                'id': chatId,
                'lastUpdatedAt': chatUpdatedAt,
            },
            'messages': {
                'lastUpdatedAt': null,
            },
            'new_message': {

            },
        };

        return {
            'url': '/chat/ajax-load',
            'method': 'post',
            'data': sendData,
            'error': function (err) {
                var errMsg = 'Возникла ошибка! ';
                if (err && err.responseText) {
                    // statusText
                    errMsg = errMsg + 'Подробнее: ' + err.responseText;
                }
                // TODO:
                console.log(errMsg);
                // alert(errMsg);
            },
        };
    }

    window.nbeClp = new ChatLoadPager();
    $(document).ready(function () {
        window.nbeClp.init();
    });
} (jQuery));
