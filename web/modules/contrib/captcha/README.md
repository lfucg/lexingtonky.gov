CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Conflicts/Known issues
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

A CAPTCHA is a challenge-response test most often placed within web forms to
determine whether the user is human. The purpose of CAPTCHA is to block form
submissions by spambots, which are automated scripts that post spam content
everywhere they can. The CAPTCHA module provides this feature to virtually any
user facing web form on a Drupal site.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/captcha

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/captcha


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


CONFLICTS/KNOWN ISSUES
----------------------

CAPTCHA and page caching do not work together currently. However, the CAPTCHA
module does support the Drupal core page caching mechanism: it just disables the
caching of the pages where it has to put its challenges.

If you use other caching mechanisms, it is possible that CAPTCHA's won't work,
and you get error messages like 'CAPTCHA validation error: unknown CAPTCHA
session ID'.



INSTALLATION
------------

 * Install the CAPTCHA module as you would normally install a contributed
   Drupal module. Visit https://www.drupal.org/node/1897420 for further
   information.

The configuration page is at admin/config/people/captcha,
  where you can configure the CAPTCHA module
  and enable challenges for the desired forms.
  You can also tweak the image CAPTCHA to your liking.

CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Navigate to Administration > Configuration > People > Captcha module
       settings to administer how and when Captcha is used.
    3. Select the challenge type you want for each of the listed forms.
    4. Select " Add a description to the CAPTCHA" to add a configurable
       description to explain the purpose of the CAPTCHA to the visitor.
    5. For Default CAPTCHA validation, define how the response should be
       processed by default. Note that the modules that provide the actual
       challenges can override or ignore this.
    6. Save configuration.


MAINTAINERS
-----------

   * Fabiano Sant'Ana (wundo) - https://www.drupal.org/u/wundo
   * Andrii Podanenko (podarok) - https://www.drupal.org/u/podarok
   * soxofaan - https://www.drupal.org/u/soxofaan
   * Lachlan Ennis (elachlan) - https://www.drupal.org/u/elachlan
   * Rob Loach (RobLoach) - https://www.drupal.org/u/robloach

Supporting organizations:

 * Chuva Inc. - https://www.drupal.org/chuva-inc

DEVELOPMENT
-------------
  You can disable captcha in your local or test environment by adding the
  following line to settings.php:
  $settings['disable_captcha'] = TRUE;
