<?php

/**
 * @file
 * Admin configuration form.
 */

/**
 * Admin configuration form for scheduling exports.
 */
function webform_scheduled_export_form($form, &$form_state, $schedule_export_config_id, $node) {
  $config = WebformScheduledExportConfig::loadOrCreate($schedule_export_config_id);

  $form['export_wrapper'] = array(
    '#title' => t('Export Info'),
    '#type' => 'fieldset',
  );

  module_load_include('inc', 'webform', 'includes/webform.report');
  $form['export_wrapper']['export_info'] = webform_results_download_form(array(), $form_state, $node);

  // Set the default values on the form we got from Webform.
  $export_config = $config->getExportConfig();
  _webform_scheduled_export_set_defaults($form['export_wrapper']['export_info'], $export_config);

  // Disable the actual download.
  $form['export_wrapper']['export_info']['download']['#default_value'] = FALSE;

  // Add element specific to scheduling the export emails.
  $form['schedule_info'] = array(
    '#title' => t('Schedule Export'),
    '#type' => 'fieldset',
    '#weight' => -2,
  );
  $form['schedule_info']['enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enabled'),
    '#description' => t('Disable or Enable this config without losing your scheduled configuration'),
    '#default_value' => $config->isEnabled(),
  );
  $form['schedule_info']['email'] = array(
    '#type' => 'textarea',
    '#title' => t('Email'),
    '#default_value' => $config->getEmail(),
    '#required' => TRUE,
    '#description' => t('You can enter multiple emails, one per line.'),
  );

  $form['schedule_info']['send_date'] = array(
    '#type' => 'date_popup',
    '#title' => t('Next Send Date'),
    '#description' => $config->isDelivered() ? t('This email has already been sent') : t('This email will be sent on the next cron after this date passes.'),
    '#default_value' => date(WEBFORM_SCHEDULED_EXPORT_DATE_FORMAT, $config->getSendDate()),
    '#date_type' => DATE_UNIX,
    '#date_timezone' => date_default_timezone(),
    '#date_format' => variable_get('webform_scheduled_export_date_display_format', 'd/m/Y H:i'),
    '#date_increment' => 15,
    '#required' => TRUE,
  );
  $form['schedule_info']['frequency'] = array(
    '#type' => 'select',
    '#title' => t('Frequency'),
    '#description' => t('Should this scheduled export repeat?'),
    '#default_value' => $config->getFrequency(),
    '#options' => array(
      'once' => t('Send Once'),
      'daily' => t('Daily'),
      'weekly' => t('Weekly'),
      'monthly' => t('Monthly'),
    ),
  );

  $form['delivery_info'] = array(
    '#type' => 'fieldset',
    '#title' => t('Delivery Info'),
    '#weight' => -1,
  );

  $plugins = array();
  foreach (\WseDeliveryMethod::getDefinitions() as $plugin_id => $info) {
    $plugins[$plugin_id] = $info['title'];
  }
  $default_delivery_method = $config->getDeliveryMethod() ?: WEBFORM_SCHEDULED_EXPORT_DEFAULT_DELIVERY;
  $form['delivery_info']['delivery_method'] = array(
    '#title' => t('Method'),
    '#type' => 'radios',
    '#options' => $plugins,
    '#default_value' => $default_delivery_method,
  );

  $form['export_wrapper']['export_info']['actions']['submit']['#value'] = t('Save export configuration');

  return $form;
}

/**
 * Recursively attempt to set the defaults back onto the form.
 *
 * @param array $form
 *   The form part.
 * @param array $default_values
 *   An array of defaults.
 */
function _webform_scheduled_export_set_defaults(&$form, $default_values) {
  foreach (element_children($form) as $key) {
    if (array_key_exists($key, $default_values)) {
      if ($form[$key]['#type'] === 'fieldset') {
        _webform_scheduled_export_set_defaults($form[$key], $default_values[$key]);
      }
      else {
        $form[$key]['#default_value'] = is_array($default_values[$key]) ? array_filter($default_values[$key]) : $default_values[$key];
      }
    }
  }
}

/**
 * Validate callback for config form.
 */
function webform_scheduled_export_form_validate(&$form, &$form_state) {
  $send_date = \DateTime::createFromFormat(WEBFORM_SCHEDULED_EXPORT_DATE_FORMAT, $form_state['values']['send_date']);
  if (!$send_date) {
    form_set_error('send_date', t('Invalid date'));
  }
}

/**
 * Submit handler for config form.
 */
function webform_scheduled_export_form_submit(&$form, &$form_state) {
  $node = $form_state['values']['node'];
  $send_date = \DateTime::createFromFormat(WEBFORM_SCHEDULED_EXPORT_DATE_FORMAT, $form_state['values']['send_date']);

  // Try to load the config otherwise create a new one.
  $config = WebformScheduledExportConfig::loadOrCreate($form_state['build_info']['args'][0]);

  // If the send date is in the future then we always mark the email as not
  // sent to ensure it gets resent when the frequency is "once". If the date has
  // changed, even to one in the past, we also mark as not sent.
  if ($config->getSendDate() != $send_date->getTimestamp() || $send_date->getTimestamp() > REQUEST_TIME) {
    $config->setDelivered(FALSE);
  }

  // Save the export and schedule config.
  $export_config = webform_scheduled_export_clean_values($form_state['values']);
  $config
    ->setSendDate($send_date->getTimestamp())
    ->setFrequency($form_state['values']['frequency'])
    ->setStatus($form_state['values']['enabled'])
    ->setWebformNid($node->nid)
    ->setEmail($form_state['values']['email'])
    ->setDeliveryMethod($form_state['values']['delivery_method'])
    ->setExportConfig($export_config);

  drupal_set_message(!empty($config->is_new) ? t('Webform scheduled export has been created.') : t('Webform scheduled export has been updated.'));

  $config->save();
}

/**
 * Cleans the form state values for saving.
 *
 * @param array $form_state_values
 *   The submitted form state values.
 *
 * @return array
 *   An array with the un-needed values removed.
 */
function webform_scheduled_export_clean_values($form_state_values) {
  $excluded = array(
    'node',
    'op',
    'form_id',
    'form_build_id',
    'form_token',
    'submit',
  );
  return array_diff_key($form_state_values, array_flip($excluded));
}
