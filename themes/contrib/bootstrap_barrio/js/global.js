/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.bootstrap_barrio = {
    attach: function (context, settings) {

      var position = $(window).scrollTop();
	    $(window).scroll(function() {
        if ($(this).scrollTop() > 50){  
          $('body').addClass("scrolled");
        }
        else{
          $('body').removeClass("scrolled");
        }
        var scroll = $(window).scrollTop();
        if (scroll > position) {
          $('body').addClass("scrolldown");
          $('body').removeClass("scrollup");
        } else {
          $('body').addClass("scrollup");
          $('body').removeClass("scrolldown");
        }
        position = scroll;
      });

      var toggleAffix = function(affixElement, scrollElement, wrapper) {
        var height = affixElement.outerHeight(),
            top = wrapper.offset().top;
        if (scrollElement.scrollTop() >= top){
            wrapper.height(height);
            affixElement.addClass("affix");
        }
        else {
            affixElement.removeClass("affix");
            wrapper.height('auto');
        }
      };
      $('[data-toggle="affix"]').each(function() {
        var ele = $(this),
          wrapper = $('<div></div>');
        ele.before(wrapper);
        $(window).on('scroll resize', function() {
          toggleAffix(ele, $(this), wrapper);
        });
        // init
        toggleAffix(ele, $(window), wrapper);
      });
    }
  };

})(jQuery, Drupal);
