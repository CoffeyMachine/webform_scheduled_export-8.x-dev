<?php

/**
 * Base class for all delivery methods.
 */
abstract class WseDeliveryMethod implements WseDeliveryMethodInterface {

  /**
   * Send the mail to all emails.
   *
   * @param \WebformScheduledExportConfig $config
   *   The configuration object.
   * @param string $key
   *   The mail key.
   * @param array $params
   *   The params for context when sending the email.
   */
  protected function doSend(\WebformScheduledExportConfig $config, $key, $params) {
    foreach (explode("\n", $config->getEmail()) as $mail) {
      drupal_mail('webform_scheduled_export', $key, $mail, language_default(), $params);
    }
  }

  /**
   * Gets the file attachment info based on the export formats.
   *
   * Webform exporters don't provide any way to get the filename and mimetype so
   * we copy most of their logic here to ensure we get it right.
   *
   * @param array $export_info
   *   The export info from Webform.
   *
   * @return array
   *   An array containing the extension and mimetype.
   */
  protected function getFileInfo($export_info) {
    $file_info_map = $this->getFileInfoMap();
    $format = $export_info['format'];

    // Special handling for delimited.
    if ($format === 'delimited') {
      $format = $export_info['options']['delimiter'] === '\t' ? 'delimited_tab' : 'delimited_csv';
    }

    // This can only occur if you're using a custom exporter for webform that
    // does not add it's own format via
    // hook_webform_scheduled_export_file_formats_alter().
    if (!isset($file_info_map[$format])) {
      watchdog('webform_scheduled_export', 'Invalid format %format', array(
        '%format' => $format,
      ));

      // Just have a guess with a default.
      return array(
        'extension' => 'csv',
        'mimetype' => 'text/csv',
      );
    }

    return $file_info_map[$format];
  }

  /**
   * Gets the metadata for each exporter type.
   *
   * @return array
   *   An array of exporter types to file metadata.
   */
  protected function getFileInfoMap() {
    $file_info_map = array(
      'excel' => array(
        'extension' => 'xlsx',
        'mimetype' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      ),
      'excel_legacy' => array(
        'extension' => 'xls',
        'mimetype' => 'application/x-msexcel',
      ),
      'delimited_tab' => array(
        'extension' => 'tsv',
        'mimetype' => 'text/tab-separated-values',
      ),
      'delimited_csv' => array(
        'extension' => 'csv',
        'mimetype' => 'text/csv',
      ),
    );

    // Allow other modules to support other files.
    drupal_alter('webform_scheduled_export_file_formats', $file_info_map);

    return $file_info_map;
  }

  /**
   * Creates an instance of the request plugin.
   *
   * @param string $plugin_id
   *   The plugin id.
   *
   * @return \WseDeliveryMethodInterface
   */
  public static function createInstance($plugin_id) {
    $definitions = static::getDefinitions();
    if (!isset($definitions[$plugin_id])) {
      throw new \InvalidArgumentException(sprintf('Plugin %s does not exist', $plugin_id));
    }

    return new $definitions[$plugin_id]['class']['class'];
  }

  /**
   * Gets the plugin definitions.
   *
   * @return array
   *   An array of plugin definitions keyed by plugin_id.
   */
  public static function getDefinitions() {
    ctools_include('plugins');
    $plugins = ctools_get_plugins('webform_scheduled_export', 'delivery_method');
    uasort($plugins, 'ctools_plugin_sort');
    return $plugins;
  }

}
