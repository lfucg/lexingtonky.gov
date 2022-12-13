;
(function($) {
  // $('.lex-hide-unless-javascript').removeClass('lex-hide-unless-javascript');

  // Commenting out the below logic.
  // Was sercumventing captcha logic needed for page feedback form.
  // Will remove in the near future.

  // $('#js-feedback-button').find('a').click(function () {
  //   $('.lex-pagefeedback-container').find('.js-accordion-control').click();
  // });

  // $('.js-accordion-content #contact-message-page-feedback-form').submit(function(e) {
  //   e.preventDefault();
  //   // var form = $(e.target);
  //   var submitBtn = form.find("input[type=submit]:visible");
  //   var accordion = form.parent('.js-accordion-content');
  //   var feedback_url = window.location.pathname + window.location.search;

  //   form.find('#edit-field-feedback-url-0-value').val(feedback_url);
  //   submitBtnOriginalVal = submitBtn.val();
  //   submitBtn.val('Sending...');

  //   $.ajax({
  //     type:'POST',
  //     url: '/contact/page_feedback',
  //     data: form.serialize(),
  //     success: function(response) {
  //       console.log('success');
  //       accordion.find('.js-success').removeClass('visually-hidden');
  //       submitBtn.val(submitBtnOriginalVal);
  //     },
  //     error: function(response) {
  //       console.log('error');
  //       accordion.find('.js-error').removeClass('visually-hidden');
  //     }
  //   });
  // });

  $.LexingtonFilterBlock = function(el) {
    var options = {
      valueNames: [ 'js-lex-filter-item' ]
    };

    var userList = new List(el, options);
  }

  /* duplicated in browse-columns.js, lex-gis.js */
  $.LexingtonFilterBlock(document.getElementsByClassName('js-lex-filter-block')[0]);

  // Now correct titles and search terms on the search pages.
  if ($('body.path-search')) {
    var search = getParameterByName('search_api_fulltext');
    if (search) {
      $('input[type="search"]').val(search);
      $('h1').append(' for "' + search + '"');
    }
  }

}(jQuery));

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}