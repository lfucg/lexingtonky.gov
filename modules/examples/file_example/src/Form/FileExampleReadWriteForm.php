<?php

namespace Drupal\file_example\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * File test form class.
 *
 * @ingroup file_example
 */
class FileExampleReadWriteForm extends FormBase {

  /**
   * Interface of the "state" service for site-specific data.
   *
   * @var StateInterface
   */
  protected $state;

  /**
   * Object used to get request data, such as the session.
   *
   * @var RequestStack
   */
  protected $requestStack;

  /**
   * Service for manipulating a file system.
   *
   * @var FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Service for fetching a stream wrapper for a file or directory.
   *
   * @var StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * Indicator variable for the session:// scheme being available.
   *
   * @vqr bool
   */
  protected $sessionSchemeEnabled;

  /**
   * Service for invoking hooks and other module operations.
   *
   * @var ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new FileExampleReadWriteForm page.
   *
   * @param StateInterface $state
   *   Storage interface for state data.
   * @param FileSystemInterface $file_system
   *   Interface for common file system operations.
   * @param StreamWrapperManagerInterface $stream_wrapper_manager
   *   Interface to obtain stream wrappers used to manipulate a given file
   *   scheme.
   * @param ModuleHandlerInterface $module_handler
   *   Interface to get information about the status of modules and other
   *   extensions.
   * @param RequestStack $request_stack
   *   Access to the current request, including to session objects.
   */
  public function __construct(
    StateInterface $state,
    FileSystemInterface $file_system,
    StreamWrapperManagerInterface $stream_wrapper_manager,
    ModuleHandlerInterface $module_handler,
    RequestStack $request_stack
  ) {
    $this->state = $state;
    $this->fileSystem = $file_system;
    $this->moduleHandler = $module_handler;
    $this->requestStack = $request_stack;
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->sessionSchemeEnabled = $this->moduleHandler->moduleExists('stream_wrapper_example');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $state = $container->get('state');
    $file_system = $container->get('file_system');
    $module_handler = $container->get('module_handler');
    $request_stack = $container->get('request_stack');
    $stream_wrapper_manager = $container->get('stream_wrapper_manager');
    return new static($state, $file_system, $stream_wrapper_manager, $module_handler, $request_stack);
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'file_example_readwrite';
  }

  /**
   * Get the default file.
   *
   * This appears in the first block of the form.
   *
   * @return string
   *   The URI of the default file.
   */
  protected function getDefaultFile() {
    $fall_back_value = $this->sessionSchemeEnabled ? 'session://drupal.txt' : 'public://drupal.txt';
    $default_file = $this->state->get('file_example_default_file', $fall_back_value);
    return $default_file;
  }

  /**
   * Fetch a SessionWrapper object.
   *
   * This is used to change relevant attributes of the Session. This will return
   * FALSE if the stream_wrapper_example is not enabled.
   *
   * @return Drupal\stream_wrapper_example\StreamWrapper\SessionWrapper|bool
   *   Wrapper object to manipulate the SESSION storage or FALSE if the session
   *   wrapper is unavailable.
   *
   * @todo Update this to be meaningful when stream_wrapper_example is
   *   completed. https://www.drupal.org/node/2638290
   */
  protected function getSessionWrapper() {
    return FALSE;
  }

  /**
   * Set the default file.
   *
   * Set a default URI of the file used for read and write operations.
   *
   * @param string $uri
   *   URI to save for future display in the form.
   */
  protected function setDefaultFile($uri) {
    $this->state->set('file_example_default_file', (string) $uri);
  }

  /**
   * Get the default directory.
   *
   * @return string
   *   The URI of the default directory.
   */
  protected function getDefaultDirectory() {
    $fall_back_value = $this->sessionSchemeEnabled ? 'session://directory1' : 'public://directory1';
    $default_directory = $this->state->get('file_example_default_directory', $fall_back_value);
    return $default_directory;
  }

