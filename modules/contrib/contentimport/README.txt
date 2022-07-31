SUMMARY - Content Import
========================
To import content for content type from CSV file

REQUIREMENTS
------------

This module requires the following:
A CSV file for content type to import.
The .csv file can have two or more columns,
eg:- title, machine_name1, machine_name2,machine_name3...
The first row should be machine name for the content type
and the following rows will be taken as data.
Refer the example given in the module folder CSV_article.csv file

INSTALLATION
-------------
Install this module as usual. Please see
http://drupal.org/documentation/install/modules-themes/modules-8

CONFIGURATION
-------------

After successfully installing the module contentimport, 
you can import data for the selected content type via 
file import.

Install the module contentimport.

Go to Configuration and select Content Import from 
Content Authoring.
 
It will redirect you to Content Import Form, with two 
fields: Content Type and Import file.

Select the Content Type, this will have all the content types
available in the application

Before choosing csv file, check weather the first row contains 
all the machine names from the content type

The file should be CSV file.

Please give write permission for your sites/default/files/ folder 
to write the log file.

If the content type having image fields, upload all the images 
in public://<content_type>/images/ folder before importing 
the CSV file(IMCE module will be helpful for this)

Put the image name in the respective image column.

Mandatory Columns is CSV:
=========================

title - Title of the node
langcode - By default it should be 'en' for english and 
		you can define the langcode created by you.

Field Mapping:
=============

For Image field - upload all the images
	in public://<content_type>/images/ folder before importing
	the CSV file(IMCE module will be helpful for this).

For Entity Reference field(Taxonomy) - put the data as Vocabulary: term1, term2
	If the Vocabulary or Term is not exists it will create automatically.
	If a field has multiple vocabularies as a target in field settings, you must
	do this:
	Eg: fruits:apple,orange
	Otherwise, if there is a single target, you must omit the vocabulary name:
	Eg: apple,orange,pear,banana

For Entity Reference field(Users) - put the user's email address 
	comma separated 
	If the User is not exists it will create automatically.
	Eg: user1@d8.com,user2@d8.com

For Entity Reference field(Content) - put the title of the node 
	colon separated 
	Eg: title1:title2:title3	

For Boolean field - Put On/on/Yes/yes to check the field and Off/No to uncheck 
	the	field.

For Date field - Put the data in m/d/Y h:m:i format or if you want the date only
	please put the data in m/d/y format.

For Timestamp field - Put the timestamp, the system will convert and store 
	the date.

For Geolocation field - Put the latitude and longitude values with , separated
	If the field having mulitple values, put the data with ; as delimiter
	Eg: lat1,long1
		lat2,long2;lat3,long3
		lat4,long4;lat5,long5;lat6,long6
		lat7,long7

For Geo field - Put the latitude and longitude values with , separated
	If the field having mulitple values, put the data with ; as delimiter
	Eg: lat1,long1
		lat2,long2;lat3,long3
		lat4,long4;lat5,long5;lat6,long6
		lat7,long7

For List (String) - Put the latitude and longitude values with , separated
	If the field having mulitple values, put the data with , as delimiter
	Eg: mango,banana

For Author - add column with key "author" in CSV and put the user names under 
  this column, system will be adding the author information when the import
  happens.

Check the attached CSV file for Sample.

Click on Import which redirects you to admin/content
