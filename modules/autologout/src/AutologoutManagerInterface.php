<?php

/**
 * @file
 * Definition of Drupal\autologout\AutologoutHelperInterface.
 */

namespace Drupal\autologout;

/**
 * Interface for AutologoutManager.
 */
interface AutologoutManagerInterface {

  /**
   * Get the timer HTML markup.
   *
   * @return string
   *   HTML to insert a countdown timer.
   */
  public function createTimer();

  /**
   * Get the time remaining before logout.
   *
   * @return int
   *   Number of seconds remaining.
   */
  public function getRemainingTime();

  /**
   * Go through every role to get timeout value, default is the global timeout.
   *
   * @return int
   *   Number of seconds timeout set for the user role.
   */
  public function getRoleTimeout();

  /**
   * Get a user's timeout in seconds.
   *
   * @param int $uid
   *   (Optional) Provide a user's uid to get the timeout for.
   *   Default is the logged in user.
   *
   * @return int
   *   The number of seconds the user can be idle for before being logged out.
   *   A value of 0 means no timeout.
   */
  public function getUserTimeout($uid = NULL);

  /**
   * Perform Logout.
   *
   * Helper to perform the actual logout. Destroys the session of the logged
   * in user.
   */
  public function logout();

  /**
   * Helper to determine if a given user should be autologged out.
   */
  public function logoutRole($user);

  /**
   * Display the inactivity message if required when the user is logged out.
   */
  public function inactivityMessage();

  /**
   * Determine if autologout should be prevented.
   *
   * @return bool
   *   TRUE if there is a reason not to autologout
   *   the current user on the current page.
   */
  public function preventJs();

  /**
   * Determine if connection should be refreshed.
   *
   * @return bool
   *   TRUE if something about the current context should keep the connection
   *   open. FALSE and the standard countdown to autologout applies.
   */
  public function refreshOnly();

}
