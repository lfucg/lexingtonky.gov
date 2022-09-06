<?php

/**
 * @file
 * Post updates.
 */

/**
 * Uninstall search_api_solr_defaults.
 */
function search_api_solr_defaults_post_update_unistall_module() {
  if (\Drupal::moduleHandler()->moduleExists('search_api_solr_defaults')) {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller */
    $moduleInstaller = \Drupal::service('module_installer');
    $moduleInstaller->uninstall('search_api_solr_defaults');
  }
}
