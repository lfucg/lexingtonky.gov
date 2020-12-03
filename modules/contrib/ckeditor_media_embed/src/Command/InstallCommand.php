<?php

namespace Drupal\ckeditor_media_embed\Command;

// @codingStandardsIgnoreLine
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Command\Shared\ContainerAwareCommandTrait;
use Drupal\Console\Core\Style\DrupalStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class InstallCommand.
 *
 * @package Drupal\ckeditor_media_embed
 *
 * @DrupalCommand (
 *     extension="ckeditor_media_embed",
 *     extensionType="module"
 * )
 */
class InstallCommand extends Command implements CKEditorCliCommandInterface {

  use ContainerAwareCommandTrait;

  /**
   * The CKEditor Media Embed CLI Commands service.
   *
   * @var \Drupal\ckeditor_media_embed\CLICommands
   */
  protected $cliCommands;

  /**
   * {@inheritdoc}
   */
  public function __construct(CliCommandWrapper $cliCommands) {
    parent::__construct();
    $this->cliCommands = $cliCommands;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('ckeditor_media_embed:install')
      ->setDescription($this->trans('commands.ckeditor_media_embed.install.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->input = $input;
    $this->output = $output;
    $this->io = new DrupalStyle($input, $output);

    $overwrite = $this->cliCommands->askToOverwritePluginFiles($this);

    if ($overwrite) {
      $this->cliCommands->overwritePluginFiles($this, $overwrite);
    }
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
    return $this->trans("commands.ckeditor_media_embed.install.messages.$message_key");
  }

  /**
   * {@inheritdoc}
   */
  public function confirmation($question, $default = FALSE) {
    $helper = $this->getHelper('question');
    $confirmation_question = new ConfirmationQuestion($question, $default);

    return $helper->ask($this->input, $this->output, $confirmation_question);
  }

  /**
   * {@inheritdoc}
   */
  public function comment($text) {
    $this->io->comment($text);
  }

}
