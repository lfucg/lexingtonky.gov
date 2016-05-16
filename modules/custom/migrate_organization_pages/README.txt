INTRODUCTION
------------
The migrate_example module demonstrates how to implement custom migrations
for Drupal 8. It includes a group of "beer" migrations demonstrating a complete
simple migration scenario.

THE BEER SITE
-------------
In this scenario, we have a beer aficionado site which stores its data in MySQL
tables - there are content items for each beer on the site, user accounts with
profile data, categories to classify the beers, and user-generated comments on
the beers. We want to convert this site to Drupal with just a few modifications
to the basic structure.

To make the example as simple as to run as possible, the source data is placed
in tables directly in your Drupal database - in most real-world scenarios, your
source data will be in an external database. The migrate_example_setup submodule
creates and populates these tables, as well as configuring your Drupal 8 site
(creating a node type, vocabulary, fields, etc.) to receive the data.

STRUCTURE
---------
There are two primary components to this example:

1. Migration configuration, in the config/install directory. These YAML files
   describe the migration process and provide the mappings from the source data
   to Drupal's destination entities.

2. Source plugins, in src/Plugin/migrate/source. These are referenced from the
   configuration files, and provide the source data to the migration processing
   pipeline, as well as manipulating that data where necessary to put it into
   a canonical form for migrations.

UNDERSTANDING THE MIGRATIONS
----------------------------
The YAML and PHP files are copiously documented in-line. To best understand
the concepts described in a more-or-less narrative form, it is recommended you
read the files in the following order:

1. migrate_plus.migration_group.beer.yml
2. migrate.migration.beer_term.yml
3. BeerTerm.php
4. migrate.migration.beer_user.yml
5. BeerUser.php
6. migrate.migration.beer_node.yml
7. BeerNode.php
8. migrate.migration.beer_comment.yml
9. BeerComment.php

RUNNING THE MIGRATIONS
----------------------
The migrate_tools module (https://www.drupal.org/project/migrate_tools) provides
the tools you need to perform migration processes. At this time, the web UI only
provides status information - to perform migration operations, you need to use
the drush commands.

# Enable the tools and the example module if you haven't already.
drush en -y migrate_tools,migrate_example

# Look at the migrations. Just look at them. Notice that they are displayed in
# the order they will be run, which reflects their dependencies. For example,
# because the node migration references the imported terms and users, it must
# run after those migrations have been run.
drush ms               # Abbreviation for migrate-status

# Run the import operation for all the beer migrations.
drush mi --group=beer  # Abbreviation for migrate-import

# Look at what you've done! Also, visit the site and see the imported content,
# user accounts, etc.
drush ms

# Look at the duplicate username message.
drush mmsg beer_user   # Abbreviation for migrate-messages

# Run the rollback operation for all the migrations (removing all the imported
# content, user accounts, etc.). Note that it will rollback the migrations in
# the opposite order as they were imported.
drush mr --group=beer  # Abbreviation for migrate-rollback

# You can import specific migrations.
drush mi beer_term,beer_user
# At this point, go look at your content listing - you'll see beer nodes named
# "Stub", generated from the user's favbeers references.

drush mi beer_node,beer_comment
# Refresh your content listing - the stub nodes have been filled with real beer!

# You can rollback specific migrations.
drush mr beer_comment,beer_node
