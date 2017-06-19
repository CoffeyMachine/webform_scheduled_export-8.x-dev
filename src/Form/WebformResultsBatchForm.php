<?php

namespace Drupal\webform_scheduled_export\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Controller\WebformResultsExportController;

/**
 * Implements a hidden form to batch process the webform output on cron.
 */
class WebformResultsBatchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webform_scheduled_export_webform_results_batch_form';
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
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
   	if ($form_state->get('hidden_value') == 1) {
	    WebformResultsExportController::batchSet($form_state->get('webform'), $form_state->get('source_entity'), $form_state->get('export_options'));
	  }
  }

}