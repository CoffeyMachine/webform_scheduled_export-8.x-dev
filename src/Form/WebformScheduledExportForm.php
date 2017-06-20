<?php

namespace Drupal\webform_scheduled_export\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\webform_scheduled_export\Entity\WebformScheduledExport;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityForm;


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

    $form['info'] = array(
      '#markup' => '<p>' . $this->t('Configuration entities are used for data that determines the configuration of your system and is usually maintained by site builders and system administrators. Configuration entities have fields and can therefore store complex data structures.') . '</p>' .
                   '<p>' . $this->t('In this demo we have defined a "webservice" configuration entity. Each record is one webservice configuration which stores a name, URL and port number for each webservice.') . '</p>',
    );

    // Show all webform entities in a table.
    $entities = $this->getAllWebservices();
    $form['entities'] = array(
      '#type' => 'table',
      '#header' => array(
        $this->t('ID'),
        $this->t('Name'),
        $this->t('URL'),
        $this->t('Port'),
      ),
      '#empty' => $this->t('There are no webservices yet. You can add using the form below'),
      '#title' => $this->t('Available webservices'),
    );
    foreach ($entities as $id => $webserice) {
      $form['entities'][$id] = array(
        array('#markup' => $webserice->id()),
        array('#markup' => $webserice->label),
        array('#markup' => $webserice->url),
        array('#markup' => $webserice->port),
      );
    }
		
		$entity = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => '255',
      '#description' => $this->t('A unique name for this webservice.'),
      '#default_value' => $entity->getLabel(),
    );
    $form['name'] = array(
      '#type' => 'machine_name',
      '#maxlength' => 64,
      '#description' => $this->t('A unique name for this webservice. It must only contain lowercase letters, numbers and underscores.'),
      '#machine_name' => array(
        'exists' => array($this, 'exists'),
      ),
      '#default_value' => $entity->id(),
    );
    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#maxlength' => '255',
      '#description' => $this->t('The URL of this webservice. For example: http://example.com'),
      '#default_value' => $entity->getUrl(),
    );
    $form['port'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Port'),
      '#maxlength' => '6',
      '#description' => $this->t('The port of this webservice. For example: 8080'),
      '#default_value' => $entity->getPort(),
    );

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
    /** @var \Drupal\sample_config_entity\Entity\Ball $entity */
    $entity = $this->entity;
    // Prevent leading and trailing spaces.
    $entity->set('label', $form_state->getValue('label'));
    $entity->set('name', $form_state->getValue('name'));
    $entity->set('url', $form_state->getValue('url'));
		$entity->set('port', $form_state->getValue('port'));
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
   * Determines if the webservice name already exists.
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
   * Gets all webservice entities.
   *
   * @return mixed
   */
  protected function getAllWebservices() {
    return $this->storage->loadMultiple();
  }

}
