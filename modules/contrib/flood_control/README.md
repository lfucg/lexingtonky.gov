# Flood Control
Flood Control provides an interface for hidden flood control variables (e.g.
login attempt limiters) and makes it possible for site administrators to
remove IP addresses and user ID's from the flood table.

## Installation
* [Download and install the module as usual](https://www.drupal.org/docs/extending-drupal/installing-modules)
* Manage the flood table at People > Flood Unblock
  (/admin/people/flood-unblock)

## Configure flood settings
* Navigate to _Configuration > People > Flood Control_
  (/admin/config/people/flood-control)
* Change the desired settings

## Manage flood table
Drupal prevents brute force attacks on accounts. It does so by refusing login
attempts when more than 5 attempts failed. The amount of failed logins is
recorded in the table 'flood'.

Flood Unblock provides an interface that makes it possible for site
administrators to remove IP addresses and user ID's from the flood table.

You need _access flood unblock_ permissions to access the _Flood Unblock_ page.

### Steps
* In the _Manage_ administrative menu, navigate to _People_ (/admin/people)
* Click the tab _Flood Unblock_ . The Flood Unblock page appears
* Select (all) the IP addresses and User ID's that you want to unblock
* Click _Remove_. The IP addresses and User ID's have been unblocked and it
  should be possible again to try to login
