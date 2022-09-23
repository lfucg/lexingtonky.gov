# IMCE FILE MANAGER

## CONTENTS OF THIS FILE


 * Introduction
 * Requirements
 * Recommended modules
 * Installation
 * CKeditor integration
 * BUeditor integration
 * File/Image field integration
 * Maintainers


## INTRODUCTION

Imce is an ajax based file manager that supports personal folders.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/imce

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/imce


## REQUIREMENTS

This module requires no modules outside of Drupal core.


## RECOMMENDED MODULES

 * BUEditor - https://www.drupal.org/project/bueditor


## INSTALLATION

 * Install the IMCE module as you would normally install a contributed
   Drupal module. Visit https://www.drupal.org/node/1897420 for further
   information.

## MENU INTEGRATION

 * Create a custom menu item with /imce path

## CKEDITOR INTEGRATION

    1. Go to Administration > Configuration >
       Content Authoring > Text formats and editors >
       and edit a text format that uses CKEditor.
    2. Enable CKEditor image button without image uploads.

Image uploads must be disabled in order for IMCE link appear in the image
dialog. There is also an image button provided by Imce but it can't be used for
editing existing images.


## BUEDITOR INTEGRATION

    1. Edit your editor at /admin/config/content/bueditor.
    2. Select Imce File Manager as the File browser under Settings.


## FILE/IMAGE FIELD INTEGRATION

    1. Go to form settings of your content type.
       Ex: /admin/structure/types/manage/article/form-display
    2. Edit widget settings of a file/image field.
    3. Check the box saying "Allow users to select files from Imce File Manager
       for this field." Save.
    4. You should now see the "Open File Browser" link above the upload widget
       in the content form.

## Tests

* Before of run tests you needs create a shortcut for
core/phpunit.xml.dist in your root project.

### Executing UnitTest

```
vendor/bin/phpunit modules/imce
```

### Executing KernelTest with Lando

```
lando php core/scripts/run-tests.sh --php /usr/local/bin/php --url http://example.lndo.site --dburl mysql://drupal8:drupal8@database/drupal8 --sqlite simpletest.sqlite --module imce --verbose --color
```

## MAINTAINERS

 * ufku - https://www.drupal.org/user/9910 - https://git.drupalcode.org/ufku
 * thalles - https://www.drupal.org/user/3589086 - https://git.drupalcode.org/thallesvf
