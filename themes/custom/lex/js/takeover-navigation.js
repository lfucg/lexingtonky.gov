(function($) {

  let windowWidth = $( window ).width();
  $( window ).resize( function() {
    windowWidth = $( window ).width();
  });

  $('.navbar-button,.menu-label').click( function() {
    $("#nav-icon").toggleClass('open');
    $("#takeoverNav").toggleClass('show');
    $("body").toggleClass('no-scroll');
    $('.menu-label').html($('.menu-label').html() == 'Menu' ? 'Close' : 'Menu');


    // going to need to use this for nav set up.
    if (windowWidth > 998) {
      console.log('bigSCREENS');
    } else {
      console.log('littlescreens');
    }
  });

  $('.level-1').click(function(e) {
      e.stopPropagation();
      e.stopImmediatePropagation();
      alert('Not so fast! We\'re still working on this stuff!');
  })

}(jQuery));
