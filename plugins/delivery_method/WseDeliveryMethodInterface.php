<?php

interface WseDeliveryMethodInterface {

  /**
   * Do the delivery for this method.
   *
   * @param \WebformScheduledExportConfig $config
   *   The scheduled configuration.
   * @param array $webform_export_info
   *   The webform export info.
   */
  public function deliver(\WebformScheduledExportConfig $config, array $webform_export_info);

  /**
   * Handle the hook_mail() implementation for this delivery.
   *
   * @param array $message
   *   The message array we're building.
   * @param array $params
   *   The params and context.
   */
  public function mail(&$message, $params);
}
