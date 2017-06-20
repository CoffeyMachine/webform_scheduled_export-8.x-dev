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
 *     "id" = "name",
 *     "label" = "label",
 *   }
 * )
 */
class WebformScheduledExport extends ConfigEntityBase {

  /**
   * The webform_scheduled_export label.
   *
   * @var string
   */
  public $label;

  /**
   * The webform_scheduled_export machine readable name.
   *
   * @var string
   */
  public $name;

  /**
   * The webform_scheduled_export URL.
   *
   * @var string
   */
  public $url;

  /**
   * The webform_scheduled_export port.
   *
   * @var string
   */
  public $port;

  /**
   * Overrides Drupal\Core\Entity\Entity::id().
   */
  public function id() {
    // It would have been easier to use "id" here instead of "name". But
    // "name" is used here to demonstrate what happens if a non-default
    // variable name is used. See "entity_keys" in the ConfigEntityType
    // annotation too.
    return $this->name;
  }
  
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
