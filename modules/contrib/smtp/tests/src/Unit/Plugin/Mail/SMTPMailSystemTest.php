<?php

namespace Drupal\Tests\smtp\Unit\Plugin\Mail;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\smtp\Plugin\Mail\SMTPMailSystem;
use Drupal\Tests\UnitTestCase;
use Egulias\EmailValidator\EmailValidator;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Validate requirements for SMTPMailSystem.
 *
 * @group SMTP
 */
class SMTPMailSystemTest extends UnitTestCase {

  /**
   * Test setup.
   */
  public function setup() {
    $this->mockConfig = $this->prophesize(Config::class);
    $this->mockConfigFactory = $this->prophesize(ConfigFactoryInterface::class);
    $this->mockConfigFactory->get('smtp.settings')->willReturn($this->mockConfig->reveal());
    $this->mockConfigFactory->getEditable('smtp.settings')->willReturn($this->mockConfig->reveal());

    $this->mockLogger = $this->prophesize(LoggerChannelFactoryInterface::class);
    $this->mockMessenger = $this->prophesize(Messenger::class);

    $mockContainer = $this->mockContainer = $this->prophesize(ContainerInterface::class);
    $mockContainer->get('config.factory')->willReturn($this->mockConfigFactory->reveal());
    $mockContainer->get('logger.factory')->willReturn($this->mockLogger->reveal());
    $mockContainer->get('messenger')->willReturn($this->mockMessenger->reveal());

    $mockStringTranslation = $this->prophesize(TranslationInterface::class);
    $mockStringTranslation->translate(Argument::any())->willReturnArgument(0);
    $mockStringTranslation->translate(Argument::any(), Argument::any())->willReturnArgument(0);
    $mockStringTranslation->translateString(Argument::any())->willReturn('.');
    $mockContainer->get('string_translation')->willReturn($mockStringTranslation->reveal());

    // Email validator.
    $this->emailValidator = new EmailValidator();
    $mockContainer->get('email.validator')->willReturn($this->emailValidator);
    \Drupal::setContainer($this->mockContainer->reveal());
  }

  /**
   * Provides scenarios for getComponents().
   */
  public function getComponentsProvider() {
    return [
      [
        // Input.
        'name@example.com',
        // Expected.
        [
          'name' => '',
          'email' => 'name@example.com',
        ],
      ],
      [
        ' name@example.com',
        [
          'name' => '',
          'input' => 'name@example.com',
          'email' => 'name@example.com',
        ],
      ],
      [
        'name@example.com ',
        [
          'name' => '',
          'input' => 'name@example.com',
          'email' => 'name@example.com',
        ],
      ],
      [
        'some name <address@example.com>',
        [
          'name' => 'some name',
          'email' => 'address@example.com',
        ],
      ],
      [
        '"some name" <address@example.com>',
        [
          'name' => 'some name',
          'email' => 'address@example.com',
        ],
      ],
      [
        '<address@example.com>',
        [
          'name' => '',
          'email' => 'address@example.com',
        ],
      ],
    ];
  }

  /**
   * Test getComponents().
   *
   * @dataProvider getComponentsProvider
   */
  public function testGetComponents($input, $expected) {
    $mailSystem = new SMTPMailSystemTestHelper([], '', [], $this->mockLogger->reveal(), $this->mockMessenger->reveal(), $this->emailValidator);

    $ret = $mailSystem->publiGetComponents($input);

    if (!empty($expected['input'])) {
      $this->assertEquals($expected['input'], $ret['input']);
    }
    else {
      $this->assertEquals($input, $ret['input']);
    }

    $this->assertEquals($expected['name'], $ret['name']);
    $this->assertEquals($expected['email'], $ret['email']);
  }

}

/**
 * Test helper for SMTPMailSystemTest.
 */
class SMTPMailSystemTestHelper extends SMTPMailSystem {

  /**
   * Exposes getComponents for testing.
   */
  public function publiGetComponents($input) {
    return $this->getComponents($input);
  }

}
