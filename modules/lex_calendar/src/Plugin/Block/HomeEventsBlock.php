<?php

namespace Drupal\lex_calendar\Plugin\Block;


/**
 * Provides Event list for the home page.
 *
 * @Block(
 *   id = "home_events_block",
 *   admin_label = @Translation("Home Events Block")
 * )
 */
class HomeEventsBlock extends CalendarBlock {

  protected $contentType = 'event';
}
