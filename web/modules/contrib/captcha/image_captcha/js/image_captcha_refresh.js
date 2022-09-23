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
        var url = $(this).attr('href') + '?' + date.getTime();
        // Adding loader.
        $('.captcha').append('<div class="image_captcha_refresh_loader"></div>');
        $.get(
          url,
          {},
          function (response) {
            if (response.status === 1) {
              $('.captcha', $form).find('img').attr('src', response.data.url);
              $('input[name=captcha_sid]', $form).val(response.data.sid);
              $('input[name=captcha_token]', $form).val(response.data.token);
              $('.captcha .image_captcha_refresh_loader').remove();
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