  /**
   * Set the default directory.
   *
   * @param string $uri
   *   URI to save for later form display.
   */
  protected function setDefaultDirectory($uri) {
    $this->state->set('file_example_default_directory', (string) $uri);
  }

  /**
   * Utility function to check for and return a managed file.
   *
   * In this demonstration code we don't necessarily know if a file is managed
   * or not, so often need to check to do the correct behavior. Normal code
   * would not have to do this, as it would be working with either managed or
   * unmanaged files.
   *
   * @param string $uri
   *   The URI of the file, like public://test.txt.
   *
   * @return FileInterface|bool
   *   A file object that matches the URI, or FALSE if not a managed file.
   *
   * @todo This should still work. An entity query could be used instead.
   *   May be other alternatives.
   */
  private static function getManagedFile($uri) {
    $fid = Database::getConnection('default')->query(
      'SELECT fid FROM {file_managed} WHERE uri = :uri',
      array(':uri' => $uri)
    )->fetchField();
    if (!empty($fid)) {
      $file_object = File::load($fid);
      return $file_object;
    }
    return FALSE;
  }

  /**
   * Prepare Url objects to prevent exceptions by the URL generator.
   *
   * Helper function to get us an external URL if this is legal, and to catch
   * the exception Drupal throws if this is not possible.
   *
   * In Drupal 8, the URL generator is very sensitive to how you set things
   * up, and some functions, in particular LinkGeneratorTrait::l(), will throw
   * exceptions if you deviate from what's expected. This function will raise
   * the chances your URL will be valid, and not do this.
   *
   * @param \Drupal\file\Entity\File|string $file_object
   *   A file entity object.
   *
   * @return \Drupal\Core\Url
   *   A Url object that can be displayed as an internal URL.
   */
  protected function getExternalUrl($file_object) {
    if ($file_object instanceof FileInterface) {
      $uri = $file_object->getFileUri();
    }
    else {
      // A little tricky, since file.inc is a little inconsistent, but often
      // this is a Uri.
      $uri = file_create_url($file_object);
    }

    try {
      // If we have been given a PHP stream URI, ask the stream itself if it
      // knows how to create an external URL.
      $wrapper = $this->streamWrapperManager->getViaUri($uri);
      if ($wrapper) {
        $external_url = $wrapper->getExternalUrl();
        // Some streams may not have the concept of an external URL, so we
        // check here to make sure, since the example assumes this.
        if ($external_url) {
          $url = Url::fromUri($external_url);
          return $url;
        }
      }
      else {
        $url = Url::fromUri($uri);
        // If we did not throw on ::fromUri (you can), we return the URL.
        return $url;
      }
    }
    catch (\Exception $e) {
      return FALSE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $default_file = $this->getDefaultFile();
    $default_directory = $this->getDefaultDirectory();

    $form['description'] = array(
      '#markup' => $this->t('This form demonstrates the Drupal 8 file api. Experiment with the form, and then look at the submit handlers in the code to understand the file api.'),
    );

    $form['write_file'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Write to a file'),
    );
    $form['write_file']['write_contents'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Enter something you would like to write to a file'),
      '#default_value' => $this->t('Put some text here or just use this text'),
    );

    $form['write_file']['destination'] = array(
      '#type' => 'textfield',
      '#default_value' => $default_file,
      '#title' => $this->t('Optional: Enter the streamwrapper saying where it should be written'),
      '#description' => $this->t('This may be public://some_dir/test_file.txt or private://another_dir/some_file.txt, for example. If you include a directory, it must already exist. The default is "public://". Since this example supports session://, you can also use something like session://somefile.txt.'),
    );

    $form['write_file']['managed_submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Write managed file'),
      '#submit' => array('::handleManagedFile'),
    );
    $form['write_file']['unmanaged_submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Write unmanaged file'),
      '#submit' => array('::handleUnmanagedFile'),
    );
    $form['write_file']['unmanaged_php'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Unmanaged using PHP'),
      '#submit' => array('::handleUnmanagedPhp'),
    );

