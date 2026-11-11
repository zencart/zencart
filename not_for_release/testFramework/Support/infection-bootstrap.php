<?php

/**
 * Included by Infection (see "bootstrap" in infection.json5) in its own process, which does not run
 * the PHPUnit bootstrap. Some mutated classes refuse to load unless the application context
 * constants exist (for example category_tree.php dies without IS_ADMIN_FLAG), and Infection loads
 * them while it analyses the source files.
 */
defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', false);
