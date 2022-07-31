<?php

namespace Drupal\search_api_pantheon_admin\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\ServerInterface;
use Drupal\search_api_pantheon\Services\PantheonGuzzle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The Pantheon Solr Admin form.
 *
 * @package Drupal\search_api_pantheon\Form
 */
class PantheonSolrAdminForm extends FormBase {

  /**
   * The PantheonGuzzle service.
   *
   * @var \Drupal\search_api_pantheon\Services\PantheonGuzzle
   */
  protected PantheonGuzzle $pantheonGuzzle;

  /**
   * Constructs a new EntityController.
   */
  public function __construct(PantheonGuzzle $pantheonGuzzle) {
    $this->pantheonGuzzle = $pantheonGuzzle;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('search_api_pantheon.pantheon_guzzle'),
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'pantheon_solr_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(
        array $form,
        FormStateInterface $form_state,
        ServerInterface $search_api_server = NULL
    ): array {
    $file_list = $this->pantheonGuzzle
      ->getQueryResult('admin/file', ['query' => ['action' => 'VIEW']]);
    $form['status'] = [
          '#type' => 'vertical_tabs',
          '#title' => $this->t('Pantheon Search Files'),
      ];
    $is_open = TRUE;
    foreach ($file_list['files'] as $filename => $fileinfo) {
      $file_contents = $this->pantheonGuzzle->getQueryResult('admin/file', [
            'query' => [
                'action' => 'VIEW',
                'file' => $filename,
            ],
        ]);
      $form[$filename] = [
            '#type' => 'details',
            '#title' => ucwords($filename),
            '#group' => 'status',
            '#weight' => substr($filename, 0, -3) === 'xml' ? -10 : 10,
        ];
      $form[$filename][] = $this->getViewSolrFile($filename, $file_contents, $is_open);
      $is_open = FALSE;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Get the vertical panel to view a config file's contents.
   *
   * @param string $filename
   *   Filename of config file on the Solr Server.
   * @param string $contents
   *   Contents of config file on the Solr Server.
   * @param bool $open
   *   Whether or not the tab appears open by default.
   *
   * @return array
   *   Form control array.
   */
  protected function getViewSolrFile(string $filename, string $contents, bool $open = FALSE): array {
    return [
          '#type' => 'details',
          '#title' => $filename,
          '#open' => $open,
          'contents' => [
              [
                  '#type' => 'markup',
                  '#markup' => sprintf('<pre>%s</pre>', Html::escape($contents)),
              ],
          ],
      ];
  }

}
