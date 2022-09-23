<?php

namespace Drupal\ckeditor_media_embed\Command;

interface CKEditorCliCommandInterface {

  /**
   * Retrieve the command input service.
   *
   * @return \Symfony\Component\Console\Input\InputInterface
   */
  public function getInput();

  /**
   * Retrieve the i/o style.
   *
   * @return \Symfony\Component\Console\Style\StyleInterface
   */
  public function getIo();

  /**
   * Retrieve message text.
   *
   * @param string $message_key
   *   The key of the requested message.
   *
   * @return string
   *   The requested message.
   */
  public function getMessage($message_key);

  /**
   * Present confirmation question to user.
   *
   * @param string $question
   *   The confirmation question.
   * @param $default
   *   The default value to return if user doesn’t enter any valid input.
   *
   * @return mixed
   *   The user answer
   */
  public function confirmation($question, $default = FALSE);

  /**
   * Output message in comment style.
   *
   * @param string $text
   *   The comment message.
   */
  public function comment($text);

}
