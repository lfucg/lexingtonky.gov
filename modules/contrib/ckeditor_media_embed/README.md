# CKEditor Media Embed Plugin

A module that adds support for CKEditor plugins Media Embed,
Semantic Media Embed, and Auto Embed to Drupal core's CKEditor.

## Installation

Install the module per normal https://www.drupal.org/documentation/install/modules-themes/modules-8
then follow the instructions for installing the CKEditor plugins below.

### Install CKEditor plugins

#### Easiest

1. [Install Drupal Console](https://github.com/hechoendrupal/DrupalConsole#installing-drupal-console).
2. Enable [CKEditor media embed](https://www.drupal.org/project/ckeditor_media_embed) module.
3. Run `drupal ckeditor_media_embed:install`.

#### Harder

1. Download the [Full "dev" package for CKEditor](https://github.com/ckeditor/ckeditor-dev/archive/latest.zip).
2. Unzip the package and place its contents into
   `DRUPAL_ROOT/libraries/ckeditor`.
3. Clear the cache

#### Hardest

1. Download the following plugins:

  * [Media Embed](http://ckeditor.com/addon/embed)
  * [Media Semantic Embed](http://ckeditor.com/addon/embedsemantic)
  * [Media Embed Base](http://ckeditor.com/addon/embedbase)
  * [Auto Embed](http://ckeditor.com/addon/autoembed)
  * [Auto Link](http://ckeditor.com/addon/autolink)
  * [Notification](http://ckeditor.com/addon/notification)
  * [Notification Aggregator](http://ckeditor.com/addon/notificationaggregator)
  * [Text Match](https://ckeditor.com/cke4/addon/textmatch) (As of CKEditor 4.11)

2. Unzip and place the contents for each plugin in the the following directory:

  * `DRUPAL_ROOT/libraries/ckeditor/plugins/PLUGIN_NAME`

3. Clear the cache

## Configuration

* Install and enable [CKEditor media embed](https://www.drupal.org/project/ckeditor_media_embed) module.

### WYSIWYG

* Go to the 'Text formats and editors' configuration page:
  `/admin/config/content/formats`, and for each text format/editor combo where
  you want to embed URLs, do the following:
  * Drag and drop the 'Media Embed' or the 'Semantic Media Embed' button into
    the Active toolbar.
  * If the text format uses the
    'Limit allowed HTML tags and correct faulty HTML' filter, use the
    'Semantic Media Embed' and read the instructions for the
    'Semantic Media Embed' below.

#### Semantic Media Embed

If you are using the 'Semantic Media Embed' button be sure to do the following:
* Enable the 'Convert Oembed tags to media embeds' filter.
* If the text format uses the 'Limit allowed HTML tags and correct faulty HTML' filter, add ```<oembed>``` to the 'Allowed HTML tags' field. (This should happen automatically however, in some cases it does not. See https://www.drupal.org/node/2689083.)

### Field formatter

The field formatter allows link fields to be rendered via the configured oembed
service provider.

* Navigate to "Manage display" for the content type, after adding a "Link"
  field.
* Select the "Oembed element using CKEditor Media Embed provider" format for
  the link field you wish.


## Additional plugins

This module also includes all additional non-core depdencies for the Media
Embed CKEditor plugin.

* [Media Embed](http://ckeditor.com/addon/embed)
* [Media Semantic Embed](http://ckeditor.com/addon/embedsemantic)
* [Media Embed Base](http://ckeditor.com/addon/embedbase)
* [Auto Embed](http://ckeditor.com/addon/autoembed)
* [Auto Link](http://ckeditor.com/addon/autolink)
* [Notification](http://ckeditor.com/addon/notification)
* [Notification Aggregator](http://ckeditor.com/addon/notificationaggregator)
* [Text Match](https://ckeditor.com/cke4/addon/textmatch)
