<?php

namespace Drupal\webform_scheduled_export\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\example\ExampleInterface;

/**
 * Defines the Example entity.
 *
 * @ConfigEntityType(
 *   id = "webform_scheduled_export",
 *   label = @Translation("Webform Scheduled Export"),
 *   handlers = {
 *     "list_builder" = "Drupal\webform_scheduled_export\Controller\WebformScheduledExportListBuilder",
 *     "form" = {
 *       "add" = "Drupal\webform_scheduled_export\Form\ExampleForm",
 *       "edit" = "Drupal\webform_scheduled_export\Form\ExampleForm",
 *       "delete" = "Drupal\webform_scheduled_export\Form\ExampleDeleteForm",
 *     }
 *   },
 *   config_prefix = "webform_scheduled_export",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/example/{example}",
 *     "delete-form" = "/admin/config/system/example/{example}/delete",
 *   }
 * )
 */

/**
 * @file
 * The webform scheduled config entity.
 */

class WebformScheduledExport extends ConfigEntityBase implements WebformScheduledExportInterface {
	
	/**
   * The Example ID.
   *
   * @var string
   */
  public $id;

  /**
   * The Example label.
   *
   * @var string
   */
  public $label;

  const ENABLED = 1;
  const DISABLED = 0;

  // @codingStandardsIgnoreStart
  public $webform_nid;
  public $status;
  public $send_date;
  public $frequency;
  public $email;
  public $delivered;
  public $export_config;
  public $delivery_method;
  // @codingStandardsIgnoreEnd

  /**
   * Mark the config as sent once the email has been handled.
   *
   * If the frequency is "once" then we mark the config as sent and that's all.
   * Otherwise, we calculate the next send date and update it accordingly.
   */
  public function markDelivered() {
    if ($this->getFrequency() === 'once') {
      $this->setDelivered(TRUE);
    }
    else {
      // Re-calculate the next send date based on the frequency.
      $send_date = $this->getSendDate();

      $one_day = 86400;
      switch ($this->getFrequency()) {
        case 'daily':
          $send_date += $one_day;
          break;

        case 'weekly':
          $send_date += $one_day * 7;
          break;

        case 'monthly':
          $send_date += $one_day * 30;
          break;
      }
      $this->setSendDate($send_date);
      $this->setDelivered(FALSE);
    }
    $this->save();
  }

  /**
   * Gets the loaded webform node.
   *
   * @return object
   *   The loaded node object.
   */
  public function getWebformNode() {
    return node_load($this->webform_nid);
  }

  /**
   * Gets the email configuration.
   *
   * @return string
   *   The comma separated emails.
   */
  public function getEmail() {
    return $this->email;
  }

  /**
   * Sets the available emails.
   *
   * @param string $email
   *   The emails to send to.
   *
   * @return $this
   *   The config object.
   */
  public function setEmail($email) {
    $this->email = $email;
    return $this;
  }

  /**
   * Checks if this config is enabled.
   *
   * @return bool
   *   TRUE if it's enabled otherwise FALSE.
   */
  public function isEnabled() {
    return $this->getStatus() === static::ENABLED;
  }

  /**
   * Gets the current config status.
   *
   * @return int
   *   The status as an integer.
   */
  public function getStatus() {
    return (int) $this->status;
  }

  /**
   * Set the config status.
   *
   * @param bool $status
   *   The status.
   *
   * @return $this
   *   The config object.
   */
  public function setStatus($status) {
    $this->status = (int) $status;
    return $this;
  }

  /**
   * Gets the send date for this schedule config.
   *
   * @return int
   *   The send date as a Unix timestamp.
   */
  public function getSendDate() {
    return $this->send_date;
  }

  /**
   * Sets the send date.
   *
   * @param int $send_date
   *   The send date as a Unix timestamp.
   *
   * @return $this
   *   The config object.
   */
  public function setSendDate($send_date) {
    $this->send_date = $send_date;
    return $this;
  }

  /**
   * Gets the config send frequency.
   *
   * @return string
   *   The config frequency.
   */
  public function getFrequency() {
    return $this->frequency;
  }

  /**
   * Sets the webform nid.
   *
   * @param int $webform_nid
   *   The webform nid.
   *
   * @return $this
   *   The config object.
   */
  public function setWebformNid($webform_nid) {
    $this->webform_nid = $webform_nid;
    return $this;
  }

