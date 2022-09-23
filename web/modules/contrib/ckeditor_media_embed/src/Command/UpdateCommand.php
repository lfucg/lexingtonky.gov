<?php

namespace Drupal\ckeditor_media_embed\Command;

// @codingStandardsIgnoreLine
use Drupal\Console\Annotations\DrupalCommand;

/**
 * Class UpdateCommand.
 *
 * @package Drupal\ckeditor_media_embed
 *
 * @DrupalCommand (
 *     extension="ckeditor_media_embed",
 *     extensionType="module"
 * )
 */
class UpdateCommand extends InstallCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('ckeditor_media_embed:update')
      ->setDescription($this->trans('commands.ckeditor_media_embed.update.description'));
  }

}
