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
    $.ajax({
      type:'POST',
      url: '/contact/page_feedback',
      data: form.serialize(),
      success: function(response) {
    }});
  })
}());
