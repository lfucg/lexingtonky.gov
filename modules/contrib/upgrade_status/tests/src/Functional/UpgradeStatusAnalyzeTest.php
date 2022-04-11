<?php

namespace Drupal\Tests\upgrade_status\Functional;

/**
 * Tests analysing sample projects.
 *
 * @group upgrade_status
 */
class UpgradeStatusAnalyzeTest extends UpgradeStatusTestBase {

  public function testAnalyzer() {
    $this->drupalLogin($this->drupalCreateUser(['administer software updates']));
    $this->runFullScan();

    /** @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface $key_value */
    $key_value = \Drupal::service('keyvalue')->get('upgrade_status_scan_results');

    // Check if the project has scan result in the keyValueStorage.
    $this->assertTrue($key_value->has('upgrade_status_test_error'));
    $this->assertTrue($key_value->has('upgrade_status_test_9_compatible'));
    $this->assertTrue($key_value->has('upgrade_status_test_10_compatible'));
    $this->assertTrue($key_value->has('upgrade_status_test_submodules'));
    $this->assertTrue($key_value->has('upgrade_status_test_submodules_with_error'));
    $this->assertTrue($key_value->has('upgrade_status_test_contrib_error'));
    $this->assertTrue($key_value->has('upgrade_status_test_contrib_9_compatible'));
    $this->assertTrue($key_value->has('upgrade_status_test_twig'));
    $this->assertTrue($key_value->has('upgrade_status_test_theme'));
    $this->assertTrue($key_value->has('upgrade_status_test_library'));
    $this->assertTrue($key_value->has('upgrade_status_test_deprecated'));

    // The project upgrade_status_test_submodules_a shouldn't have scan result,
    // because it's a submodule of 'upgrade_status_test_submodules',
    // and we always want to run the scan on root modules.
    $this->assertFalse($key_value->has('upgrade_status_test_submodules_a'));

    $report = $key_value->get('upgrade_status_test_error');
    $this->assertNotEmpty($report);
    $this->assertEquals(5, $report['data']['totals']['file_errors']);
    $this->assertCount(5, $report['data']['files']);
    $file = reset($report['data']['files']);
    $message = $file['messages'][0];
    $this->assertEquals('fatal.php', basename(key($report['data']['files'])));
    $this->assertEquals("Syntax error, unexpected T_STRING on line 3", $message['message']);
    $this->assertEquals(3, $message['line']);
    $file = next($report['data']['files']);
    $this->assertEquals('UpgradeStatusTestErrorController.php', basename(key($report['data']['files'])));
    $message = $file['messages'][0];
    $this->assertEquals("Call to deprecated function upgrade_status_test_contrib_error_function_8_to_9(). Deprecated in drupal:8.6.0 and is removed from drupal:9.0.0. Use the replacement instead.", $message['message']);
    $this->assertEquals(13, $message['line']);
    $file = next($report['data']['files']);
    $this->assertEquals('ExtendingClass.php', basename(key($report['data']['files'])));
    $message = $file['messages'][0];
    $this->assertEquals("Class Drupal\upgrade_status_test_error\ExtendingClass extends deprecated class Drupal\upgrade_status_test_error\DeprecatedBaseClass. Deprecated in drupal:8.8.0 and is removed from drupal:9.0.0. Instead, use so and so. See https://www.drupal.org/project/upgrade_status.", $message['message']);
    $this->assertEquals(10, $message['line']);
    $file = next($report['data']['files']);
    $this->assertEquals('UpgradeStatusTestErrorEntity.php', basename(key($report['data']['files'])));
    $message = $file['messages'][0];
    $this->assertEquals("Configuration entity must define a `config_export` key. See https://www.drupal.org/node/2481909", $message['message']);
    $this->assertEquals(15, $message['line']);
    $file = next($report['data']['files']);
    $this->assertEquals('upgrade_status_test_error.info.yml', basename(key($report['data']['files'])));
    $message = $file['messages'][0];
    $this->assertEquals("Add core_version_requirement: ^8 || ^9 to designate that the extension is compatible with Drupal 9. See https://drupal.org/node/3070687.", $message['message']);
    $this->assertEquals(0, $message['line']);

    // The Drupal 9 compatible test modules are not Drupal 10 compatible.
    $test_9_compatibles = [
      'upgrade_status_test_9_compatible' => '^8 || ^9',
      'upgrade_status_test_contrib_9_compatible' => '^8 || ^9.1',
    ];
    foreach ($test_9_compatibles as $name => $version_requirement) {
      $report = $key_value->get($name);
      $this->assertNotEmpty($report);
      if ($this->getDrupalCoreMajorVersion() < 9) {
        $this->assertEquals(0, $report['data']['totals']['file_errors']);
        $this->assertCount(0, $report['data']['files']);
      }
      else {
        $this->assertEquals(1, $report['data']['totals']['file_errors']);
        $this->assertCount(1, $report['data']['files']);
        $file = reset($report['data']['files']);
        $this->assertEquals($name . '.info.yml', basename(key($report['data']['files'])));
        $message = $file['messages'][0];
        $this->assertEquals("Value of core_version_requirement: $version_requirement is not compatible with the next major version of Drupal core. See https://drupal.org/node/3070687.", $message['message']);
        $this->assertEquals(0, $message['line']);
      }
    }

    // The Drupal 10 compatible test module is also Drupal 9 compatible.
    $report = $key_value->get('upgrade_status_test_10_compatible');
    $this->assertNotEmpty($report);
    $this->assertEquals(0, $report['data']['totals']['file_errors']);
    $this->assertCount(0, $report['data']['files']);

    $report = $key_value->get('upgrade_status_test_contrib_error');
    $this->assertNotEmpty($report);
    $this->assertEquals(5, $report['data']['totals']['file_errors']);
    $this->assertCount(2, $report['data']['files']);
    $file = reset($report['data']['files']);
    $this->assertEquals('UpgradeStatusTestContribErrorController.php', basename(key($report['data']['files'])));
    $message = $file['messages'][0];
    $this->assertEquals("Call to deprecated function upgrade_status_test_contrib_error_function_8_to_9(). Deprecated in drupal:8.6.0 and is removed from drupal:9.0.0. Use the replacement instead.", $message['message']);
    $this->assertEquals(13, $message['line']);
    $this->assertEquals('old', $message['upgrade_status_category']);
    $message = $file['messages'][1];
    $this->assertEquals("Call to deprecated function upgrade_status_test_contrib_error_function_8_to_10(). Deprecated in drupal:8.6.0 and is removed from drupal:10.0.0. Use the replacement instead.", $message['message']);
    $this->assertEquals(14, $message['line']);
    $this->assertEquals($this->getDrupalCoreMajorVersion() < 9 ? 'ignore' : 'old', $message['upgrade_status_category']);
    $message = $file['messages'][2];
    $this->assertEquals("Call to deprecated function upgrade_status_test_contrib_error_function_9_to_10(). Deprecated in drupal:9.4.0 and is removed from drupal:10.0.0. Use the replacement instead.", $message['message']);
    $this->assertEquals(15, $message['line']);
    $this->assertEquals($this->getDrupalCoreMajorVersion() < 9 ? 'ignore' : 'later', $message['upgrade_status_category']);
    $message = $file['messages'][3];
    $this->assertEquals("Call to deprecated function upgrade_status_test_contrib_error_function_9_to_11(). Deprecated in drupal:9.0.0 and is removed from drupal:11.0.0. Use the replacement instead.", $message['message']);
    $this->assertEquals(16, $message['line']);
    $this->assertEquals('ignore', $message['upgrade_status_category']);
    $file = next($report['data']['files']);
    $this->assertEquals('upgrade_status_test_contrib_error.info.yml', basename(key($report['data']['files'])));
    $message = $file['messages'][0];
    $this->assertEquals("Add core_version_requirement: ^8 || ^9 to designate that the extension is compatible with Drupal 9. See https://drupal.org/node/3070687.", $message['message']);
    $this->assertEquals(0, $message['line']);
    $this->assertEquals('uncategorized', $message['upgrade_status_category']);

    // On at least Drupal 9, these modules will not be ready for the next major.
    $base_info_error = (int) ($this->getDrupalCoreMajorVersion() >= 9);

    $report = $key_value->get('upgrade_status_test_twig');
    $this->assertNotEmpty($report);

    if ($this->getDrupalCoreMajorVersion() >= 9) {
      $this->assertEquals(5, $report['data']['totals']['file_errors']);
      $this->assertCount(3, $report['data']['files']);

      $file = array_shift($report['data']['files']);
      $this->assertEquals('The spaceless tag in "modules/contrib/upgrade_status/tests/modules/upgrade_status_test_twig/templates/spaceless.html.twig" at line 2 is deprecated since Twig 2.7, use the "spaceless" filter with the "apply" tag instead. See https://drupal.org/node/3071078.', $file['messages'][0]['message']);
    }
    else {
      $this->assertEquals(3, $report['data']['totals']['file_errors']);
      $this->assertCount(1, $report['data']['files']);
    }

    $file = array_shift($report['data']['files']);
    $this->assertEquals('Twig Filter "deprecatedfilter" is deprecated. See https://drupal.org/node/3071078.', $file['messages'][0]['message']);
    $this->assertEquals(10, $file['messages'][0]['line']);
    $this->assertEquals('Template is attaching a deprecated library. The "upgrade_status_test_library/deprecated_library" asset library is deprecated for testing.', $file['messages'][1]['message']);
    $this->assertEquals(1, $file['messages'][1]['line']);
    $this->assertEquals('Template is attaching a deprecated library. The "upgrade_status_test_twig/deprecated_library" asset library is deprecated for testing.', $file['messages'][2]['message']);
    $this->assertEquals(2, $file['messages'][2]['line']);

    $report = $key_value->get('upgrade_status_test_theme');
    $this->assertNotEmpty($report);
    $this->assertEquals(5 + $base_info_error, $report['data']['totals']['file_errors']);
    $this->assertCount(3 + $base_info_error, $report['data']['files']);
    $file = reset($report['data']['files']);
    foreach ([0 => 2, 1 => 4] as $index => $line) {
      $message = $file['messages'][$index];
      $this->assertEquals('Twig Filter "deprecatedfilter" is deprecated. See https://drupal.org/node/3071078.', $message['message']);
      $this->assertEquals($line, $message['line']);
    }
    $file = next($report['data']['files']);
    $this->assertEquals('Theme is overriding a deprecated library. The "upgrade_status_test_library/deprecated_library" asset library is deprecated for testing.', $file['messages'][0]['message']);
    $this->assertEquals(0, $file['messages'][0]['line']);
    $this->assertEquals('Theme is extending a deprecated library. The "upgrade_status_test_twig/deprecated_library" asset library is deprecated for testing.', $file['messages'][1]['message']);
    $this->assertEquals(0, $file['messages'][1]['line']);
    $file = next($report['data']['files']);
    $this->assertEquals('The theme is overriding the "upgrade_status_test_theme_function_theme_function_override" theme function. Theme functions are deprecated. For more info, see https://www.drupal.org/node/2575445.', $file['messages'][0]['message']);
    $this->assertEquals(6, $file['messages'][0]['line']);
    // @see https://www.drupal.org/project/upgrade_status/issues/3219968 base theme cannot be tested practically.
    /*$file = next($report['data']['files']);
    $this->assertEquals('upgrade_status_test_theme.info.yml', basename(key($report['data']['files'])));
    $message = $file['messages'][0];
    $this->assertEquals("The now required 'base theme' key is missing. See https://www.drupal.org/node/3066038.", $message['message']);
    $this->assertEquals(0, $message['line']);*/

    $report = $key_value->get('upgrade_status_test_theme_functions');
    $this->assertNotEmpty($report);
    $this->assertEquals(3 + $base_info_error, $report['data']['totals']['file_errors']);
    $this->assertCount(1 + $base_info_error, $report['data']['files']);
    $file = reset($report['data']['files']);
    $this->assertEquals('The module is defining "upgrade_status_test_theme_function" theme function. Theme functions are deprecated. For more info, see https://www.drupal.org/node/2575445.', $file['messages'][0]['message']);
    $this->assertEquals(9, $file['messages'][0]['line']);
    $this->assertEquals('The module is defining "upgrade_status_test_theme_function" theme function. Theme functions are deprecated. For more info, see https://www.drupal.org/node/2575445.', $file['messages'][1]['message']);
    $this->assertEquals(20, $file['messages'][1]['line']);
    $this->assertEquals('The module is defining an unknown theme function. Theme functions are deprecated. For more info, see https://www.drupal.org/node/2575445.', $file['messages'][2]['message']);
    $this->assertEquals(21, $file['messages'][2]['line']);

    $report = $key_value->get('upgrade_status_test_library');
    $this->assertNotEmpty($report);
    $this->assertEquals(4 + $base_info_error, $report['data']['totals']['file_errors']);
    $this->assertCount(2 + $base_info_error, $report['data']['files']);
    $file = reset($report['data']['files']);
    $this->assertEquals("The 'library' library is depending on a deprecated library. The \"upgrade_status_test_library/deprecated_library\" asset library is deprecated for testing.", $file['messages'][0]['message']);
    $this->assertEquals(0, $file['messages'][0]['line']);
    $this->assertEquals("The 'library' library is depending on a deprecated library. The \"upgrade_status_test_twig/deprecated_library\" asset library is deprecated for testing.", $file['messages'][1]['message']);
    $this->assertEquals(0, $file['messages'][1]['line']);
    $file = $report['data']['files'][array_keys($report['data']['files'])[1]];
    $this->assertEquals('The referenced library is deprecated. The "upgrade_status_test_library/deprecated_library" asset library is deprecated for testing.', $file['messages'][0]['message']);
    $this->assertEquals(8, $file['messages'][0]['line']);
    $this->assertEquals('The referenced library is deprecated. The "upgrade_status_test_twig/deprecated_library" asset library is deprecated for testing.', $file['messages'][1]['message']);
    $this->assertEquals(10, $file['messages'][1]['line']);

    $report = $key_value->get('upgrade_status_test_library_exception');
    $this->assertNotEmpty($report);
    $this->assertEquals(1 + $base_info_error, $report['data']['totals']['file_errors']);
    $this->assertCount(1 + $base_info_error, $report['data']['files']);
    $file = reset($report['data']['files']);
    $this->assertEquals("Incomplete library definition for definition 'library_exception' in extension 'upgrade_status_test_library_exception'", $file['messages'][0]['message']);

    // Module upgrade_status_test_submodules_with_error_a shouldn't have scan
    // result, but its info.yml errors should appear in its parent scan.
    $this->assertFalse($key_value->has('upgrade_status_test_submodules_with_error_a'));
    $report = $key_value->get('upgrade_status_test_submodules_with_error');
    $this->assertNotEmpty($report);
    $this->assertEquals(2, $report['data']['totals']['file_errors']);
    $this->assertCount(2, $report['data']['files']);

    $report = $key_value->get('upgrade_status_test_deprecated');
    $this->assertNotEmpty($report);
    $this->assertEquals(1, $report['data']['totals']['file_errors']);
    $this->assertCount(1, $report['data']['files']);
    $file = reset($report['data']['files']);
    $this->assertEquals("This extension is deprecated. Don't use it. See https://drupal.org/project/upgrade_status.", $file['messages'][0]['message']);
  }

}
