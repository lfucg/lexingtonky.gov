/**
 * @file
 * Attaches behaviors for the zipang captcha refresh module.
 */

(function ($) {
  'use strict';

  /**
   * Attaches jQuery validate behavoir to forms.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *  Attaches the outline behavior to the right context.
   */
  Drupal.behaviors.CaptchaRefresh = {

    attach: function (context) {
      $('.reload-captcha', context).not('.processed').bind('click', function () {
        $(this).addClass('processed');
        var $form = $(this).parents('form');
        // Send post query for getting new captcha data.
        var date = new Date();
        var baseUrl = document.location.origin;
        var url = baseUrl + '/' + $(this).attr('href') + '?' + date.getTime();
        $.get(
          url,
          {},
          function (response) {
            if (response.status === 1) {
              $('.captcha', $form).find('img').attr('src', response.data.url);
              $('input[name=captcha_sid]', $form).val(response.data.sid);
              $('input[name=captcha_token]', $form).val(response.data.token);
            }
            else {
              alert(response.message);
            }
          },
          'json'
        );
        return false;
      });
    }
  };
})(jQuery);
