jQuery(function ($) {
  $('#js-feedback-button').on('click', function () {
    $(".js-accordion-control").attr('aria-hidden', function(i, attr) {
      return attr == 'false' ? 'true' : 'false'
    });

    $("#collapsible-0").attr('aria-hidden', function(i, attr) {
      return attr == 'true' ? 'false' : 'true'
    });
  });

});
