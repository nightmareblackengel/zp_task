(function ($)
{
    function MessageLoader()
    {
        this.ajaxObj = null;
    }

    MessageLoader.prototype.init = function ()
    {
        this.loadData(
            AJAX_REQUEST_INCLUDE,
            AJAX_REQUEST_INCLUDE,
            AJAX_REQUEST_INCLUDE
        );
    }

    MessageLoader.prototype.loadData = function (showChats, showMessages, showAddNewItem)
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

    MessageLoader.prototype.parseAjaxResHandler = function (data)
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
        if (data.messages && data.messages.result === AJAX_RESPONSE_OK) {
            if (data.messages.html) {
                // TODO: new engine
                $('.nbeAjaxMessageContainer').html(data.messages.html);
            }
            // сокроем "общий лоадер" (можно вызывать дважды и более)
            window.nbeClp.hideAjaxLoader('messages');
            window.nbeClp.scrollToLastMessage(data.chat_id);
            $('.addNewMsgContainer').removeClass('nbeDisplayNone');
            if (data.chat_id) {
                // установим кол-во сообщений
                $('.nbeAjaxChatContainer .list-group-item[data-id="' + data.chat_id + '"]').attr('data-msg-count', data.messages.messages_count);
            }
        }
        if (data.new_message && data.new_message.result === AJAX_RESPONSE_OK && data.new_message.html) {
            $('.addNewMsgContainer').html(data.new_message.html);
            window.nbeClp.initSendForm();
        }
        window.nbeClp.alwaysOnAjaxDone();
        console.log('success loaded', data);

        var showMessages = AJAX_REQUEST_EXCLUDE;
        if (data.chat_id) {
            showMessages = AJAX_REQUEST_INCLUDE;
        }

        setTimeout(function () {
            window.nbeClp.loadData(
                AJAX_REQUEST_EXCLUDE,
                showMessages,
                AJAX_REQUEST_EXCLUDE
            );
        }, 5000);

        return true;
    }

    // скролл до выделенного чата, в области списка чатов
    MessageLoader.prototype.scrollChatListTo = function (chatId)
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

    // скролл вниз, в области сообщений
    MessageLoader.prototype.scrollToLastMessage = function (chatId, hasNewMsg)
    {
        var $chatNameItem = $('.nbeAjaxChatContainer .list-group-item[data-id="' + chatId + '"]');
        var clientHeight = parseInt($('.nbeAjaxMessageContainer').prop('clientHeight'));
        var scrollHeight = parseInt($('.nbeAjaxMessageContainer').prop('scrollHeight'));
        var scrollTop = parseInt($('.nbeAjaxMessageContainer').scrollTop());

        if (isNaN(clientHeight) || isNaN(scrollHeight) || isNaN(scrollTop)) {
            return;
        }
        // первый раз при загрузке данных - всегда происходит скрол вниз
        if ($chatNameItem.attr('data-scrolled') !== '1' || hasNewMsg) {
            $chatNameItem.attr('data-scrolled', '1');
            $('.nbeAjaxMessageContainer').scrollTop(scrollHeight);
            return;
        }
        // если пользователь сильно проскроллил вверх - то не будем перемещать скрол вниз
        if ((scrollHeight - scrollTop)/1.5 > clientHeight) {
            return;
        }

        $('.nbeAjaxMessageContainer').scrollTop(scrollHeight);
        return;
    }

    MessageLoader.prototype.alwaysOnAjaxDone = function ()
    {
        window.nbeClp.hideAjaxLoader('chats');
        window.nbeClp.hideAjaxLoader('messages');
    }

    MessageLoader.prototype.hideAjaxLoader = function (type)
    {
        if (!type) {
            return;
        }
        $('.loaderContainer[data-code="' + type + '"]').removeClass('nbeLoading');
    }

    MessageLoader.prototype.getAjaxData = function(showChats, showMessages, showAddNewItem)
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

        var messagesParam = {
            'show_in_response': showMessages,
            'max_msg_count': $('.nbeAjaxChatContainer .list-group-item[data-id="' + chatId + '"]').attr('data-msg-count'),
            'last_updated_at': null,
        }

        var sendData = {
            'chats': {
                'show_in_response': showChats,
                'id': chatId,
                'last_updated_at': chatUpdatedAt,
            },
            'messages': messagesParam,
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

    // получение значений для формы средствами Yii2
    MessageLoader.prototype.addAttributeParam = function(attrId)
    {
        return {
            'id': attrId,
            'input': $('#' + attrId),
            'container': $('.field-' + attrId),
            'error': $('.field-' + attrId + ' .help-block'),
        }
    }

    // инициализация Формы отправки средствами Yii2
    MessageLoader.prototype.initSendForm = function()
    {
        $('#addNewMessageForm').yiiActiveForm({
            'message': this.addAttributeParam('messageaddform-message'),
            "userId": this.addAttributeParam('messageaddform-userid'),
            "chatId": this.addAttributeParam('messageaddform-chatid'),
            "messageType": this.addAttributeParam('messageaddform-messagetype'),
        });

        var $chatHeader = $('.chatMsgHeader[data-chat-type="1"]');
        var $cmdChannelCommands = $('.msgLinkCmd[data-chat-type="1"]');
        if (!$chatHeader.length) {
            $cmdChannelCommands.addClass('nbeDisplayNone');
        } else {
            $cmdChannelCommands.removeClass('nbeDisplayNone');
        }
    }

    window.nbeClp = new MessageLoader();
    $(document).ready(function () {
        window.nbeClp.init();
    });
} (jQuery));
