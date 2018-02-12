<?php

namespace Drupal\autologout\Tests;
use Drupal\simpletest\WebTestBase;

/**
 * Test session cleanup on login.
 *
 * @description Ensure that the autologout module cleans up stale sessions at login
 *
 * @group Autologout
 */
class AutologoutSessionCleanupOnLoginTest extends WebTestBase {
  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['autologout', 'node'];
  /**
   * A store references to different sessions.
   */
  protected $curlHandles = [];
  protected $loggedInUsers = [];
  protected $privilegedUser;
  protected $database;

  /**
   * SetUp() performs any pre-requisite tasks that need to happen.
   */
  public function setUp() {
    parent::setUp();
    // Create and log in our privileged user.
    $this->privilegedUser = $this->drupalCreateUser(['access content overview',
      'administer site configuration',
      'access site reports',
      'access administration pages',
      'bypass node access',
      'administer content types',
      'administer nodes',
      'administer autologout',
      'change own logout threshold',
    ]);
    $this->database = $this->container->get('database');
  }

  /**
   * Test that stale sessions are cleaned up at login.
   */
  public function testSessionCleanupAtLogin() {
    // For the purposes of the test, set the timeout periods to 5 seconds.
    $config = \Drupal::configFactory()->getEditable('autologout.settings');
    $config->set('timeout', 5)
      ->set('padding', 0)
      ->save();

    // Login in session 1.
    $this->drupalLogin($this->privilegedUser);
    // Check one active session.
    $sessions = $this->getSessions($this->privilegedUser);
    $this->assertEqual(1, count($sessions), 'After initial login there is one active session');

    // Switch sessions.
    $session1 = $this->stashSession();

    // Login to session 2.
    $this->drupalLogin($this->privilegedUser);

    // Check two active sessions.
    $sessions = $this->getSessions($this->privilegedUser);
    $this->assertEqual(2, count($sessions), 'After second login there is now two active session');

    $this->stashSession();

    // Switch sessions.
    // Wait for sessions to expire.
    sleep(6);

    // Login to session 3.
    $this->drupalLogin($this->privilegedUser);

    // Check one active session.
    $sessions = $this->getSessions($this->privilegedUser);
    $this->assertEqual(1, count($sessions), 'After third login, there is 1 active session, two stale sessions were cleaned up.');

    // Switch back to session 1 and check no longer logged in.
    $this->restoreSession($session1);
    $this->drupalGet('node');
    $this->assertNoText(t('Log out'), 'User is no longer logged in on session 1.');

    $this->closeAllSessions();
  }

  /**
   * Get active sessions for given user.
   */
  public function getSessions($account) {
    // Check there is one session in the sessions table.
    $result = $this->database->select('sessions', 's')
      ->fields('s')
      ->condition('uid', $account->id())
      ->orderBy('timestamp', 'DESC')
      ->execute();
    $sessions = [];
    foreach ($result as $session) {
      $sessions[] = $session;
    }

    return $sessions;
  }

  /**
   * Initialise a new unique session.
   *
   * @return string
   *   Unique identifier for the session just stored.
   *   It is the cookiefile name.
   */
  public function stashSession() {
    if (empty($this->cookieFile)) {
      // No session to stash.
      return 0;
    }

    // The session_id is the current cookieFile.
    $session_id = $this->cookieFile;

    $this->curlHandles[$session_id] = $this->curlHandle;
    $this->loggedInUsers[$session_id] = $this->loggedInUser;

    // Reset Curl.
    unset($this->curlHandle);
    $this->loggedInUser = FALSE;

    // Set a new unique cookie filename.
    do {
      $this->cookieFile = $this->originalFileDirectory . '/' . $this->randomMachineName() . '.jar';
    } while (isset($this->curlHandles[$this->cookieFile]));

    return $session_id;
  }

  /**
   * Restore a previously stashed session.
   *
   * @param string $session_id
   *   The session to restore as returned by stashSession();
   *   This is also the path to the cookie file.
   *
   * @return string
   *   The old session id that was replaced.
   */
  public function restoreSession($session_id) {
    $old_session_id = NULL;

    if (isset($this->curlHandle)) {
      $old_session_id = $this->stashSession();
    }

    // Restore the specified session.
    $this->curlHandle = $this->curlHandles[$session_id];
    $this->cookieFile = $session_id;
    $this->loggedInUser = $this->loggedInUsers[$session_id];

    return $old_session_id;
  }

  /**
   * Close all stashed sessions and the current session.
   */
  public function closeAllSessions() {
    foreach ($this->curlHandles as $curl_handle) {
      if (isset($curl_handle)) {
        curl_close($curl_handle);
      }
    }

    // Make the server forget all sessions.
    $this->database->truncate('sessions')->execute();

    $this->curlHandles = [];
    $this->loggedInUsers = [];
    $this->loggedInUser = FALSE;
    $this->cookieFile = $this->originalFileDirectory . '/' . $this->randomMachineName() . '.jar';
    unset($this->curlHandle);
  }

}
