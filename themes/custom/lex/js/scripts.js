(function() {
  var $ = jQuery;

  $('.lex-hide-unless-javascript').removeClass('lex-hide-unless-javascript');

  // smooth scroll in-page: https://css-tricks.com/snippets/jquery/smooth-scrolling/
  $('a[href*="#"]:not([href="#"])').click(function() {
    var openAccordion = function(container) {
      if (container.find('.js-accordion-content:visible').length === 0) {
        container.find('.js-accordion-control').click();
      }
    }

    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
      if (target.length) {
        openAccordion(target);
        $('html, body').animate({
          scrollTop: target.offset().top,
        }, 1000);
        return false;
      }
    }
  });


  $('.js-accordion-content #contact-message-page-feedback-form').submit(function(e) {
    e.preventDefault();
    var form = $(e.target);
    var submitBtn = form.find("input[type=submit]:visible");
    var accordion = form.parent('.js-accordion-content');
    var feedback_url = window.location.pathname + window.location.search;

    form.find('#edit-field-feedback-url-0-value').val(feedback_url);
    submitBtnOriginalVal = submitBtn.val();
    submitBtn.val('Sending...');

    $.ajax({
      type:'POST',
      url: '/contact/page_feedback',
      data: form.serialize(),
      success: function(response) {
        accordion.find('.js-success').removeClass('visually-hidden');
        submitBtn.val(submitBtnOriginalVal);
      },
      error: function(response) {
        accordion.find('.js-error').removeClass('visually-hidden');
      }
    });
  });

  $.LexingtonFilterBlock = function(el) {
    var options = {
      valueNames: [ 'js-lex-filter-item' ]
    };

    var userList = new List(el, options);
  }

  /* duplicated in browse-columns.js, lex-gis.js */
  $.LexingtonFilterBlock(document.getElementsByClassName('js-lex-filter-block')[0]);

}());
console.log('loaded');

function googleTranslateElementInit() {
  /* empty the translate element in case it has contents */
  new google.translate.TranslateElement({
    pageLanguage: 'en',
    includedLanguages: 'en,es,fr',
    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
  }, 'google_translate_element');

  /* override default "Select Language" */
  var label = document.getElementsByClassName('goog-te-menu-value')[0];
  if (label && ! label.innerHTML.match('Translate')) {
    label.innerHTML = '<span class="lex-translatelink">Translate</span>';
  }
}
