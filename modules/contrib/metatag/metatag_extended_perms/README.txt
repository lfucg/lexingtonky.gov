Metatag Extended Permissions
----------------------------
This add-on for Metatag creates a new permission for each individual meta tag,
allowing for very fine controlled access over meta tag customization.


Usage
--------------------------------------------------------------------------------
* Enable the Metatag Extended Permissions module.
* Assign the appropriate permissions via the admin/people/permisisons page.


Known issues
--------------------------------------------------------------------------------
This module introduces a possibility for dramatically increasing the number of
checkboxes on the permissions page. This can lead to the following problems:
* The permissions admin page or node forms taking a long time to load.
* PHP timeout errors on the permissions admin or node forms pages.
* Out-of-memory errors loading the above.
* The web server not being able to process the permissions form due to hitting
  PHP's max_input_vars limit.

Because of these, it is advised to fully test this module on a copy of a site
before enabling it on production, to help ensure these problems are not
encountered.


Credits / contact
--------------------------------------------------------------------------------
Originally written by Michael Petri [1].


References
--------------------------------------------------------------------------------
1: https://www.drupal.org/u/michaelpetri
