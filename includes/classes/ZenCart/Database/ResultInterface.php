<?php
/**
 * ResultInterface
 *
 * @package   Database
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version   1.6.0
 */

namespace ZenCart\Database;

/**
 * A common object contract for database result sets
 *
 * By implementing SeekableIterator, results can be iterated over using foreach.
 *
 * <pre>
 * foreach ($result as $index => $fields) {
 *   echo $fields['column_name'] . PHP_EOL;
 * }
 * </pre>
 *
 * @package Database
 * @since   1.6.0
 */
interface ResultInterface extends \SeekableIterator, \Countable, \Serializable {

  /**
   * Randomize the data
   *
   * @return ResultInterface
   */
  public function randomize();

  /**
   * Convert the result to an array
   *
   * @return array
   */
  public function toArray();

  /**
   * Initialize the next set of data
   *
   * @return void
   * @deprecated
   */
  public function MoveNext();

  /**
   * Initialize a random set of data
   *
   * @return void
   * @deprecated
   */
  public function MoveNextRandom();

  /**
   * Get the total number of data sets
   *
   * @return integer
   * @deprecated
   */
  public function RecordCount();

  /**
   * Initialize a specific set of data
   *
   * @return void
   * @deprecated
   */
  public function Move($row);

}
