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
    var submitBtn = form.find("input[type=submit]:visible");
    var accordion = form.parent('.js-accordion-content');

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
  })
}());
