<?php

$plugin = array(
  'title' => t('Email Notification'),
  'description' => t('Notify the user via email and put the file on disk'),
  'weight' => 0,
  'class' => array(
    'class' => 'WseEmailNotification',
    'file' => 'WseEmailNotification.php',
  ),
);

/**
 * Send a notification and move the file into private files.
 */
class WseEmailNotification extends WseDeliveryMethod {

  /**
   * {@inheritdoc}
   */
  public function deliver(\WebformScheduledExportConfig $config, array $webform_export_info) {
    if ($file = $this->moveFile($webform_export_info)) {
      $this->doSend($config, 'email_notification', array(
        'context' => array(
          'webform_scheduled_export_plugin' => get_class($this),
          'title' => $config->getWebformNode()->title,
          'url' => file_create_url($file->uri),
          'filename' => $file->filename,
          'fid' => $file->fid,
        ),
      ));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function mail(&$message, $params) {
    $message['subject'] = t('Exported submissions available for @title', array(
      '@title' => $params['context']['title'],
    ));

    // @TODO, write some info about the export into the body?
    $message['body'][] = t('The exported submissions are available here: !url', array(
      '!url' => $params['context']['url'],
    ));
  }

  /**
   * Moves the temporary file to private:// and saves it to managed_files.
   *
   * @param array $export_info
   *   The info from the webform exporter.
   *
   * @return bool|\stdClass
   *   The file object if successful otherwise FALSE.
   */
  protected function moveFile(array $export_info) {
    $file_info = $this->getFileInfo($export_info);
    $date = \DateTime::createFromFormat('Y-m-d H:i', $export_info['options']['send_date'])->format('dmY');
    $uri = file_create_filename(sprintf('%s-%s.%s', $export_info['format'], $date, $file_info['extension']), 'private://');
    if (!$file_path = file_unmanaged_move($export_info['file_name'], $uri)) {
      return FALSE;
    }

    $file = new stdClass();
    $file->uid = 1;
    $file->status = FILE_STATUS_PERMANENT;
    $file->filename = basename($uri);
    $file->uri = $file_path;
    $file->filemime = $file_info['mimetype'];
    $file->filesize = filesize($file->uri);

    return file_save($file);
  }

}
