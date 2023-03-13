jQuery(document).ready(function() {
    jQuery('#translateModal .lang-item').click(function () {
        jQuery("#translateModal .modal-header .close").trigger('click');
    });

    jQuery('.switcher .selected').click(function() {
      if (!(jQuery('.switcher .option').is(':visible'))) {
        jQuery('.switcher .option').stop(true,true).delay(50).slideDown(800);
      }
    });

    jQuery('body').not('.switcher .selected').mousedown(function() {
      if (jQuery('.switcher .option').is(':visible')) {
        jQuery('.switcher .option').stop(true,true).delay(300).slideUp(800);
      }
    });
});
