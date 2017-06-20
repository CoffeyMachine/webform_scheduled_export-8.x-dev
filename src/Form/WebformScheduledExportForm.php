<?php

namespace Drupal\webform_scheduled_export\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\webform\WebformExporterManager;
use Drupal\webform_scheduled_export\Entity\WebformScheduledExport;



/**
 * Implements the Webform Scheduled Export configuration form.
 */
class WebformScheduledExportForm extends EntityForm {

  /**
   * The webservice storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory.
   */
  protected $entityQuery;

  /**
   * Constructs a new form using dependency injection.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The query factory.
   */
  public function __construct(EntityStorageInterface $storage, QueryFactory $entity_query) {
    $this->storage = $storage;
    $this->entityQuery = $entity_query;
		$this->exporterManager = \Drupal::service('plugin.manager.webform.exporter');
		$this->submissionExporter = \Drupal::service('webform_submission.exporter');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      // We only care about the Webservice enities in this form, therefore
      // we directly use and store the right storage.
      $container->get('entity_type.manager')->getStorage('webform_scheduled_export'),
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}.
   */
  public function getFormID() {
    return 'webform_scheduled_export_add';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
		$entity = $this->entity;
    
    //format values
    
		$export_options['label'] = $entity->label;
		$export_options['id'] = $entity->id;
		$export_options['sftp_hostname'] = $entity->sftp_hostname;
		$export_options['sftp_username'] = $entity->sftp_username;
		$export_options['sftp_password'] = $entity->sftp_password;
		$export_options['sftp_directory'] = $entity->sftp_directory;
		$export_options['webform'] = $entity->webform? \Drupal\webform\Entity\Webform::load($entity->webform) : NULL;
		if($entity->id !== NULL) {
			$export_options['exporter'] = $entity->exporter;
			$export_options['delimiter'] = $entity->delimiter;
			$export_options['multiple_delimiter'] = $entity->multiple_delimiter;
			$export_options['excel'] = $entity->excel;
			$export_options['file_name'] = $entity->file_name;
			$export_options['header_format'] = $entity->header_format;
			$export_options['header_prefix'] = $entity->header_prefix;
			$export_options['header_prefix_key_delimiter'] = $entity->header_prefix_key_delimiter;
			$export_options['header_prefix_label_delimiter'] = $entity->header_prefix_label_delimiter;
			$export_options['entity_reference_format'] = $entity->entity_reference_format;
			$export_options['options_format'] = $entity->options_format;
			$export_options['options_item_format'] = $entity->options_item_format;
			$export_options['likert_answers_format'] = $entity->likert_answers_format;
			$export_options['signature_format'] = $entity->signature_format;
			$export_options['composite_element_item_format'] = $entity->composite_element_item_format;
		}
		
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Administrator Label'),
      '#maxlength' => '255',
      '#description' => $this->t('A unique name for this scheduled export.'),
      '#default_value' => $export_options['label'],
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#maxlength' => 64,
      '#description' => $this->t('A unique name for this scheduled export. It must only contain lowercase letters, numbers and underscores.'),
      '#machine_name' => array(
        'exists' => array($this, 'exists'),
      ),
      '#default_value' => $export_options['id'],
    );
		$form['webform'] = [
	    '#type' => 'entity_autocomplete',
	    '#target_type' => 'webform',
	    '#title' => $this->t('Webform'),
	    '#required' => true,
	    '#description' => $this->t('Select a webform to export.'),
	    '#default_value' => $export_options['webform'],
		];
		$form['sftp'] = [
      '#type' => 'details',
      '#title' => $this->t('SFTP upload'),
      '#open' => TRUE,
      '#description' => $this->t('Configure the connection to your SFTP server where the webform results will be uploaded.'),
    ];
		$form['sftp']['sftp_hostname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SFTP hostname'),
      '#required' => TRUE,
      '#default_value' => $export_options['sftp_hostname'],
    ];
		$form['sftp']['sftp_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SFTP username'),
      '#required' => TRUE,
      '#default_value' => $export_options['sftp_username'],
    ];
		$form['sftp']['sftp_password'] = [
      '#type' => 'password',
      '#title' => $this->t('SFTP password'),
      '#required' => TRUE,
      '#default_value' => $export_options['sftp_password'],
    ];
		$form['sftp']['sftp_directory'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SFTP directory'),
      '#required' => TRUE,
      '#default_value' => $export_options['sftp_directory'],
      '#description' => $this->t('The directory on your SFTP server where the export will be uploaded to.'),
    ];

		$this->submissionExporter->buildExportOptionsForm($form, $form_state, $export_options);
		
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->entity;
		$values = $form_state->getValues();
		
		//format values
		$formatted_values['label'] = $values['label'];
		$formatted_values['id'] = $values['id'];
		$formatted_values['sftp_hostname'] = $values['sftp_hostname'];
		$formatted_values['sftp_username'] = $values['sftp_username'];
		$formatted_values['sftp_password'] = $values['sftp_password'];
		$formatted_values['sftp_directory'] = $values['sftp_directory'];
		$formatted_values['webform'] = $values['webform'];
		$formatted_values['exporter'] = $values['export']['format']['exporter'];
		$formatted_values['delimiter'] = $values['export']['format']['delimiter'];
		$formatted_values['multiple_delimiter'] = $values['export']['element']['multiple_delimiter'];
		$formatted_values['excel'] = $values['export']['format']['excel'];
		$formatted_values['file_name'] = $values['export']['format']['file_name'];
		$formatted_values['header_format'] = $values['export']['header']['header_format'];
		$formatted_values['header_prefix'] = $values['export']['header']['header_prefix'];
		$formatted_values['header_prefix_key_delimiter'] = $values['export']['header']['header_prefix_key_delimiter'];
		$formatted_values['header_prefix_label_delimiter'] = $values['export']['header']['header_prefix_label_delimiter'];
		$formatted_values['entity_reference_format'] = $values['export']['elements']['entity_reference']['entity_reference_format'];
		$formatted_values['options_format'] = $values['export']['elements']['options']['options_format'];
		$formatted_values['options_item_format'] = $values['export']['elements']['options']['options_item_format'];
		$formatted_values['likert_answers_format'] = $values['export']['elements']['likert']['likert_answers_format'];
		$formatted_values['signature_format'] = $values['export']['elements']['signature']['signature_format'];
		$formatted_values['composite_element_item_format'] = $values['export']['elements']['composite']['composite_element_item_format'];
		
		foreach($formatted_values as $key => $value) {
			$entity->set($key, $value);
		}
		
    $status = $entity->save();
    //$edit_link = $this->entity->link($this->t('Edit'));
    $action = $status == SAVED_UPDATED ? 'updated' : 'added';
    // Tell the user we've updated their ball.
    drupal_set_message($this->t('Webform Scheduled Export %label has been %action.', ['%label' => $entity->label(), '%action' => $action]));
    //$this->logger('sample_config_entity')->notice('Webform Scheduled Export %label has been %action.', array('%label' => $entity->label(), 'link' => $edit_link));
    // Redirect back to the list view.
    $form_state->setRedirect('webform_scheduled_export.collection');
  }

  /**
   * Determines if the webform scheduled export name already exists.
   *
   * @param string $id
   *   The action ID
   *
   * @return bool
   *   TRUE if the action exists, FALSE otherwise.
   */
  public function exists($id) {
    $action = $this->storage->load($id);
    return !empty($action);
  }

  /**
   * Gets all webform scheduled export entities.
   *
   * @return mixed
   */
  protected function getAllWebformScheduledExports() {
    return $this->storage->loadMultiple();
  }

}
