<?php

namespace Drupal\tour_example\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for tour example routes.
 *
 * This is where our tour page is defined.
 *
 * @ingroup tour_example
 */
class TourExampleController extends ControllerBase {

  /**
   * A page for users to tour, plus some descriptive documentation.
   *
   * @return string
   *   A render array containing our 'Hello World' page content.
   */
  public function description() {
    $output = array();
    // First we make some markup for the tour demo section.
    // Note that this does not contain any tour information, only DIVs with
    // IDs. Our tour dialogs will focus on these items.
    $output['tour-demo-container'] = array(
      '#markup' => t('
        <h2>The Tour:</h2>
        <p>Click the \'Tour\' icon in the admin menu bar to start.</p>
        <div class="button" id="tour-id-1">First item.</div>
        <div class="button" id="tour-id-2">Second item.</div>
        <div class="button" id="tour-id-3">Third item.</div>
        <div class="button" id="tour-id-4">Fourth item.</div>'),
    );
    // Some explanatory markup.
    $output['tour-example-description'] = array(
      '#markup' => t('
<h2>About Tours</h2>

<p>The Tour module allows you you make instructional tours of user interfaces.</p>

<p>The Tour module comes with Drupal 8 and makes it easy for developers to add
&quot;Tours&quot; for guiding users through unfamiliar user interfaces.</p>

<p>Each tour is comprised of a series of tooltips that provide contextual
information about an interface. The user can start a tour by clicking the
&quot;Tour&quot; icon on the right side of the Drupal 8 toolbar. The tour icon is only
visible when there is a tour available on the current page.</p>

<p>The Tour module provides the Tour API, which makes it easy for developers to
add tours to their modules. In most cases, adding a tour is as simple as
creating a YAML file in the config directory in their module, containing the
expected data. For a detailed example of such a file, see
config/tour.tour.tour-example.yml.</p>

<p>If you are interested in building tours through a user interface, you may
want to look at the Tour UI module: <a href="https://drupal.org/project/tour_ui">https://drupal.org/project/tour_ui</a></p>

<p>The Tour module uses the Joyride jQuery plugin for its underlying
functionality. You can find more information about Joyride at
<a href="https://github.com/zurb/joyride">https://github.com/zurb/joyride</a></p>'
      ),
    );
    return $output;
  }

}
