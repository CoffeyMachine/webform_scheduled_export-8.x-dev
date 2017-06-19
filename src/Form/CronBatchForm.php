<?php

namespace Drupal\webform_scheduled_export\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Controller\WebformResultsExportController;

/**
 * Implements an example form.
 */
class CronBatchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cron_batch_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['hidden_value'] = array(
	    '#type' => 'hidden',
	    '#value' => 1,
	  );
    return $form;
  }

  /**
   * {@inheritdoc}
   *
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('phone_number')) < 3) {
      $form_state->setErrorByName('phone_number', $this->t('The phone number is too short. Please enter a full phone number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //if ($form_state['values']['hidden_value'] == 1) {
	    WebformResultsExportController::batchSet($form_state->get('webform'), $form_state->get('source_entity'), $form_state->get('export_options'));
	  //}
  }

}