// (function($) {
//     function ChatPanel()
//     {
//
//     }
//
//     ChatPanel.prototype.init = function ()
//     {
//         $('.nbeFLeft').on('click', function () {
//             var $firstColumns = $('.firstLayoutColumn');
//             var $chatBtn = $(this);
//
//             if ($chatBtn.hasClass('nbeSelected')) {
//                 $firstColumns.addClass('hideFlc');
//                 $chatBtn.removeClass('nbeSelected');
//             } else {
//                 $firstColumns.removeClass('hideFlc');
//                 $chatBtn.addClass('nbeSelected');
//             }
//         });
//         // first time its show on
//         $('.nbeFLeft').trigger('click');
//     }
//
//     window.nbeSideBar = new ChatPanel();
//
//     $(document).ready(function() {
//         window.nbeSideBar.init();
//     });
// } (jQuery));
