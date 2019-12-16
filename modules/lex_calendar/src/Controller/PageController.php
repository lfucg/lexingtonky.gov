<?php

namespace Drupal\lex_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Standard Drupal slim controller for echoing simple pages.
 */
class PageController extends ControllerBase {

  /**
   * Responder for /calender/events.
   */
  public function eventPage() {
    return [
      '#type' => 'markup',
      '#theme' => 'calendar_page',
      '#title' => $this->t('City Calendar'),
      '#fc_callback' => 'fetchEventsAndMeetings',
      '#attached' => [
        'library' => [
          'lex_calendar/lex_calendar',
        ],
      ],
    ];
  }

  /**
   * Responder for /calendar/meetings.
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
