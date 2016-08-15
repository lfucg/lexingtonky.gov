<?php

namespace Drupal\addtocal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity;


/**
 * controller for the add to calender module.
 */
class AddtocalController extends ControllerBase {
  /**
   * Function to reload previous page.
   */
  public function loadPage() {
    $request = \Drupal::request();
    return $request->server->get('HTTP_REFERER');
  }

  /**
   * Function to clear acquia varnish cache.
   */
  public function addtocalics() {

    $nid = 904;
    $node_detail = \Drupal\node\Entity\Node::load($nid);
//
//  $entity_type_id='node';
//  $bundle=$node_detail->bundle();
//
//  foreach (\Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle) as $field_name => $field_definition) {
//     if (!empty($field_definition->getTargetBundle())) {
//       $bundleFields[$entity_type_id][$field_name]['type'] = $field_definition->getType();
//       $bundleFields[$entity_type_id][$field_name]['name'] = $field_definition->getname();
//       $bundleFields[$entity_type_id][$field_name]['label'] = $field_definition->getLabel();
//       if($bundleFields[$entity_type_id][$field_name]['type'] == 'datetime'){
//         //array_push($dec_option, $bundleFields[$entity_type_id][$field_name]['label']);
//         $name_date = $bundleFields[$entity_type_id][$field_name]['name'];
//         //$dec_option[$label] = $label;
//
//       }
//       if($bundleFields[$entity_type_id][$field_name]['label']==$body){
//         $name_body = $bundleFields[$entity_type_id][$field_name]['name'];
//       }
//
//       if($bundleFields[$entity_type_id][$field_name]['label']==$add){
//         $name_add = $bundleFields[$entity_type_id][$field_name]['name'];
//       }
//
//
//
//       }
//     }
//
    // $start=$node_detail->get($name_date)->getValue()[0]['value'];
//     $description=$node_detail->get($name_body)->getValue()[0]['value'];
//     $description = html_entity_decode($description);
//     $location=$node_detail->get($name_add)->getValue()[0]['value'];
//
//     $url=$entity_type_id.'/'.$nid;
//
//
//   $end = $start;
//   $start_timestamp = strtotime($start . 'UTC');
//   $end_timestamp = strtotime($end . 'UTC');
//
//   $diff_timestamp = $end_timestamp - $start_timestamp;
//
//   $start_date = gmdate('Ymd', $start_timestamp) . 'T' . gmdate('His', $start_timestamp) . 'Z';

//   $local_start_date = date('Ymd', $start_timestamp) . 'T' . date('His', $start_timestamp) . '';
//   $end_date = gmdate('Ymd', $end_timestamp) . 'T' . gmdate('His', $end_timestamp) . 'Z';
//   $local_end_date = date('Ymd', $end_timestamp) . 'T' . date('His', $end_timestamp) . '';
//
//   $diff_hours = str_pad(round(($diff_timestamp / 60) / 60), 2, '0', STR_PAD_LEFT);
//   $diff_minutes = str_pad(abs(round($diff_timestamp / 60) - ($diff_hours * 60)), 2, '0', STR_PAD_LEFT);
//
//   $duration = $diff_hours . $diff_minutes;
//
//   // kint($duration);
//   // die;
//
//
// if($cal=='ical'){
//   //return new Response('Click on iCalendar');
//     //exit;
//
// }
// if($cal=='outl'){
//   //return new Response('Click on Outlook');
//     //exit;
//
// }

// Cache-Control: private
// Content-Disposition: attachment;filename=event.ics
// Content-Type: text/plain; charset=utf-8
// Date: Mon, 15 Aug 2016 15:07:31 GMT
// Server: Microsoft-IIS/6.0
// Transfer-Encoding: chunked
// X-AspNet-Version: 1.1.4322
// X-Powered-By: ASP.NET

    $start_date = $node_detail->get('field_date')->getValue()[0]['value'];
    $start_date = gmdate('Ymd\THis\Z', strtotime($start_date . 'UTC'));

    $end_date = $node_detail->get('field_date_end')->getValue()[0]['value'];
    if ($end_date) {
      $end_date = gmdate('Ymd\THis\Z', strtotime($end_date . 'UTC'));
    }

    $summary = $node_detail->get('title')->getValue()[0]['value'];
    $location = $node_detail->get('field_locations')->referencedEntities()[0]->getName();
    $description = $node_detail->get('body')->getValue()[0]['value'];;
    $uid = '213';

    $vevent = [
      'BEGIN:VEVENT',
      'UID:' . $uid,
      'DTSTAMP:' . $start_date,
      'SUMMARY:' . $summary,
      'DESCRIPTION:' . $description,
      'LOCATION:' . $location,
      'END:VEVENT',
    ];

    if ($end_date) {
      array_push($vevent, 'DTEND:' . $end_date);
    }

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
    // header("Content-Type: application/force-download");
    // header("Content-Disposition: attachment;filename=event.ics");

    echo join($event, "\r\n");
    exit;
  }
// BEGIN:VCALENDAR
// VERSION:2.0
// METHOD:PUBLISH
// BEGIN:VEVENT
// DTSTAMP:20160105T191834-0000
// DTSTART:20160817T190000-0000
// DTEND:20160817T190000-0000
// SUMMARY:Greenspace Commission Committee Meeting
// DESCRIPTION:
// UID:17705
// LOCATION:7th floor Phoenix Building, 101 East Vine Street Lexington, Kentucky 40507
// CLASS:PUBLIC
// END:VEVENT
// END:VCALENDAR

// validated:

// BEGIN:VCALENDAR
// VERSION:2.0
// PRODID:0239483409
// BEGIN:VEVENT
// DTSTAMP:20160815T122144Z
// DTEND:20160815T122144Z
// SUMMARY:Greenspace Commission Committee Meeting
// DESCRIPTION:
// UID:17705
// LOCATION:7th floor Phoenix Building, 101 East Vine Street Lexington, Kentucky 40507
// CLASS:PUBLIC
// END:VEVENT
// END:VCALENDAR



}
