<?php

namespace Drupal\image_captcha\Controller;

/**
 * To change template file, choose Tools | Templates and open it in the editor.
 */

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Description of CaptchaImageRefresh.
 */
class CaptchaImageRefresh extends ControllerBase {

  /**
   * Obtaining system time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The contruct method.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Obtaining system time.
   */
  public function __construct(TimeInterface $time) {
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('datetime.time')
    );
  }

  /**
   * Put your code here.
   */
  public function refreshCaptcha($form_id = NULL) {
    $result = [
      'status' => 0,
      'message' => '',
    ];
    try {
      module_load_include('inc', 'captcha', 'captcha');
      $config = $this->config('image_captcha.settings');
      $captcha_sid = _captcha_generate_captcha_session($form_id);
      $captcha_token = md5(mt_rand());
      $allowed_chars = _image_captcha_utf8_split($config->get('image_captcha_image_allowed_chars', IMAGE_CAPTCHA_ALLOWED_CHARACTERS));
      $code_length = (int) $config->get('image_captcha_code_length');
      $code = '';
      for ($i = 0; $i < $code_length; $i++) {
        $code .= $allowed_chars[array_rand($allowed_chars)];
      }
      $connection = Database::getConnection();
      $connection->update('captcha_sessions')
        ->fields([
          'token' => $captcha_token,
          'solution' => $code,
        ])
        ->condition('csid', $captcha_sid, '=')
        ->execute();
      $result['data'] = [
        'url' => Url::fromRoute('image_captcha.generator', ['session_id' => $captcha_sid, 'timestamp' => $this->time->getRequestTime()])->toString(),
        'token' => $captcha_token,
        'sid' => $captcha_sid,
      ];
      $result['status'] = 1;
    }
    catch (\Exception $e) {
      if ($message = $e->getMessage()) {
        $result['message'] = $message;
      }
      else {
        $result['message'] = $this->t('Error has occurred. Please contact to site administrator.');
      }
    }
    return new JsonResponse($result);
  }

}
