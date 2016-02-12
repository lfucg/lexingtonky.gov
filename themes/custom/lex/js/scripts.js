(function() {
  var $ = jQuery;

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
  })
}());
