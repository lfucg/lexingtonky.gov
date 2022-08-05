# Twig VarDumper for Drupal 9

Provides a way to display Twig PHP variables in a pretty way.

Twig VarDumper provides a better `{{ dump() }}` and `{{ vardumper() }}` function that can help you debug Twig variables.

By default, the module display the var_dump output, just like the other common debugging mode.

Make sure to have the required Symfony libraries to get this module working.

See the examples below on how to use it, it's very easy to use.

## Installation

The module is relying on the VarDumper and http-foundation components of the Symfony project.
There easiest way to install this module is with composer. Here are the commands to run:

* `composer config repositories.drupal composer https://packages.drupal.org/9`
* `composer require drupal/twig_vardumper`
* `drush en twig_vardumper -y`
* Once the module and/or the submodules are enabled, don't forget to check for the new user permissions.

## How to use

Enable the module twig_vardumper then (e.g., page.html.twig)...

    <header class="header-mediador">
      {{ page.header }}
    </header>

    {{ dump(page.content) }}
    {{ vardumper(page.content) }}

    {{ page.content }}

    <footer class="seccion-footer">
      {{ page.footer }}
    </footer>

## Related modules

* Twig Tweak: with drupal_dump() etc...

## Related documentation

* https://www.drupal.org/docs/8/theming/twig/debugging-twig-templates
* https://front.id/en/articles/drupal-template-helper
* https://www.keopx.net/blog/drupal-template-helper-para-drupal-8 (Spanish)
