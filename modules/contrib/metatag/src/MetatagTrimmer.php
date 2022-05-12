<?php

namespace Drupal\metatag;

use PHPUnit\Framework\Exception;

/**
 * MetatagTrimmer service class for trimming metatags.
 */
class MetatagTrimmer {

  /**
   * Trims a given string after the word on the given length.
   *
   * @param string $string
   *   The string to trim.
   * @param int $maxlength
   *   The maximum length where the string approximately gets trimmed.
   *
   * @return string
   *   The trimmed string.
   */
  public function trimAfterValue($string, $maxlength) {
    $spacePos = strpos($string, ' ', $maxlength - 1);
    if (FALSE === $spacePos) {
      return $string;
    }
    $subString = substr($string, 0, $spacePos);

    return trim($subString);
  }

  /**
   * Trims a given string before the word on the given length.
   *
   * @param string $string
   *   The string to trim.
   * @param int $maxlength
   *   The maximum length where the string approximately gets trimmed.
   *
   * @return string
   *   The trimmed string.
   */
  public function trimBeforeValue($string, $maxlength) {
    $subString = substr($string, 0, $maxlength + 1);
    if (' ' === substr($subString, -1)) {
      return trim($subString);
    }
    $spacePos = strrpos($subString, ' ', 0);
    if (FALSE === $spacePos) {
      return $string;
    }
    $returnedString = substr($string, 0, $spacePos);

    return trim($returnedString);
  }

  /**
   * Trims a value based on the given length and the given method.
   *
   * @param string $value
   *   The string to trim.
   * @param int $maxlength
   *   The maximum length where the string approximately gets trimmed.
   * @param string $method
   *   The trim method to use for the trimming.
   *   Allowed values: 'afterValue', 'onValue' and 'beforeValue'.
   */
  public function trimByMethod($value, $maxlength, $method) {
    if (empty($value) || empty($maxlength)) {
      return $value;
    }

    switch ($method) {
      case 'afterValue':
        return $this->trimAfterValue($value, $maxlength);

      case 'onValue':
        return trim(substr($value, 0, $maxlength));

      case 'beforeValue':
        return $this->trimBeforeValue($value, $maxlength);

      default:
        throw new Exception('Unknown trimming method: ' . $method);
    }
  }

}
