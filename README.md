# webform_scheduled_export-8.x-dev
Sandbox for the Drupal 8 port of https://www.drupal.org/project/webform_scheduled_export

To install:
 * Drop the module into your modules directory
 * cd to the module directory and run 'composer install'

To use:
 * Go to: /admin/structure/webform/scheduled-export/
 * Add configurations as desired
 * Webform will be exported on cron run.

Currently this only supports SFTP upload as the method of exporting.
