<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 16 Modified in v2.1.0 $
 */

namespace Zencart\PluginSupport;

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

    public function hasLogErrors()
    {
        return (count($this->logErrors));
    }

    public function hasFriendlyErrors()
    {
        return (count($this->friendlyErrors));
    }

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

    public function hasErrors()
    {
        return (count($this->logErrors + $this->friendlyErrors));
    }

    public function getFriendlyErrors()
    {
        return $this->friendlyErrors;
    }

    public function getLogErrors()
    {
        return $this->logErrors;
    }
}
