[![Build Status](https://travis-ci.org/grasmash/drupal-security-warning.svg?branch=master)](https://travis-ci.org/grasmash/drupal-security-warning)

This Composer plugin will display a warning when users install or update Drupal packages that are not supported by the Drupal Security team, as per the [Security Advisory Policy](https://www.drupal.org/security-advisory-policy).

Installing or updating a "non-covered" Drupal package will displayed:

    You are using Drupal packages that are not supported by the Drupal Security Team!
      - drupal/consumers:1.0.0.0-beta1: Project has not opted into security advisory coverage!
      - drupal/inline_entity_form:1.0.0.0-beta1: Beta releases are not covered by Drupal security advisories.
      - drupal/scheduled_updates:1.0.0.0-alpha6: Project has not opted into security advisory coverage!
      - drupal/diff:1.0.0.0-RC1: RC releases are not covered by Drupal security advisories.
      - drupal/seckit:1.0.0.0-alpha2: Alpha releases are not covered by Drupal security advisories.
      - drupal/security_review:dev-1.x: Dev releases are not covered by Drupal security advisories.
    See https://www.drupal.org/security-advisory-policy for more information.