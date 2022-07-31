<?php

namespace Drupal\upgrade_status\Util;

use Drupal\Core\Database\Connection;

class CorrectDbServerVersion {

  /**
   * Database connection
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Cached database version (Used in Drupal 8.9.x only)
   *
   * @var string
   */
  protected $databaseServerVersion;

  /**
   * Constructs a Drupal\upgrade_status\Util\MariaDbServerVersion.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection
   */
  public function __construct(
    Connection $database
  ) {
    $this->database = $database;
  }

  /**
   * Returns corrected version of database.
   *
   * When running on MariaDb on Drupal 8.9.x, the version
   * from $database->version() is not reported correctly.
   * This is fixed in Drupal 9 directly in the Mysql database
   * driver. In Drupal 8.9.x, however, the fix exists only in
   * the Status Report page. We therefore need to replicate
   * the same logic here.
   *
   * @see https://www.drupal.org/project/drupal/issues/3213482
   *
   * @return string
   *   Returns the MariaDb server version if applicable, or the passed-in
   *   version if not.
   */
  public function getCorrectedDbServerVersion($version) {
    if ($this->isMariaDb()) {
      return $this->getMariaDbVersionMatch();
    }
    return $version;
  }

  /**
   * Determines whether the MySQL distribution is MariaDB or not.
   *
   * @return bool
   *   Returns TRUE if the distribution is MariaDB, or FALSE if not.
   */
  protected function isMariaDb(): bool {
    return (bool) $this->getMariaDbVersionMatch();
  }

  /**
   * Gets the MariaDB portion of the server version.
   *
   * @return string
   *   The MariaDB portion of the server version if present, or NULL if not.
   */
  protected function getMariaDbVersionMatch(): ?string {
    // MariaDB may prefix its version string with '5.5.5-', which should be
    // ignored.
    // @see https://github.com/MariaDB/server/blob/f6633bf058802ad7da8196d01fd19d75c53f7274/include/mysql_com.h#L42.
    $regex = '/^(?:5\.5\.5-)?(\d+\.\d+\.\d+.*-mariadb.*)/i';

    preg_match($regex, $this->getDatabaseServerVersion(), $matches);
    return (empty($matches[1])) ? NULL : $matches[1];
  }

  /**
   * Gets the database server version.
   *
   * @return string
   *   The database server version.
   */
  protected function getDatabaseServerVersion(): string {
    if (!$this->databaseServerVersion) {
      $this->databaseServerVersion = $this->database->version();
    }
    return $this->databaseServerVersion;
  }
}
