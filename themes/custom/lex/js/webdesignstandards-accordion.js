(function() {
  /*
   * Web Design Standards uses jQuery 1.11, which conflicts with drupal
   * This is a manual extraction of the accordion since that's all we need
   * and it works with the Drupal jQuery
   */
  var $ = jQuery;
  var accordionControl = '.js-accordion-control';

  /**
   * Accordion
   *
   * An accordion component.
   *
   * @param {jQuery} $el A jQuery html element to turn into an accordion.
   */
  function Accordion($el) {
    var self = this;
    this.$root = $el;
    this.$root.on('click', accordionControl, function(ev) {
      var expanded = JSON.parse($(this).attr('aria-expanded'));
      ev.preventDefault();
      self.hideAll();
      if (!expanded) {
        self.show($(this));
      }
    });
  }

  Accordion.prototype.$ = function(selector) {
    return this.$root.find(selector);
  }

  Accordion.prototype.hide = function($button) {
    var selector = $button.attr('aria-controls'),
        $content = this.$('#' + selector);

    $button.attr('aria-expanded', false);
    $content.attr('aria-hidden', true);
  };

  Accordion.prototype.show = function($button) {
    var selector = $button.attr('aria-controls'),
        $content = this.$('#' + selector);

    $button.attr('aria-expanded', true);
    $content.attr('aria-hidden', false);
  };

  Accordion.prototype.hideAll = function() {
    var self = this;
    this.$(accordionControl).each(function() {
      self.hide($(this));
    });
  };
  function accordion($el) {
    return new Accordion($el);
  }

  $('[class^=lex-accordion]').each(function() {
    accordion($(this));
  });
}());
