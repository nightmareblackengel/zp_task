(function ($)
{
    function ChatLoadPager()
    {
        this.ajaxObj = null;
    }

    ChatLoadPager.prototype.init = function () {
        this.loadData();
    }

    ChatLoadPager.prototype.loadData = function () {
        if (this.ajaxObj) {
            this.ajaxObj.abort().done(function() {
                console.log('ajaxObj aborted');
            });
            this.ajaxObj = null;
            console.log('ajaxObj nulled');
        }

        var sendData = {
            'requestChatId': null,
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

        this.ajaxObj = $.ajax(ajaxData)
            .done(function (data) {
                var errMsg = 'Возникла ошибка! ';

                if (!data) {
                    alert(errMsg);
                    return false;
                }

                if (data.chats && data.chats.result === 1 && data.chats.html) {
                    $('.nbeAjaxChatContainer').html(data.chats.html);
                }

                console.log('done', data);
            });
    }

    window.nbeClp = new ChatLoadPager();
    $(document).ready(function () {
        window.nbeClp.init();
    });
} (jQuery));
