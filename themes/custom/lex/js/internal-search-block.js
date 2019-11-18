(function($) {
  $('.search-fade, #search-icon').click( function() {
    $("#search-icon").toggleClass('open');
    $(".search-block").toggleClass('hide');
    $(".search-fade").toggleClass('show');
    $("body").toggleClass('no-scroll');
    $("#nav-icon").removeClass('open');
    $("#takeoverNav").removeClass('show');
    $(".fade").removeClass('show');
  });
}(jQuery));
