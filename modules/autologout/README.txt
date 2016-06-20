CONTENTS OF THIS FILE
---------------------
 * Introduction
 * Requirements
 * Recommended Module
 * Installation
 * Configuration

INTRODUCTION
------------
After a given timeout has passed, users are given a configurable session
expired prompt. They can reset the timeout, logout, or ignore it, in which
case they'll be logged out after the padding time has elapsed.
This is all backed up by a server side logout if JS is disable or bypassed.

REQUIREMENTS
------------
None.

RECOMMENDED MODULES
-------------------
 * Session Limit (https://www.drupal.org/project/session_limit)
 * Password Policy (https://www.drupal.org/project/password_policy)

INSTALLATION
------------
 * Install as usual:
 See https://www.drupal.org/documentation/install/modules-themes/modules-8
 for further information.

CONFIGURATION
-------------
 * Configure permissions at Home >> Administration >> People
   (/admin/people/permissions)
 * Configure Autologout at Home >> Administration >> Configuration >> People
   (/admin/config/people/autologout)
 * Configurable Global timeout and timeout padding.
   The latter determines how much time a user has to respond to the prompt
   and when the server side timeout will occur.
 * Configurable messaging.
 * Configurable redirect url, with the destination automatically appended.
 * Configure which roles will be automatically logged out.
 * Configure if a logout will occur on admin pages.
 * Integration with ui.dialog if available.
   This makes for attractive and more functional dialogs.
 * Configurable timeout based on role.
 * Configurable timeout based on user.
 * Configurable maximum timeout.
   Primarily used when a user has permission to change their timeout value,
   this will be a cap or maximum value they can use.
 * Order of presidence is : user timeout -> lowest role timeout -> global
   timeout.
 * So if a user has a user timeout set, that is their timeout threshold,
   if none is set the lowest timeout value based on all the roles the user
   belongs to is used, if none is set the global timeout is used.
