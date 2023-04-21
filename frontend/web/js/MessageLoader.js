(function ($)
{
    function MessageLoader()
    {
        this.ajaxObj = null;
        this.ajaxTimer = null;
        this.ajaxCount = 0;
        // "возможность фиксировать перемещение скрола"
        this.canCheckScroll = false;
    }

    MessageLoader.prototype.init = function ()
    {
        var isMessagePage = this.isMessagesListPage();
        this.initScrollChecker();
        // первая загрузка данных
        this.loadData(
            AJAX_REQUEST_INCLUDE,
            isMessagePage ? AJAX_REQUEST_INCLUDE : AJAX_REQUEST_EXCLUDE,
            isMessagePage ? AJAX_REQUEST_INCLUDE : AJAX_REQUEST_EXCLUDE
        );
    }

    MessageLoader.prototype.isMessagesListPage = function()
    {
        return $('.nbeAjaxMessageContainer').length && $('.addNewMsgContainer').length;
    }

    MessageLoader.prototype.initScrollChecker = function()
    {
        var selfMl = this;

        $('.nbeAjaxMessageContainer').on('scroll', function(event) {
            if (!selfMl.canCheckScroll) {
                return false;
            }

            var scrollTop = parseInt($('.nbeAjaxMessageContainer').scrollTop());
            console.log('can scroll', scrollTop, scrollTop < 200);
            if (!isNaN(scrollTop) && $('.nbeAjaxMessageContainer').find('.newMessageCircle').length) {
                // если скрол приближается к элементу "загрузка данных"
                if (scrollTop < 200) {
                    selfMl.canCheckScroll = false;
                    selfMl.loadData(
                        AJAX_REQUEST_EXCLUDE,
                        AJAX_REQUEST_CHECK_PREV,
                        AJAX_REQUEST_EXCLUDE
                    );
                }
            }
        });
    }

    MessageLoader.prototype.loadData = function (showChats, showMessages, showAddNewItem)
    {
        window.nbeClp.ajaxCount++;
        window.nbeClp.clearAjaxTimer();

        window.nbeClp.ajaxObj = $.ajax(
            window.nbeClp.getAjaxParams(
                showChats,
                showMessages,
                showAddNewItem
            )
        ).done(window.nbeClp.parseAjaxResHandler);
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

        if (data.new_message && data.new_message.result === AJAX_RESPONSE_OK && data.new_message.html) {
            $('.addNewMsgContainer').html(data.new_message.html);
            window.nbeClp.initSendForm();
            $('.addNewMsgContainer').removeClass('nbeDisplayNone');
        }

        if (data.messages && data.messages.result === AJAX_RESPONSE_OK) {
            var placementType = AJAX_RESPONSE_PLACE_APPEND;
            if (data.messages.msgAddType) {
                placementType = data.messages.msgAddType;
            }

            if (data.messages.html) {
                if (placementType === AJAX_RESPONSE_PLACE_NEW) {
                    $('.nbeAjaxMessageContainer').html(data.messages.html);
                } else if (placementType === AJAX_RESPONSE_PLACE_APPEND) {
                    $('.nbeAjaxMessageContainer').append(data.messages.html);
                } else if (placementType === AJAX_RESPONSE_PLACE_PREPEND) {
                    if (placementType === AJAX_RESPONSE_PLACE_PREPEND) {
                        // удалим отображение подзагрузки
                        $('.nbeAjaxMessageContainer').find('.newMessageCircle').remove();
                    }
                    // чтобы "крутящееся колесико" не отображалось вечно -
                    $('.nbeAjaxMessageContainer').scrollTop(250);
                    //
                    $('.nbeAjaxMessageContainer').prepend(data.messages.html);
                }
            }
            // сокроем "общий лоадер" (можно вызывать дважды и более)
            window.nbeClp.hideAjaxLoader('messages');
            if (data.chat_id) {
                // установим кол-во сообщений
                $('.nbeAjaxChatContainer .list-group-item[data-id="' + data.chat_id + '"]').attr('data-msg-count', data.messages.messages_count);
            }

            if (placementType === AJAX_RESPONSE_PLACE_NEW || placementType === AJAX_RESPONSE_PLACE_APPEND) {
                window.nbeClp.scrollToLastMessage(data.chat_id);
            }
            // разрешаем фиксировать движения скрола, после "предзагрузки"ы
            window.nbeClp.canCheckScroll = true;
        }
        window.nbeClp.alwaysOnAjaxDone();

        var showMessages = data.chat_id ? AJAX_REQUEST_CHECK_NEW : AJAX_REQUEST_EXCLUDE;
        window.nbeClp.ajaxCount--;

        // 1. из-за задержки ответа со стороны сервера - возможен "двойной запуск"
        // (здесь "двойной" запуск нужен, т.к. у пользователя должно обновиться окно сразу после того, как он напечатал сообщение)
        // 2. повторно данные будут обновляться только для страницы сообщений
        if (window.nbeClp.ajaxCount < 1 && window.nbeClp.isMessagesListPage()) {
            window.nbeClp.ajaxTimer = setTimeout(function () {
                window.nbeClp.loadData(
                    AJAX_REQUEST_EXCLUDE,
                    showMessages,
                    AJAX_REQUEST_EXCLUDE
                );
            }, 5000);
        }

        return true;
    }

    // скролл до выделенного чата, в области списка чатов
    MessageLoader.prototype.scrollChatListTo = function (chatId)
    {
        if (!chatId) {
            return;
        }

        var $scrollToItem = $('.nbeChatList .list-group-item[data-id="' + chatId + '"]');
        if (!$scrollToItem.length) {
            return;
        }
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

    MessageLoader.prototype.getAjaxParams = function(showChats, showMessages, showAddNewItem)
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
            'chat_msg_count': $('.nbeAjaxChatContainer .list-group-item[data-id="' + chatId + '"]').attr('data-msg-count'),
            'last_updated_at': null,
        }
        if (showMessages === AJAX_REQUEST_CHECK_PREV) {
            messagesParam['showed_msg_count'] = $('.nbeAjaxMessageContainer').find('.oneMsgContainer').length;
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
            'url': '/ajax/load',
            'method': 'post',
            'data': sendData,
            'error': function (err) {
                var errMsg = 'Возникла ошибка! ';
                if (err && err.responseText) {
                    // statusText
                    errMsg = errMsg + 'Подробнее: ' + err.responseText;
                }
                // повторный запуск "подгрузки сообщений" выполнятся не будет (т.к. возникает ошибка)
                window.nbeClp.ajaxCount--;
                alert(errMsg);
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
            'message': window.nbeClp.addAttributeParam('messageaddform-message'),
            "userId": window.nbeClp.addAttributeParam('messageaddform-userid'),
            "chatId": window.nbeClp.addAttributeParam('messageaddform-chatid'),
            "messageType": window.nbeClp.addAttributeParam('messageaddform-messagetype'),
        });

        var $activeChatItem = $('.list-group-item.active');
        var $cmdChannelCommands = $('.msgLinkCmd[data-chat-type="1"]');
        if ($activeChatItem.attr('data-type') === '1') {
            $cmdChannelCommands.removeClass('nbeDisplayNone');
        } else {
            $cmdChannelCommands.addClass('nbeDisplayNone');
        }
    }

    // удаляем таймер подгрузки новых сообщений
    MessageLoader.prototype.clearAjaxTimer = function()
    {
        if (window.nbeClp.ajaxTimer) {
            clearTimeout(window.nbeClp.ajaxTimer);
            window.nbeClp.ajaxTimer = null;
        }
    }

    window.nbeClp = new MessageLoader();
    $(document).ready(function () {
        window.nbeClp.init();
    });
} (jQuery));
