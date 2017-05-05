<?php

namespace Drupal\lex_calendar\Plugin\Block;


/**
 * Provides Event list for the home page.
 *
 * @Block(
 *   id = "home_meetings_block",
 *   admin_label = @Translation("Home Meetings Block")
 * )
 */
class HomeMeetingBlock extends CalendarBlock {

  protected $contentType = 'meeting';
}
