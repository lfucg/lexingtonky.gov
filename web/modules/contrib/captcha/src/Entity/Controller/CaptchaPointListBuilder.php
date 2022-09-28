<?php

namespace Drupal\captcha\Entity\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Builds the list of capture points for the captcha point form.
 */
class CaptchaPointListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['form_id'] = $this->t('Captcha Point form ID');
    $header['captcha_type'] = $this->t('Captcha Point challenge type');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['form_id'] = $entity->id();
    $row['captcha_type'] = $entity->getCaptchaType();

    return $row + parent::buildRow($entity);
  }

}
