(function($) {
    function UserSettings()
    {

    }

    UserSettings.prototype.init = function ()
    {
        this.initStoreType();
    }

    UserSettings.prototype.initStoreType = function()
    {
        var $storeTypeDropDown = $('.nbeStoreTypeDd');
        $storeTypeDropDown.on('change', function() {
            var $selectedItem = $('.nbeStoreTypeDd option:selected');
            var selValue = $selectedItem.attr('value');

            $('.nbeStoreTime').hide();
            $('.nbeStoreTime[data-type="' + selValue + '"]').show();
            $('.nbeStoreTimeDd').val('');
        });

        var initValue = $('.nbeStoreTimeDd').val();
        $storeTypeDropDown.trigger('change');
        $('.nbeStoreTimeDd').val(initValue);
    }

    window.uSett = new UserSettings();

    $(document).ready(function() {
        window.uSett.init();
    });
}) (jQuery);
