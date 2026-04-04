<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\PluginSupport;

/**
 * @since ZC v1.5.7
 */
class PluginErrorContainer
{

    /**
     * $logger "null" the logger to use.
     * @var object
     */
    protected $logger;
    /**
     * $logErrors is an array of error messages
     */
    protected array $logErrors = [];
    /**
     * $friendlyErrors is a subset of $logErrors that have a friendly message (a known error with additional information)
     */
    protected array $friendlyErrors = [];

    public function __construct($logger = null)
    {
        $this->logger = $logger;
        $this->logErrors = [];
        $this->friendlyErrors = [];
    }

    /**
     * @since ZC v1.5.7
     */
    public function hasLogErrors()
    {
        return (count($this->logErrors));
    }

    /**
     * @since ZC v1.5.7
     */
    public function hasFriendlyErrors()
    {
        return (count($this->friendlyErrors));
    }

    /**
     * @since ZC v1.5.7
     */
    public function addError($logSeverity, $logMessage, $useLogMessageForFriendly = false, $friendlyMessage = '')
    {
        if ($useLogMessageForFriendly) {
            $friendlyMessage = $logMessage;
        }
        $this->logErrors[] = $logMessage;
        if ($friendlyMessage === '') return;
        $friendlyHash = hash('md5', $friendlyMessage);
        $this->friendlyErrors[$friendlyHash] = $friendlyMessage;
        if ($this->logger) {
            // do something here for external logging;
        }
    }

    /**
     * @since ZC v1.5.7
     */
    public function hasErrors()
    {
        return (count($this->logErrors + $this->friendlyErrors));
    }

    /**
     * @since ZC v1.5.7
     */
    public function getFriendlyErrors()
    {
        return $this->friendlyErrors;
    }

    /**
     * @since ZC v1.5.7
     */
    public function getLogErrors()
    {
        return $this->logErrors;
    }
}
