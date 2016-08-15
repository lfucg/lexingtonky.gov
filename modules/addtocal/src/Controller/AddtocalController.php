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
  public function addtocalics($nid, $cal, $body, $add) {

 $node_detail  = \Drupal\node\Entity\Node::load($nid);

 $entity_type_id='node';
 $bundle=$node_detail->bundle();

 foreach (\Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle) as $field_name => $field_definition) {
    if (!empty($field_definition->getTargetBundle())) {
      $bundleFields[$entity_type_id][$field_name]['type'] = $field_definition->getType();
      $bundleFields[$entity_type_id][$field_name]['name'] = $field_definition->getname();
      $bundleFields[$entity_type_id][$field_name]['label'] = $field_definition->getLabel();
      if($bundleFields[$entity_type_id][$field_name]['type'] == 'datetime'){
        //array_push($dec_option, $bundleFields[$entity_type_id][$field_name]['label']);
        $name_date = $bundleFields[$entity_type_id][$field_name]['name'];
        //$dec_option[$label] = $label;

      }
      if($bundleFields[$entity_type_id][$field_name]['label']==$body){
        $name_body = $bundleFields[$entity_type_id][$field_name]['name'];
      }

      if($bundleFields[$entity_type_id][$field_name]['label']==$add){
        $name_add = $bundleFields[$entity_type_id][$field_name]['name'];
      }



      }
    }

    $start=$node_detail->get($name_date)->getValue()[0]['value'];
    $description=$node_detail->get($name_body)->getValue()[0]['value'];
    $description = html_entity_decode($description);
    $location=$node_detail->get($name_add)->getValue()[0]['value'];

    $url=$entity_type_id.'/'.$nid;


  $end = $start;
  $start_timestamp = strtotime($start . 'UTC');
  $end_timestamp = strtotime($end . 'UTC');

  $diff_timestamp = $end_timestamp - $start_timestamp;

  $start_date = gmdate('Ymd', $start_timestamp) . 'T' . gmdate('His', $start_timestamp) . 'Z';
  $local_start_date = date('Ymd', $start_timestamp) . 'T' . date('His', $start_timestamp) . '';
  $end_date = gmdate('Ymd', $end_timestamp) . 'T' . gmdate('His', $end_timestamp) . 'Z';
  $local_end_date = date('Ymd', $end_timestamp) . 'T' . date('His', $end_timestamp) . '';

  $diff_hours = str_pad(round(($diff_timestamp / 60) / 60), 2, '0', STR_PAD_LEFT);
  $diff_minutes = str_pad(abs(round($diff_timestamp / 60) - ($diff_hours * 60)), 2, '0', STR_PAD_LEFT);

  $duration = $diff_hours . $diff_minutes;

  // kint($duration);
  // die;


if($cal=='ical'){
  //return new Response('Click on iCalendar');
    //exit;

}
if($cal=='outl'){
  //return new Response('Click on Outlook');
    //exit;

}


    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers 
    header("Content-Type: application/force-download");
    echo 'BEGIN:VCALENDAR
  VERSION:2.0
  PRODID:-//hacksw/handcal//NONSGML v1.0//EN
  BEGIN:VEVENT
  UID:' . $entity_type . '-' . $entity_id . '@' . $_SERVER['HTTP_HOST'] .'
  DTSTAMP:' . $start_date . '
  DTSTART:' . $start_date . '
  DTEND:' . $end_date . '
  SUMMARY:' . 'title' . '
  DESCRIPTION: ' . $description . '
  LOCATION:' . $location . '
  END:VEVENT
  END:VCALENDAR';
    exit;
  }

}
