(function() {
  // set feedback_url
  var $ = jQuery;
  var link = document.getElementById('js-pagefeedback-link');
  if (link) {
    link.setAttribute('href', link.getAttribute('href') + '?feedback_url=' + window.location.pathname);
  }

  $('#contact-message-page-feedback-form').submit(function(e) {
    e.preventDefault();
    var form = $(e.target);
    var accordion = form.parent('.js-accordion-content');

    $.ajax({
      type:'POST',
      url: '/contact/page_feedback',
      data: form.serialize(),
      success: function(response) {
        accordion.find('.js-success').removeClass('visually-hidden');
    }});
  })
}());