    $form['fileops'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Read from a file'),
    );
    $form['fileops']['fileops_file'] = array(
      '#type' => 'textfield',
      '#default_value' => $default_file,
      '#title' => $this->t('Enter the URI of a file'),
      '#description' => $this->t('This must be a stream-type description like public://some_file.txt or http://drupal.org or private://another_file.txt or (for this example) session://yet_another_file.txt.'),
    );
    $form['fileops']['read_submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Read the file and store it locally'),
      '#submit' => array('::handleFileRead'),
    );
    $form['fileops']['delete_submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Delete file'),
      '#submit' => array('::handleFileDelete'),
    );
    $form['fileops']['check_submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Check to see if file exists'),
      '#submit' => array('::handleFileExists'),
    );

    $form['directory'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Create or prepare a directory'),
    );

    $form['directory']['directory_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Directory to create/prepare/delete'),
      '#default_value' => $default_directory,
      '#description' => $this->t('This is a directory as in public://some/directory or private://another/dir.'),
    );
    $form['directory']['create_directory'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Create directory'),
      '#submit' => array('::handleDirectoryCreate'),
    );
    $form['directory']['delete_directory'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Delete directory'),
      '#submit' => array('::handleDirectoryDelete'),
    );
    $form['directory']['check_directory'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Check to see if directory exists'),
      '#submit' => array('::handleDirectoryExists'),
    );

    $form['debug'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Debugging'),
    );
    // The Session Wrapper Exampple is not yet committed, so
    // we hide this button until this happens.
    $form['debug']['show_raw_session'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Show raw $_SESSION contents'),
      '#submit' => array('::handleShowSession'),
      '#access' => $this->sessionSchemeEnabled,
    );
    $form['debug']['reset_session'] = array(
      '#type' => 'submit',
      '#value' => t('Reset the Session'),
      '#submit' => array('::handleResetSession'),
    );

    return $form;
  }

  /**
   * Submit handler to write a managed file.
   *
   * A "managed file" is a file that Drupal tracks as a file entity.  It's the
   * standard way Drupal manages files in file fields and elsewhere.
   *
   * The key functions used here are:
   * - file_save_data(), which takes a buffer and saves it to a named file and
   *   also creates a tracking record in the database and returns a file object.
   *   In this function we use FILE_EXISTS_RENAME (the default) as the argument,
   *   which means that if there's an existing file, create a new non-colliding
   *   filename and use it.
   * - file_create_url(), which converts a URI in the form public://junk.txt or
   *   private://something/test.txt into a URL like
   *   http://example.com/sites/default/files/junk.txt.
   *    * @param array $form
   *   An associative array containing the structure of the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function handleManagedFile(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $data = $form_values['write_contents'];
    $uri = !empty($form_values['destination']) ? $form_values['destination'] : NULL;

    // Managed operations work with a file object.
    $file_object = \file_save_data($data, $uri, FILE_EXISTS_RENAME);
    if (!empty($file_object)) {
      $url = $this->getExternalUrl($file_object);
      $this->setDefaultFile($file_object->getFileUri());
      $file_data = $file_object->toArray();
      if ($url) {
        drupal_set_message(
         $this->t('Saved managed file: %file to destination %destination (accessible via <a href=":url">this URL</a>, actual uri=<span id="uri">@uri</span>)',
            array(
              '%file' => print_r($file_data, TRUE),
              '%destination' => $uri,
              '@uri' => $file_object->getFileUri(),
              ':url' => $url->toString(),
            )
          )
        );
      }
      else {
        // This Uri is not routable, so we cannot give a link to it.
        drupal_set_message(
         $this->t('Saved managed file: %file to destination %destination (no URL, since this stream type does not support it)',
            array(
              '%file' => print_r($file_data, TRUE),
              '%destination' => $uri,
              '@uri' => $file_object->getFileUri(),
            )
          )
        );

      }
    }
    else {
      drupal_set_message(t('Failed to save the managed file'), 'error');
    }

  }

  /**
   * Submit handler to write an unmanaged file.
   *
   * An unmanaged file is a file that Drupal does not track.  A standard
   * operating system file, in other words.
   *
   * The key functions used here are:
   * - file_unmanaged_save_data(), which takes a buffer and saves it to a named
   *   file, but does not create any kind of tracking record in the database.
   *   This example uses FILE_EXISTS_REPLACE for the third argument, meaning
   *   that if there's an existing file at this location, it should be replaced.
   * - file_create_url(), which converts a URI in the form public://junk.txt or
   *   private://something/test.txt into a URL like
   *   http://example.com/sites/default/files/junk.txt.
   *    * @param array $form
   *   An associative array containing the structure of the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function handleUnmanagedFile(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $data = $form_values['write_contents'];
    $destination = !empty($form_values['destination']) ? $form_values['destination'] : NULL;

    // With the unmanaged file we just get a filename back.
    $filename = file_unmanaged_save_data($data, $destination, FILE_EXISTS_REPLACE);
    if ($filename) {
      $url = $this->getExternalUrl($filename);
      $this->setDefaultFile($filename);
      if ($url) {
        drupal_set_message(
         $this->t('Saved file as %filename (accessible via <a href=":url">this URL</a>, uri=<span id="uri">@uri</span>)',
            array(
              '%filename' => $filename,
              '@uri' => $filename,
              ':url' => $url->toString(),
            )
          )
        );
      }
      else {
        drupal_set_message(
         $this->t('Saved file as %filename (not accessible externally)',
            array(
              '%filename' => $filename,
              '@uri' => $filename,
            )
          )
        );
      }
    }
    else {
      drupal_set_message(t('Failed to save the file'), 'error');
    }
  }

  /**
   * Submit handler to write an unmanaged file using plain PHP functions.
   *
   * The key functions used here are:
   * - file_unmanaged_save_data(), which takes a buffer and saves it to a named
   *   file, but does not create any kind of tracking record in the database.
   * - file_create_url(), which converts a URI in the form public://junk.txt or
   *   private://something/test.txt into a URL like
   *   http://example.com/sites/default/files/junk.txt.
   * - drupal_tempnam() generates a temporary filename for use.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function handleUnmanagedPhp(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $data = $form_values['write_contents'];
    $destination = !empty($form_values['destination']) ? $form_values['destination'] : NULL;

    if (empty($destination)) {
      // If no destination has been provided, use a generated name.
      $destination = $this->fileSystem->tempnam('public://', 'file');
    }

    // With all traditional PHP functions we can use the stream wrapper notation
    // for a file as well.
    $fp = fopen($destination, 'w');

    // To demonstrate the fact that everything is based on streams, we'll do
    // multiple 5-character writes to put this to the file. We could easily
    // (and far more conveniently) write it in a single statement with
    // fwrite($fp, $data).
    $length = strlen($data);
    $write_size = 5;
    for ($i = 0; $i < $length; $i += $write_size) {
      $result = fwrite($fp, substr($data, $i, $write_size));
      if ($result === FALSE) {
        drupal_set_message(t('Failed writing to the file %file', array('%file' => $destination)), 'error');
        fclose($fp);
        return;
      }
    }
    $url = $this->getExternalUrl($destination);
    $this->setDefaultFile($destination);
    if ($url) {
      drupal_set_message(
       $this->t('Saved file as %filename (accessible via <a href=":url">this URL</a>, uri=<span id="uri">@uri</span>)',
          array(
            '%filename' => $destination,
            '@uri' => $destination,
            ':url' => $url->toString(),
          )
        )
      );
    }
    else {
      drupal_set_message(
       $this->t('Saved file as %filename (not accessible externally)',
          array(
            '%filename' => $destination,
            '@uri' => $destination,
          )
        )
      );
    }

  }

  /**
   * Submit handler for reading a stream wrapper.
   *
   * Drupal now has full support for PHP's stream wrappers, which means that
   * instead of the traditional use of all the file functions
   * ($fp = fopen("/tmp/some_file.txt");) far more sophisticated and generalized
   * (and extensible) things can be opened as if they were files. Drupal itself
   * provides the public:// and private:// schemes for handling public and
   * private files. PHP provides file:// (the default) and http://, so that a
   * URL can be read or written (as in a POST) as if it were a file. In
   * addition, new schemes can be provided for custom applications. The Stream
   * Wrapper Example, if installed, impleents a custom 'session' scheme that
   * you can test with this example.
   *
   * Here we take the stream wrapper provided in the form. We grab the
   * contents with file_get_contents(). Notice that's it's as simple as that:
   * file_get_contents("http://example.com") or
   * file_get_contents("public://somefile.txt") just works. Although it's
   * not necessary, we use file_unmanaged_save_data() to save this file locally
   * and then find a local URL for it by using file_create_url().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function handleFileRead(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $uri = $form_values['fileops_file'];

    if (empty($uri) or !is_file($uri)) {
      drupal_set_message(t('The file "%uri" does not exist', array('%uri' => $uri)), 'error');
      return;
    }

    // Make a working filename to save this by stripping off the (possible)
    // file portion of the streamwrapper. If it's an evil file extension,
    // file_munge_filename() will neuter it.
    $filename = file_munge_filename(preg_replace('@^.*/@', '', $uri), '', TRUE);
    $buffer = file_get_contents($uri);

    if ($buffer) {
      $sourcename = file_unmanaged_save_data($buffer, 'public://' . $filename);
      if ($sourcename) {
        $url = $this->getExternalUrl($sourcename);
        $this->setDefaultFile($sourcename);
        if ($url) {
          drupal_set_message(
           $this->t('The file was read and copied to %filename which is accessible at <a href=":url">this URL</a>',
              array(
                '%filename' => $sourcename,
                ':url' => $url->toString(),
              )
            )
          );
        }
        else {
          drupal_set_message(
           $this->t('The file was read and copied to %filename (not accessible externally)',
              array(
                '%filename' => $sourcename,
              )
            )
          );

        }
      }
      else {
        drupal_set_message(t('Failed to save the file'));
      }
    }
    else {
      // We failed to get the contents of the requested file.
      drupal_set_message(t('Failed to retrieve the file %file', array('%file' => $uri)));
    }

  }

  /**
   * Submit handler to delete a file.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function handleFileDelete(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $uri = $form_values['fileops_file'];

    // Since we don't know if the file is managed or not, look in the database
    // to see. Normally, code would be working with either managed or unmanaged
    // files, so this is not a typical situation.
    $file_object = self::getManagedFile($uri);

    // If a managed file, use file_delete().
    if (!empty($file_object)) {
      // While file_delete should return FALSE on failure,
      // it can currently throw an exception on certain cache states.
      try {
        // This no longer returns a result code.  If things go bad,
        // it will throw an exception:
        file_delete($file_object->id());
        drupal_set_message(t('Successfully deleted managed file %uri', array('%uri' => $uri)));
        $this->setDefaultFile($uri);
      }
      catch (\Exception $e) {
        drupal_set_message(t('Failed deleting managed file %uri. Result was %result',
          array(
            '%uri' => $uri,
            '%result' => print_r($e->getMessage(), TRUE),
          )
        ), 'error');
      }
    }
    // Else use file_unmanaged_delete().
    else {
      $result = file_unmanaged_delete($uri);
      if ($result !== TRUE) {
        drupal_set_message(t('Failed deleting unmanaged file %uri', array('%uri' => $uri, 'error')));
      }
      else {
        drupal_set_message(t('Successfully deleted unmanaged file %uri', array('%uri' => $uri)));
        $this->setDefaultFile('file_example_default_file', $uri);
      }
    }
  }

  /**
   * Submit handler to check existence of a file.
   */
  public function handleFileExists(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $uri = $form_values['fileops_file'];
    if (is_file($uri)) {
      drupal_set_message(t('The file %uri exists.', array('%uri' => $uri)));
    }
    else {
      drupal_set_message(t('The file %uri does not exist.', array('%uri' => $uri)));
    }
  }

  /**
   * Submit handler for directory creation.
   *
   * Here we create a directory and set proper permissions on it using
   * file_prepare_directory().
   */
  public function handleDirectoryCreate(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $directory = $form_values['directory_name'];

    // The options passed to file_prepare_directory are a bitmask, so we can
    // specify either FILE_MODIFY_PERMISSIONS (set permissions on the
    // directory), FILE_CREATE_DIRECTORY, or both together:
    // FILE_MODIFY_PERMISSIONS | FILE_CREATE_DIRECTORY.
    // FILE_MODIFY_PERMISSIONS will set the permissions of the directory by
    // by default to 0755, or to the value of the variable
    // 'file_chmod_directory'.
    if (!file_prepare_directory($directory, FILE_MODIFY_PERMISSIONS | FILE_CREATE_DIRECTORY)) {
      drupal_set_message(t('Failed to create %directory.', array('%directory' => $directory)), 'error');
    }
    else {
      $result = is_dir($directory);
      drupal_set_message(t('Directory %directory is ready for use.', array('%directory' => $directory)));
      $this->setDefaultDirectory($directory);
    }
  }

  /**
   * Submit handler for directory deletion.
   *
   * @see file_unmanaged_delete_recursive()
   */
  public function handleDirectoryDelete(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $directory = $form_values['directory_name'];

    $result = file_unmanaged_delete_recursive($directory);
    if (!$result) {
      drupal_set_message(t('Failed to delete %directory.', array('%directory' => $directory)), 'error');
    }
    else {
      drupal_set_message(t('Recursively deleted directory %directory.', array('%directory' => $directory)));
      $this->setDefaultDirectory($directory);
    }
  }

  /**
   * Submit handler to test directory existence.
   *
   * This actually just checks to see if the directory is writable.
   *
   * @param array $form
   *   FormAPI form.
   * @param FormStateInterface $form_state
   *   FormAPI form state.
   */
  public function handleDirectoryExists(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $directory = $form_values['directory_name'];
    $result = is_dir($directory);
    if (!$result) {
      drupal_set_message(t('Directory %directory does not exist.', array('%directory' => $directory)));
    }
    else {
      drupal_set_message(t('Directory %directory exists.', array('%directory' => $directory)));
    }
  }

  /**
   * Utility submit function to show the contents of $_SESSION.
   */
  public function handleShowSession(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    // If the devel module is installed, use it's nicer message format.
    if ($this->moduleHandler->moduleExists('devel')) {
      // @codingStandardsIgnoreStart
      // We wrap this in the coding standards ignore tags because the use of
      // function dsm() is discouraged.
      dsm($this->getStoredData(), $this->t('Entire $_SESSION["file_example"]'));
      // @codingStandardsIgnoreEnd
    }
    else {
      drupal_set_message('<pre>' . print_r($this->getStoredData(), TRUE) . '</pre>');
    }
  }

  /**
   * Utility submit function to reset the demo.
   *
   * @param array $form
   *   FormAPI form.
   * @param FormStateInterface $form_state
   *   FormAPI form state.
   *
   * @todo Note this does NOT clear any managed file references in Drupal's DB.
   *       It might be a good idea to add this.
   */
  public function handleResetSession(array &$form, FormStateInterface $form_state) {
    $this->state->delete('file_example_default_file');
    $this->state->delete('file_example_default_directory');
    $this->clearStoredData();
    drupal_set_message('Session reset.');
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // We don't use this, but the interface requires us to implement it.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // We don't use this, but the interface requires us to implement it.
  }

  /**
   * Get our stored data for display.
   */
  protected function getStoredData() {
    $handle = $this->getSessionWrapper();
    if ($handle) {
      return $handle->getPath('');
    }
    return "SESSION STORE IS NOT ENABLED";
  }

  /**
   * Reset our stored data.
   */
  protected function clearStoredData() {
    $handle = $this->getSessionWrapper();
    if ($handle) {
      return $handle->cleanUpStore();
    }
  }

}
