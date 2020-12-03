<?php

namespace Drupal\ckeditor_media_embed\Command\Drush;

use Drupal\ckeditor_media_embed\Command\CKEditorCliCommandInterface;
use Drupal\ckeditor_media_embed\Command\CliCommandWrapper;
use Drupal\Core\Serialization\Yaml;
use Drush\Style\DrushStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InstallCommand.
 */
class InstallCommand implements CKEditorCliCommandInterface {

  /**
   * The CKEditor Media Embed CLI Commands service.
   *
   * @var \Drupal\ckeditor_media_embed\CLICommands
   */
  protected $cliCommands;

  /**
   * The messages displayed to the user at various steps of the installation.
   *
   * @var string[]
   */
  protected $messages;

  /**
   * Constructs command object.
   *
   * @param \Drupal\ckeditor_media_embed\Command\CliCommandWrapper $cli_commands
   *   The CKEditor Media Embed CLI Commands service.
   */
  public function __construct(CliCommandWrapper $cli_commands) {
    $this->cliCommands = $cli_commands;
    $this->setMessages();
  }

  /**
   * Executes the command.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   An InputInterface instance.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   An OutputInterface instance.
   * @param \Drush\Style\DrushStyle $io
   *   The Drush i/o object.
   */
  public function execute(InputInterface $input, OutputInterface $output, DrushStyle $io) {
    $this->input = $input;
    $this->output = $output;
    $this->io = $io;

    $overwrite = $this->cliCommands->askToOverwritePluginFiles($this);

    if ($overwrite) {
      $this->cliCommands->overwritePluginFiles($this, $overwrite);
    }
  }

  /**
   * Set messages to display to the user at various steps of the installation.
   *
   * @return $this
   */
  protected function setMessages() {
    $messages_file = \Drupal::service('module_handler')->getModule('ckeditor_media_embed')->getPath() . '/console/translations/en/ckeditor_media_embed.install.yml';
    $messages = Yaml::decode(file_get_contents($messages_file))['messages'];

    $this->messages = array_map(function ($message) {
      return dt($message);
    }, $messages);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getInput() {
    return $this->input;
  }

  /**
   * {@inheritdoc}
   */
  public function getIo() {
    return $this->io;
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage($message_key) {
    return $this->messages[$message_key];
  }

  /**
   * {@inheritdoc}
   */
  public function confirmation($question, $default = FALSE) {
    return $this->io->confirm($question, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function comment($text) {
    $this->io->text(sprintf('<comment>%s</comment>', $text));
  }

}
