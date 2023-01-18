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
        }

        console.log('done', data);
        return true;
    }

    ChatLoadPager.prototype.getAjaxData = function()
    {
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

        return ajaxData;
    }

    window.nbeClp = new ChatLoadPager();
    $(document).ready(function () {
        window.nbeClp.init();
    });
} (jQuery));
