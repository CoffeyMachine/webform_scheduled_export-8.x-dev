<?php

$plugin = array(
  'title' => t('Email Attachment'),
  'description' => t('Attach the exported file as an attachment to the email'),
  'weight' => 0,
  'class' => array(
    'class' => 'WseEmailAttachment',
    'file' => 'WseEmailAttachment.php',
  ),
);

/**
 * Email the exported file as an attachment.
 */
class WseEmailAttachment extends WseDeliveryMethod {

  /**
   * {@inheritdoc)
   */
  public function deliver(\WebformScheduledExportConfig $config, array $webform_export_info) {
    $file_info = $this->getFileInfo($webform_export_info);
    $this->doSend($config, 'email_attachment', array(
      'context' => array(
        'webform_scheduled_export_plugin' => get_class($this),
        'title' => $config->getWebformNode()->title,
      ),
      'attachments' => array(
        array(
          'filepath' => $webform_export_info['file_name'],
          'filename' => 'submissions.' . $file_info['extension'],
          'filemime' => $file_info['mimetype'],
        ),
      ),
    ));
  }

  /**
   * {@inheritdoc)
   */
  public function mail(&$message, $params) {
    $message['subject'] = t('Exported submissions attached for @title', array(
      '@title' => $params['context']['title'],
    ));

    $message['body'][] = t('The exported submissions are attached.');
  }

}
