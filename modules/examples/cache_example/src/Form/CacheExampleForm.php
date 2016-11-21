<?php

namespace Drupal\cache_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * Form with examples on how to use cache.
 */
class CacheExampleForm extends FormBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The cache.default cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * Dependency injection through the constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The string translation service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current active user service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache object associated with the default bin.
   */
  public function __construct(
    RequestStack $request_stack,
    TranslationInterface $translation,
    AccountProxyInterface $current_user,
    CacheBackendInterface $cache_backend
  ) {
    $this->setRequestStack($request_stack);
    $this->setStringTranslation($translation);
    $this->currentUser = $current_user;
    $this->cacheBackend = $cache_backend;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Forms that require a Drupal service or a custom service should access
    // the service using dependency injection.
    // @link https://www.drupal.org/node/2203931.
    // Those services are passed in the $container through the static create
    // method.
    return new static(
      $container->get('request_stack'),
      $container->get('string_translation'),
      $container->get('current_user'),
      $container->get('cache.default')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cron_cache';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Log execution time.
    $start_time = microtime(TRUE);

    // Try to load the files count from cache. This function will accept two
    // arguments:
    // - cache object name (cid)
    // - cache bin, the (optional) cache bin (most often a database table) where
    //   the object is to be saved.
    //
    // cache_get() returns the cached object or FALSE if object does not exist.
    if ($cache = $this->cacheBackend->get('cache_example_files_count')) {
      /*
       * Get cached data. Complex data types will be unserialized automatically.
       */
      $files_count = $cache->data;
    }
    else {
      // If there was no cached data available we have to search filesystem.
      // Recursively get all .PHP files from Drupal's core folder.
      $files_count = count(file_scan_directory('core', '/.php/'));

      // Since we have recalculated, we now need to store the new data into
      // cache. Complex data types will be automatically serialized before
      // being saved into cache.
      // Here we use the default setting and create an unexpiring cache item.
      // See below for an example that creates an expiring cache item.
      $this->cacheBackend->set('cache_example_files_count', $files_count, CacheBackendInterface::CACHE_PERMANENT);
    }

    $end_time = microtime(TRUE);
    $duration = $end_time - $start_time;

    // Format intro message.
    $intro_message = '<p>' . $this->t("This example will search Drupal's core folder and display a count of the PHP files in it.") . ' ';
    $intro_message .= $this->t('This can take a while, since there are a lot of files to be searched.') . ' ';
    $intro_message .= $this->t('We will search filesystem just once and save output to the cache. We will use cached data for later requests.') . '</p>';
    $intro_message .= '<p>'
      . $this->t(
        '<a href="@url">Reload this page</a> to see cache in action.',
        array('@url' => $this->getRequest()->getRequestUri())
      )
      . ' ';
    $intro_message .= $this->t('You can use the button below to remove cached data.') . '</p>';

    $form['file_search'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('File search caching'),
    );
    $form['file_search']['introduction'] = array(
      '#markup' => $intro_message,
    );

    $color = empty($cache) ? 'red' : 'green';
    $retrieval = empty($cache) ? $this->t('calculated by traversing the filesystem') : $this->t('retrieved from cache');

    $form['file_search']['statistics'] = array(
      '#type' => 'item',
      '#markup' => $this->t('%count files exist in this Drupal installation; @retrieval in @time ms. <br/>(Source: <span style="color:@color;">@source</span>)', array(
        '%count' => $files_count,
        '@retrieval' => $retrieval,
        '@time' => number_format($duration * 1000, 2),
        '@color' => $color,
        '@source' => empty($cache) ? $this->t('actual file search') : $this->t('cached'),
      )
      ),
    );
    $form['file_search']['remove_file_count'] = array(
      '#type' => 'submit',
      '#submit' => array(array($this, 'expireFiles')),
      '#value' => $this->t('Explicitly remove cached file count'),
    );

    $form['expiration_demo'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Cache expiration settings'),
    );
    $form['expiration_demo']['explanation'] = array(
      '#markup' => $this->t('A cache item can be set as CACHE_PERMANENT, meaning that it will only be removed when explicitly cleared, or it can have an expiration time (a Unix timestamp).'),
    );

    $item = $this->cacheBackend->get('cache_example_expiring_item', TRUE);
    if ($item == FALSE) {
      $item_status = $this->t('Cache item does not exist');
    }
    else {
      $item_status = $item->valid ? $this->t('Cache item exists and is set to expire at %time', array('%time' => $item->data)) :
      $this->t('Cache_item is invalid');
    }

    $form['expiration_demo']['current_status'] = array(
      '#type' => 'item',
      '#title' => $this->t('Current status of cache item "cache_example_expiring_item"'),
      '#markup' => $item_status,
    );
    $form['expiration_demo']['expiration'] = array(
      '#type' => 'select',
      '#title' => $this->t('Time before cache expiration'),
      '#options' => array(
        'never_remove' => $this->t('CACHE_PERMANENT'),
        -10 => $this->t('Immediate expiration'),
        10 => $this->t('10 seconds from form submission'),
        60 => $this->t('1 minute from form submission'),
        300 => $this->t('5 minutes from form submission'),
      ),
      '#default_value' => -10,
      '#description' => $this->t('Any cache item can be set to only expire when explicitly cleared, or to expire at a given time.'),
    );
    $form['expiration_demo']['create_cache_item'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Create a cache item with this expiration'),
      '#submit' => array(array($this, 'createExpiringItem')),
    );

    $form['cache_clearing'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Expire and remove options'),
      '#description' => $this->t("We have APIs to expire cached items and also to just remove them. Unfortunately, they're all the same API, cache_clear_all"),
    );
    $form['cache_clearing']['cache_clear_type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Type of cache clearing to do'),
      '#options' => array(
        'expire' => $this->t('Remove items from the "cache" bin that have expired'),
        'remove_all' => $this->t('Remove all items from the "cache" bin regardless of expiration'),
        'remove_tag' => $this->t('Remove all items in the "cache" bin with the tag "cache_example" set to 1'),
      ),
      '#default_value' => 'expire',
    );
    // Submit button to clear cached data.
    $form['cache_clearing']['clear_expired'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Clear or expire cache'),
      '#submit' => array(array($this, 'cacheClearing')),
      '#access' => $this->currentUser->hasPermission('administer site configuration'),
    );

    return $form;
  }

  /**
   * Submit handler that explicitly clears cache_example_files_count from cache.
   */
  public function expireFiles($form, &$form_state) {
    // Clear cached data. This function will delete cached object from cache
    // bin.
    //
    // The first argument is cache id to be deleted. Since we've provided it
    // explicitly, it will be removed whether or not it has an associated
    // expiration time. The second argument (required here) is the cache bin.
    // Using cache_clear_all() explicitly in this way
    // forces removal of the cached item.
    $this->cacheBackend->delete('cache_example_files_count');

    // Display message to the user.
    drupal_set_message($this->t('Cached data key "cache_example_files_count" was cleared.'), 'status');
  }

  /**
   * Submit handler to create a new cache item with specified expiration.
   */
  public function createExpiringItem($form, &$form_state) {

    $tags = array(
      'cache_example:1',
    );

    $interval = $form_state->getValue('expiration');
    if ($interval == 'never_remove') {
      $expiration = CacheBackendInterface::CACHE_PERMANENT;
      $expiration_friendly = $this->t('Never expires');
    }
    else {
      $expiration = time() + $interval;
      $expiration_friendly = format_date($expiration);
    }
    // Set the expiration to the actual Unix timestamp of the end of the
    // required interval. Also add a tag to it to be able to clear caches more
    // precise.
    $this->cacheBackend->set('cache_example_expiring_item', $expiration_friendly, $expiration, $tags);
    drupal_set_message($this->t('cache_example_expiring_item was set to expire at %time', array('%time' => $expiration_friendly)));
  }

  /**
   * Submit handler to demonstrate the various uses of cache_clear_all().
   */
  public function cacheClearing($form, &$form_state) {
    switch ($form_state->getValue('cache_clear_type')) {
      case 'expire':
        // Here we'll remove all cache keys in the 'cache' bin that have
        // expired.
        $this->cacheBackend->garbageCollection();
        drupal_set_message($this->t('\Drupal::cache()->garbageCollection() was called, removing any expired cache items.'));
        break;

      case 'remove_all':
        // This removes all keys in a bin using a super-wildcard. This
        // has nothing to do with expiration. It's just brute-force removal.
        $this->cacheBackend->deleteAll();
        drupal_set_message($this->t('ALL entries in the "cache" bin were removed with \Drupal::cache()->deleteAll().'));
        break;

      case 'remove_tag':
        // This removes cache entries with the tag "cache_example" set to 1 in
        // the "cache".
        $tags = array(
          'cache_example:1',
        );
        Cache::invalidateTags($tags);
        drupal_set_message($this->t('Cache entries with the tag "cache_example" set to 1 in the "cache" bin were invalidated with \Drupal\Core\Cache\Cache::invalidateTags($tags).'));
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
