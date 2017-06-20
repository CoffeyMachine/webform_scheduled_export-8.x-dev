<?php

namespace Drupal\webform_scheduled_export\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Example.
 */
class WebformScheduledExportListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Title');
    $header['webform'] = $this->t('Webform');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['webform'] = $entity->webform;

    // You probably want a few more properties here...

    return $row + parent::buildRow($entity);
  }
	
	/**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
 
    if ($entity->hasLinkTemplate('edit')) {
      $operations['edit'] = array(
        'title' => t('Edit Scheduled Export'),
        'weight' => 20,
        'url' => $entity->urlInfo('edit'),
      );
    }
		if ($entity->hasLinkTemplate('delete')) {
      $operations['delete'] = array(
        'title' => t('Delete Scheduled Export'),
        'weight' => 21,
        'url' => $entity->urlInfo('delete'),
      );
    }
		
    return $operations;
  }

}