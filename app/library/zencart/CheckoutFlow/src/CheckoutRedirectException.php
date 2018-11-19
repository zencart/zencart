<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace Zencart\CheckoutFlow;

/**
 * Class CheckoutRedirectException
 * @package Zencart\CheckoutFlow
 */
class CheckoutRedirectException extends \Exception
{
    /**
     * @var string
     */
    protected $redirectDestination;

    /**
     * @param array $redirectDestination
     * @param string $message
     * @param int $code
     */
    public function __construct(array $redirectDestination, $message = '', $code = 0)
    {
        session_write_close();
        $this->redirectDestination = $redirectDestination;
        parent::__construct($message, $code);
    }

    /**
     * @return string
     */
    public function getRedirectDestination()
    {
        return $this->redirectDestination;
    }

    /**
     * @param $redirectDestination
     */
    public function setFailError($redirectDestination)
    {
        $this->redirectDestination = $redirectDestination;
    }
}

