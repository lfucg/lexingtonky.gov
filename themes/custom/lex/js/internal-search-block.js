(function($) {
  $('.search-fade, #search-icon').click( function() {
    $("#search-icon").toggleClass('open');
    $(".search-block").toggleClass('hide');
    $(".search-fade").toggleClass('show');
    $("body").toggleClass('no-scroll');
  });
}(jQuery));