  /**
   * Gets the webform nid.
   *
   * @return int
   *   The associated webform ID.
   */
  public function getWebformId() {
    return $this->webform_nid;
  }

  /**
   * Sets the send frequency.
   *
   * @param string $frequency
   *   The frequency to set, one of: once, daily, weekly or monthly.
   *
   * @return $this
   *   The config object.
   */
  public function setFrequency($frequency) {
    $this->frequency = $frequency;
    return $this;
  }

  /**
   * Checks if this config has already been delivered for the next send_date.
   *
   * @return bool
   *   TRUE if already delivered otherwise FALSE.
   */
  public function isDelivered() {
    return (bool) $this->delivered;
  }

  /**
   * Sets whether this export has already been sent.
   *
   * @param bool $delivered
   *   The new sent status.
   *
   * @return $this
   *   The config object.
   */
  public function setDelivered($delivered) {
    $this->delivered = (int) $delivered;
    return $this;
  }

  /**
   * Sets the export config.
   *
   * @param array $export_config
   *   The export configuration.
   *
   * @return $this
   *   The config object.
   */
  public function setExportConfig(array $export_config) {
    $this->export_config = serialize($export_config);
    return $this;
  }

  /**
   * Gets the export configuration.
   *
   * @return array
   *   An array of saved export config.
   */
  public function getExportConfig() {
    return unserialize($this->export_config) ?: array();
  }

  /**
   * Gets a config entity by the webform nid it is related to.
   *
   * @param int $webform_nid
   *   The related webform node id.
   *
   * @return static
   *   The loaded entity if it exists otherwise FALSE.
   */
  public static function loadByWebformNid($webform_nid) {
    return static::loadMultiple(FALSE, array('webform_nid' => $webform_nid));
  }

  /**
   * Load multiple configs via the entity id.
   *
   * @param array|bool $ids
   *   The ids to load.
   * @param array $conditions
   *   The filters or conditions.
   *
   * @return static[]
   *   An array of config entities.
   */
  public static function loadMultiple($ids, $conditions = array()) {
    return entity_load('webform_scheduled_export_config', $ids, $conditions);
  }

  /**
   * Creates a new config entity.
   *
   * @param array $values
   *   An array of values to create the config with.
   *
   * @return static
   *   The new config entity.
   */
  public static function create(array $values) {
    return entity_create('webform_scheduled_export_config', $values + array(
      'status' => 1,
      'send_date' => REQUEST_TIME,
      'frequency' => 'once',
    ));
  }

  /**
   * Load an existing config entity or create a shell if one does not exist.
   *
   * @param int|null $id
   *   An ID or NULL if the entity should be created.
   *
   * @return \WebformScheduledExportConfig
   *   The loaded config entity.
   */
  public static function loadOrCreate($id) {
    if ($id !== NULL) {
      $entity = entity_load('webform_scheduled_export_config', array($id));
      $entity = array_shift($entity);
    }
    else {
      $entity = WebformScheduledExportConfig::create(array());
    }
    return $entity;
  }

  /**
   * Gets all config entities that are ready to be sent.
   *
   * @return array|static[]
   *   The loaded config entities.
   */
  public static function getExportsReadyToSend() {
    $query = new \EntityFieldQuery();
    $result = $query
      ->entityCondition('entity_type', 'webform_scheduled_export_config')
      ->propertyCondition('status', WebformScheduledExportConfig::ENABLED)
      ->propertyCondition('send_date', REQUEST_TIME, '<')
      ->propertyCondition('delivered', 0)
      ->execute();

    // If it's empty, nothing to do.
    if (empty($result['webform_scheduled_export_config'])) {
      return array();
    }
    return static::loadMultiple(array_keys($result['webform_scheduled_export_config']));
  }

  /**
   * Sets the delivery method.
   *
   * @param string $delivery_method
   *   The delivery method id.
   *
   * @return $this
   *   The config object.
   */
  public function setDeliveryMethod($delivery_method) {
    $this->delivery_method = $delivery_method;
    return $this;
  }

  /**
   * Gets the delivery method.
   *
   * @return string
   *   The delivery method implementation plugin id.
   */
  public function getDeliveryMethod() {
    return $this->delivery_method;
  }

}
