(function() {
  // set feedback_url
  var link = document.getElementById('js-pagefeedback-link');
  if (link) {
    link.setAttribute('href', link.getAttribute('href') + '?feedback_url=' + window.location.pathname);
  }
}());
