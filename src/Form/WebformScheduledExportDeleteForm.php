<?php
 
namespace Drupal\webform_scheduled_export\Form;
 
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
 
/**
 * Provides a deletion confirmation form for webform_scheduled_export entity.
 */
class WebformScheduledExportDeleteForm extends EntityConfirmFormBase {
 
  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the webform scheduled export configuration %name?', array('%name' => $this->entity->label()));
  }
 
  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('webform_scheduled_export.collection');
  }
 
  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }
 
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $this->logger('webform_scheduled_export')->notice('Webform Scheduled Export %name has been deleted.', array('%name' => $this->entity->label()));
    drupal_set_message($this->t('Webform Scheduled Export %name has been deleted.', array('%name' => $this->entity->label())));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }
 
}