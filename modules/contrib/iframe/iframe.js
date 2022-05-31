/**
 * @file
 * See Issue "autoresize" https://www.drupal.org/node/360549 .*/

(function ($) {
  Drupal.behaviors.iframeModule = {
    attach: function (context, settings) {
      $('iframe.autoresize').each(function () {
        var offsetHeight = 20;
        var thisIframe = $(this);
        var iframeWaitInterval;

        function resizeHeight(iframe) {
          if ($(iframe).length) { /* Iframe yet loaded ? */
            var iframeDoc = $(iframe)[0].contentDocument || $(iframe)[0].contentWindow.document;
            var contentheight = 0;

            try {
              contentheight = $(iframeDoc).find('body').height();
            }
            catch (e) {
              elem = $(iframe)[0];
              msg = $('<p><small>(' + Drupal.t('Iframe URL is not from the same domain - autoresize not working.') + ')</small></p>');
              $(elem).after(msg);
              clearInterval(iframeWaitInterval);
            }

            if (contentheight > 0) {
              clearInterval(iframeWaitInterval);
              try {
                var frameElement = $(iframe)[0].frameElement || $(iframe)[0];
                frameElement.style.height = (contentheight + offsetHeight) + 'px';
                frameElement.scrolling = 'no';
              }
              catch (e) {
                /* here, ist not an correctable error */
              }
            }
          }
        }
        var delayedResize = function () {
            resizeHeight(thisIframe);
        }

        iframeWaitInterval = setInterval(delayedResize, 300);
        // setTimeout(delayedResize, 300);
        // resizeHeight(thisIframe);
      });
    }
  }
})(jQuery);
