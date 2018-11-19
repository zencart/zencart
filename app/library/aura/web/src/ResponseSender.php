<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Web;

/**
 *
 * Logic to send a web response as an HTTP response.
 *
 * @package Aura.Web
 *
 */
class ResponseSender
{
    /**
     *
     * A web response object.
     *
     * @var Request
     *
     */
    protected $response;

    /**
     *
     * Constructor.
     *
     * @param Response $response A web response object.
     *
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     *
     * Sends the response.
     *
     * @return null
     *
     */
    public function __invoke()
    {
        $this->sendStatus();
        $this->sendHeaders();
        $this->sendCookies();
        $this->sendContent();
    }

    /**
     *
     * Sends the HTTP status.
     *
     * @return null
     *
     */
    protected function sendStatus()
    {
        header(
            $this->response->status->get(),
            true,
            $this->response->status->getCode()
        );
    }

    /**
     *
     * Sends the HTTP non-cookie headers.
     *
     * @return null
     *
     */
    protected function sendHeaders()
    {
        foreach ($this->response->headers->get() as $label => $value) {
            header("$label: $value", false);
        }
    }

    /**
     *
     * Sends the HTTP cookie headers.
     *
     * @return null
     *
     */
    protected function sendCookies()
    {
        foreach ($this->response->cookies->get() as $name => $cookie) {
            setcookie(
                $name,
                $cookie['value'],
                $cookie['expire'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }
    }

    /**
     *
     * Sends the HTTP body by echoing the response content; if the content is a
     * callable, it is invoked and echoed.
     *
     * @return null
     *
     */
    protected function sendContent()
    {
        $content = $this->response->content->get();
        if (is_callable($content)) {
            echo $content();
        } else {
            echo $content;
        }
    }
}
