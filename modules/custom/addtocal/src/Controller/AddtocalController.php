<?php

namespace Drupal\addtocal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * controller for the add to calender module.
 */
class AddtocalController extends ControllerBase {

  private function formatLocation($term) {
    return $term->getName() . ', ' .
      $term->get('field_street_number')->getValue()[0]['value'] . ' ' .
      $term->get('field_street')->getValue()[0]['value'] . ' Lexington, KY ' .
      $term->get('field_zip_code')->getValue()[0]['value'];
  }

  public function formatDate($date) {
    return gmdate('Ymd\THis\Z', strtotime($date . 'UTC'));
  }

  /**
   * Function to clear acquia varnish cache.
   */
  public function addtocalics($nid) {
    $node_detail = \Drupal\node\Entity\Node::load($nid);
    if (!$node_detail) { throw new NotFoundHttpException(); }
    $now_date = $this->formatDate('');
    $start_date = $this->formatDate($node_detail->get('field_date')->getValue()[0]['value']);
    $end_date = $node_detail->get('field_date_end')->getValue() ? $node_detail->get('field_date_end')->getValue()[0]['value'] : '';
    $summary = $node_detail->get('title')->getValue()[0]['value'];
    $location = $node_detail->get('field_locations')->referencedEntities() ? $this->formatLocation($node_detail->get('field_locations')->referencedEntities()[0]) : '';
    $uid = $nid . '@' . $_SERVER['HTTP_HOST'];

    $vevent = [
      'BEGIN:VEVENT',
      'UID:' . $uid,
      'DTSTAMP:' . $now_date,
      'DTSTART:' . $start_date,
      'SUMMARY:' . $summary,
      'LOCATION:' . $location,
    ];

    if ($end_date) {
      array_push($vevent, 'DTEND:' . $this->formatDate($end_date));
    }
    array_push($vevent, 'END:VEVENT');

    $vcalendar = [
      'BEGIN:VCALENDAR',
      'VERSION:2.0',
      'PRODID:-//hacksw/handcal//NONSGML v1.0//EN',
    ];

    $event = array_merge($vcalendar, $vevent, ['END:VCALENDAR']);

    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment;filename=event.ics");

    echo join($event, "\r\n");
    exit;
  }
}
