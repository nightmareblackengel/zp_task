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
            alert(errMsg);
            return false;
        }
        if (data.result === AJAX_RESULT_ERR) {
            if (data.message) {
                errMsg = data.message;
            }
            alert(errMsg);
            return false;
        }

        if (data.chats && data.chats.result === AJAX_RESULT_OK && data.chats.html) {
            $('.nbeAjaxChatContainer').html(data.chats.html);
            $('.nbeAjaxChatContainer').attr('data-chat-updated', data.chats.downloadedAt);
            window.nbeClp.hideAjaxLoader('chats');
        }
        if (data.messages && data.messages.result === AJAX_RESULT_OK && data.messages.html) {
            $('.nbeAjaxMessageContainer').html(data.messages.html);
            window.nbeClp.hideAjaxLoader('messages');
        }

        console.log('done', data);
        return true;
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
            'requestChatId': chatId,
            'lastChatUpdatedAt': chatUpdatedAt,
            "lastMsgUpdatedAt": null,
        };
        var ajaxData = {
            'url': '/chat/ajax-load',
            'method': 'post',
            'data': sendData,
            'error': function (err) {
                var errMsg = 'Возникла ошибка! ';
                if (err && err.responseText) {
                    // statusText
                    errMsg = errMsg + 'Подробнее: ' + err.responseText;
                }
                alert(errMsg);
            },
        };

        return ajaxData;
    }

    window.nbeClp = new ChatLoadPager();
    $(document).ready(function () {
        window.nbeClp.init();
    });
} (jQuery));
