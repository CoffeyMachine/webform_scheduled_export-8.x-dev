<?php

/**
 * @file
 * Definition of Drupal\webform_scheduled_export\Entity\WebformScheduledExport.
 */

namespace Drupal\webform_scheduled_export\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the webform_scheduled_export entity.
 *
 * @ConfigEntityType(
 *   id = "webform_scheduled_export",
 *   label = @Translation("Webform Scheduled Export"),
 *   config_prefix = "webform_scheduled_export",
 *   admin_permission = "administer site configuration",
 *   handlers = {
 * 	 	"list_builder" = "Drupal\webform_scheduled_export\Controller\WebformScheduledExportListBuilder",
 * 		"form" = {
 *       "default" = "Drupal\webform_scheduled_export\Form\WebformScheduledExportForm",
 * 			 "delete" = "Drupal\webform_scheduled_export\Form\WebformScheduledExportDeleteForm"
 *     },
 * 	 },
 * 	 links = {
 *     "edit" = "/admin/structure/webform/scheduled-export/{webform_scheduled_export}/edit",
 * 		 "delete" = "/admin/structure/webform/scheduled-export/{webform_scheduled_export}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   }
 * )
 */
class WebformScheduledExport extends ConfigEntityBase {

  public $label;
  public $id;
  public $sftp_hostname;
  public $sftp_username;
	public $sftp_password;
	public $sftp_directory;
	public $webform;
	public $exporter;
	public $delimiter;
	public $multiple_delimiter;
	public $excel;
	public $file_name;
	public $header_format;
	public $header_prefix;
	public $header_prefix_key_delimiter;
	public $header_prefix_label_delimiter;
	public $entity_reference_format;
	public $options_format;
	public $options_item_format;
	public $likert_answers_format;
	public $signature_format;
	public $composite_element_item_format;
	

  public function getLabel() {
    return $this->label;
  }
  public function getUrl() {
    return $this->url;
  }
  public function getPort() {
    return $this->port;
  }

}
