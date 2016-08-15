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

//  $node_detail  = \Drupal\node\Entity\Node::load($nid);
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
//     $start=$node_detail->get($name_date)->getValue()[0]['value'];
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
$start_date = '20160105T191834Z';
// 20160815T122144Z
// DTSTART:20160817T190000-0000
$end_date = '20160105T191834Z';
// $end_date = '20160817T190000Z';
$summary = 'Greenspace Commission Committee Meeting';
$location = '7th floor Phoenix Building, 101 East Vine Street Lexington, Kentucky 40507';
$description = '';

    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers
    // header("Content-Type: application/force-download");
    // header("Content-Disposition: attachment;filename=event.ics");
    echo 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VEVENT
UID:123
DTSTAMP:' . $start_date . '
DTEND:' . $end_date . '
SUMMARY:' . $summary . '
DESCRIPTION: ' . $description . '
LOCATION:' . $location . '
END:VEVENT
END:VCALENDAR';
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
