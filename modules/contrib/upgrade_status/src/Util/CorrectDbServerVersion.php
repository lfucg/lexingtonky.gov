<?php

namespace Drupal\upgrade_status\Util;

use Drupal\Core\Database\Connection;
use Drupal\upgrade_status\ProjectCollector;

class DatabaseServerMetadataExtractor {

  /**
   * Database connection
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * MySql database version.
   *
   * @var string
   */
  protected $mysqlVersion;

  /**
   * Constructs a Drupal\upgrade_status\Util\MariaDbServerVersion.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Returns version of database.
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
   *   Returns the MariaDb server version if applicable, or the default
   *   version if not.
   */
  public function getVersion() {
    if ($this->isMariaDb()) {
      return $this->getMariaDbVersion();
    }
    return $this->database->version();
  }

  /**
   * Returns type of database.
   *
   * @return string
   *   Returns specific to MariaDb type if applicable, or the default
   *   database server type if not.
   */
  public function getType() {
    if ($this->isMariaDb()) {
      return 'mariadb';
    }
    return $this->database->databaseType();
  }

  /**
   * Determines whether the MySQL distribution is MariaDB or not.
   *
   * @return bool
   *   Returns TRUE if the distribution is MariaDB, or FALSE if not.
   */
  protected function isMariaDb(): bool {
    if ($this->database->databaseType() !== 'mysql' || ProjectCollector::getDrupalCoreMajorVersion() !== 8) {
      return FALSE;
    }
    // If running on Drupal 8, the mysql driver might
    // mis-report the database version.
    return (bool) $this->getMariaDbVersion();
  }

  /**
   * Gets the MariaDB portion of the server version.
   *
   * @return string
   *   The MariaDB portion of the server version if present, or NULL if not.
   */
  protected function getMariaDbVersion(): ?string {
    // MariaDB may prefix its version string with '5.5.5-', which should be
    // ignored.
    // @see https://github.com/MariaDB/server/blob/f6633bf058802ad7da8196d01fd19d75c53f7274/include/mysql_com.h#L42.
    $regex = '/^(?:5\.5\.5-)?(\d+\.\d+\.\d+.*-mariadb.*)/i';

    preg_match($regex, $this->getMysqlDbVersion(), $matches);
    return (empty($matches[1])) ? NULL : $matches[1];
  }

  /**
   * Returns MySql database server version.
   *
   * @return string
   *   MySql database server version.
   */
  protected function getMysqlDbVersion(): string {
    if (!$this->mysqlVersion) {
      $this->mysqlVersion = $this->database->query('SELECT VERSION()')->fetchColumn();
    }
    return $this->mysqlVersion;
  }
}
