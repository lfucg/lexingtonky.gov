import Cookies from 'universal-cookie';

const cookies = new Cookies();

jQuery(function ($) {

  $('.lex-alert').each(function (index, item) {
    if (!cookies.get('alert-' + $(item).attr('alert-id'))) {
      $(item).css('display', 'flex');
    }
  });

  $('.lex-alert--close').on('click', function () {
    const alertID = $(this).parent().attr('alert-id');
    cookies.set(
      'alert-' + alertID, 'hidden',
      {
        path: '/',
        maxAge: 86400,
      },
    );
    $(this).parent().hide();
  });

});
