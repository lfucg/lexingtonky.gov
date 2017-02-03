<?php

namespace Drupal\lex_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Standard Drupal slim controller for echoing simple pages.
 */
class PageController extends ControllerBase {

  /**
   *
   */
  public function eventPage() {
    return [
      '#type' => 'markup',
      '#theme' => 'calendar_page',
      '#title' => $this->t('City events calendar'),
      '#fc_callback' => 'fetchEvents',
      '#attached' => [
        'library' => [
          'lex_calendar/lex_calendar',
        ],
      ],
    ];
  }

  /**
   *
   */
  public function meetingPage() {
    return [
      '#type' => 'markup',
      '#theme' => 'calendar_page',
      '#title' => $this->t('City meetings calendar'),
      '#fc_callback' => 'fetchMeetings',
      '#attached' => [
        'library' => [
          'lex_calendar/lex_calendar',
        ],
      ],
    ];
  }

}
