<?php

class WebformScheduledExportUIController extends EntityDefaultUIController {

  public function hook_menu() {
    $path = drupal_get_path('module', 'webform_scheduled_export');
    $items['node/%webform_menu/webform-results/scheduled-export'] = array(
      'title' => 'Scheduled Export',
      'page callback' => 'drupal_get_form',
      'page arguments' => array($this->entityType . '_overview_form', $this->entityType, 1),

      'access callback' => 'webform_results_access',
      'access arguments' => array(1),
      'file' => 'webform_scheduled_export.admin.inc',
      'file path' => $path,
      'weight' => 7,
      'type' => MENU_LOCAL_TASK,
    );
    $items['node/%webform_menu/webform-results/scheduled-export/add'] = array(
      'title' => 'Scheduled Export',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('webform_scheduled_export_form', NULL, 1),
      'access callback' => 'webform_results_access',
      'access arguments' => array(1),
      'file' => 'webform_scheduled_export.admin.inc',
      'file path' => $path,
      'weight' => 7,
      'type' => MENU_NORMAL_ITEM,
    );
    $items['node/%webform_menu/webform-results/scheduled-export/edit/%'] = array(
      'title' => 'Scheduled Export',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('webform_scheduled_export_form', 5, 1),
      'access callback' => 'webform_results_access',
      'access arguments' => array(1),
      'file' => 'webform_scheduled_export.admin.inc',
      'file path' => $path,
      'weight' => 7,
      'type' => MENU_NORMAL_ITEM,
    );
    return $items;
  }

  /**
   * Builds the entity overview form.
   */
  public function overviewForm($form, &$form_state) {
    // By default just show a simple overview for all entities.
    $form['table'] = $this->overviewTable(array(), $form_state['build_info']['args'][1]);
    $form['pager'] = array('#theme' => 'pager');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function overviewTable($conditions = array(), $node = NULL) {
    module_load_include('inc', 'webform', 'includes/webform.export');

    $entities = WebformScheduledExportConfig::loadByWebformNid($node->nid);
    $link_options_return = array('query' => array('destination' => current_path()));

    $output = array(
      'add' => array(
        '#type' => 'link',
        '#title' => t('Create Schedule'),
        '#href' => 'node/' . $node->nid . '/webform-results/scheduled-export/add',
        '#attributes' => array(
          'class' => array('button'),
        ),
        '#options' => $link_options_return,
      ),
      'table' => array(
        '#theme' => 'table',
        '#header' => array(
          'Export Format',
          'Email',
          'Next Send Date',
          'Frequency',
          'Enabled',
          'Opperations'
        ),
      ),
    );

    if (!empty($entities)) {
      $exporter_types = webform_export_list();
      foreach ($entities as $entity) {
        $export_config = $entity->getExportConfig();
        $row = &$output['table']['#rows'][];
        $row[] = $exporter_types[$export_config['format']];
        $row[] = $entity->getEmail();
        $row[] = format_date($entity->getSendDate());
        $row[] = $entity->getFrequency();
        $row[] = $entity->isEnabled() ? t('Yes') : t('No');
        $row[] = l('Edit Schedule', 'node/' . $node->nid . '/webform-results/scheduled-export/edit/' . $entity->internalIdentifier(), $link_options_return);
      }
    }
    else {
      $output['table']['#rows'][] = array(
        array(
          'data' => t('No exports have been scheduled for this webform.'),
          'colspan' => count($output['table']['#header']),
        ),
      );
    }

    return $output;
  }

}
