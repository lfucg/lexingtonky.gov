<?php

namespace Drupal\js_example\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for js_example pages.
 *
 * @ingroup js_example
 */
class JsExampleController extends ControllerBase {

  /**
   * Example info page.
   *
   * @return array
   *   A renderable array.
   */
  public function info() {
    $build['content'] = [
      'first_line' => [
        '#prefix' => '<p>',
        '#markup' => 'Drupal includes jQuery and jQuery UI.',
        '#suffix' => '</p>',
      ],
      'second_line' => [
        '#prefix' => '<p>',
        '#markup' => 'We have two examples of using these:',
        '#suffix' => '</p>',
      ],
      'examples_list' => [
        '#theme' => 'item_list',
        '#items' => [
          'An accordion-style section reveal effect. This demonstrates calling a jQuery UI function using Drupal&#39;s rendering system.',
          'Sorting according to numeric &#39;weight.&#39; This demonstrates attaching your own JavaScript code to individual page elements using Drupal&#39;s rendering system.',
        ],
        '#type' => 'ol',
      ],
    ];

    return $build;
  }

  /**
   * Weights demonstration.
   *
   * Here we demonstrate attaching a number of scripts to the render array via
   * a library. These scripts generate content according to 'weight' and color.
   *
   * In this controller, on the Drupal side, we do three main things:
   * - Create a container DIV, with an ID all the scripts can recognize.
   * - Attach some scripts which generate color-coded content. We use the
   *   'weight' attribute to set the order in which the scripts are included in
   *   the library declaration.
   * - Add the color->weight array to drupalSettings, which is where Drupal
   *   passes data out to JavaScript.
   *
   * Each of the color scripts (red.js, blue.js, etc) uses jQuery to find our
   * DIV, and then add some content to it. The order in which the color scripts
   * execute will end up being the order of the content.
   *
   * The 'weight' atttribute in libraries yml file determines the order in which
   * a script is output to the page. To see this in action:
   * - Uncheck the 'Aggregate Javascript files' setting at:
   *   admin/config/development/performance.
   * - Load the page: examples/js_example/weights. Examine the page source.
   *   You will see that the color js scripts have been added in the <head>
   *   element in weight order.
   *
   * To test further, change a weight in the $weights array below and in library
   * yml file, then rebuild cache and reload examples/js_example/weights.
   * Examine the new source to see the reordering.
   *
   * @return array
   *   A renderable array.
   */
  public function getJsWeightImplementation() {
    // Create an array of items with random-ish weight values.
    $weights = array(
      'red' => -4,
      'blue' => -2,
      'green' => -1,
      'brown' => -2,
      'black' => -1,
      'purple' => -5,
    );

    // Start building the content.
    $build = array();
    // Main container DIV. We give it a unique ID so that the JavaScript can
    // find it using jQuery.
    $build['content'] = array(
      '#markup' => '<div id="js-weights"></div>',
    );
    // Attach library containing css and js files.
    $build['#attached']['library'][] = 'js_example/js_example.weights';
    // Attach the weights array to our JavaScript settings. This allows the
    // color scripts we just attached to discover their weight values, by
    // accessing drupalSettings.js_example.js_weights.*color*. The color scripts
    // only use this information for display to the user.
    $build['#attached']['drupalSettings']['js_example']['js_weights'] = $weights;

    return $build;
  }

  /**
   * Accordion page implementation.
   *
   * We're allowing a twig template to define our content in this case,
   * which isn't normally how things work, but it's easier to demonstrate
   * the JavaScript this way.
   *
   * @return array
   *   A renderable array.
   */
  public function getJsAccordionImplementation() {
    $title = t('Click sections to expand or collapse:');
    // Build using our theme. This gives us content, which is not a good
    // practice, but which allows us to demonstrate adding JavaScript here.
    $build['myelement'] = array(
      '#theme' => 'js_example_accordion',
      '#title' => $title,
    );
    // Add our script. It is tiny, but this demonstrates how to add it. We pass
    // our module name followed by the internal library name declared in
    // libraries yml file.
    $build['myelement']['#attached']['library'][] = 'js_example/js_example.accordion';
    // Return the renderable array.
    return $build;
  }

}
