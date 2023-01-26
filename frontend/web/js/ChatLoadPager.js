(function ($)
{
    function ChatLoadPager()
    {
        this.ajaxObj = null;
    }

    ChatLoadPager.prototype.init = function ()
    {
        this.loadData(
            AJAX_REQUEST_INCLUDE,
            AJAX_REQUEST_INCLUDE,
            AJAX_REQUEST_INCLUDE
        );
    }

    ChatLoadPager.prototype.loadData = function (showChats, showMessages, showAddNewItem)
    {
        if (this.ajaxObj) {
            this.ajaxObj.abort();
            this.ajaxObj = null;
        }

        this.ajaxObj = $.ajax(
            this.getAjaxData(
                showChats,
                showMessages,
                showAddNewItem
            )
        ).done(this.parseAjaxResHandler);
    }

    ChatLoadPager.prototype.parseAjaxResHandler = function (data)
    {
        var errMsg = 'Возникла ошибка! ';

        if (!data || !data.result) {
            alert(errMsg);
            window.nbeClp.alwaysOnAjaxDone();
            return false;
        }
        if (data.result === AJAX_RESPONSE_ERR) {
            if (data.message) {
                errMsg = data.message;
            }
            alert(errMsg);
            window.nbeClp.alwaysOnAjaxDone();
            return false;
        }

        if (data.chats && data.chats.result === AJAX_RESPONSE_OK && data.chats.html) {
            $('.nbeAjaxChatContainer').html(data.chats.html);
            // сокроем "общий лоадер" (можно вызывать дважды и более)
            window.nbeClp.hideAjaxLoader('chats');
            $('.nbeAjaxChatContainer').attr('data-chat-updated', data.chats.downloaded_at);
            // выполним скролл
            window.nbeClp.scrollChatListTo(data.chat_id);
        }
        if (data.messages && data.messages.result === AJAX_RESPONSE_OK && data.messages.html) {
            $('.nbeAjaxMessageContainer').html(data.messages.html);
            // сокроем "общий лоадер" (можно вызывать дважды и более)
            window.nbeClp.hideAjaxLoader('messages');
            // проскролим до последнего сообщения
            $('.nbeAjaxMessageContainer').scrollTop($('.nbeAjaxMessageContainer').height());
            // если есть сообщения
            if (data.messages.messages_count !== false && typeof data.messages.messages_count === 'number') {
                $('.addNewMsgContainer').removeClass('nbeDisplayNone');
            }
        }
        if (data.new_message && data.new_message.result === AJAX_RESPONSE_OK && data.new_message.html) {
            $('.addNewMsgContainer').html(data.new_message.html);
            window.nbeClp.initSendForm();
        }
        window.nbeClp.alwaysOnAjaxDone();
        console.log('success loaded', data);

        setTimeout(function () {
            window.nbeClp.loadData(
                AJAX_REQUEST_EXCLUDE,
                AJAX_REQUEST_INCLUDE,
                AJAX_REQUEST_EXCLUDE
            );
        }, 5000);

        return true;
    }

    ChatLoadPager.prototype.scrollChatListTo = function (chatId)
    {
        if (!chatId) {
            return;
        }

        var $scrollToItem = $('.nbeChatList .list-group-item[data-id="' + chatId + '"]');
        var offsetHeight = parseInt($scrollToItem.offset().top);
        var itemHeight = parseInt($scrollToItem.prop('scrollHeight'));
        // отступ до 1го элементаs
        var scrollHeight = - 160;
        if (offsetHeight) {
            scrollHeight += offsetHeight;
        }
        if (itemHeight) {
            scrollHeight += itemHeight;
        }
        if (scrollHeight) {
            $('.nbeChatContainer').scrollTop(scrollHeight/2);
        }
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

    ChatLoadPager.prototype.getAjaxData = function(showChats, showMessages, showAddNewItem)
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
                'show_in_response': showChats,
                'id': chatId,
                'last_updated_at': chatUpdatedAt,
            },
            'messages': {
                'show_in_response': showMessages,
                'last_updated_at': null,
            },
            'new_item': {
                'show_in_response': showAddNewItem,
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

    ChatLoadPager.prototype.addAttributeParam = function(attrId)
    {
        return {
            'id': attrId,
            'input': $('#' + attrId),
            'container': $('.field-' + attrId),
            'error': $('.field-' + attrId + ' .help-block'),
        }
    }

    ChatLoadPager.prototype.initSendForm = function()
    {
        $('#addNewMessageForm').yiiActiveForm({
            'message': this.addAttributeParam('chatmessageform-message'),
            "userId": this.addAttributeParam('chatmessageform-userid'),
            "chatId": this.addAttributeParam('chatmessageform-chatid'),
            "messageType": this.addAttributeParam('chatmessageform-messagetype'),
        });
    }

    window.nbeClp = new ChatLoadPager();
    $(document).ready(function () {
        window.nbeClp.init();
    });
} (jQuery));
