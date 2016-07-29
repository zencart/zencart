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
 * A factory to create Request and Response objects.
 *
 * @package Aura.Web
 *
 */
class WebFactory
{
    /**
     *
     * A copy of $GLOBALS.
     *
     * @var array
     *
     */
    protected $globals = array();

    /**
     *
     * Additional mobile agent values for the request.
     *
     * @var array
     *
     */
    protected $mobile_agents = array();

    /**
     *
     * Additional crawler agent values for the request.
     *
     * @var array
     *
     */
    protected $crawler_agents = array();

    /**
     *
     * Additional content decoder callables for the request.
     *
     * @var array
     *
     */
    protected $decoders = array();

    /**
     *
     * Additional file .extension mappings to media types for the request.
     *
     * @var array
     *
     */
    protected $types = array();

    /**
     *
     * The HTTP method override field name for the request.
     *
     * @var string
     *
     */
    protected $method_field;

    /**
     *
     * The list of trusted proxies.
     *
     * @var array
     *
     */
    protected $proxies = array();

    /**
     *
     * Constructor.
     *
     * @param array $globals A copy of $GLOBALS.
     *
     */
    public function __construct(array $globals)
    {
        $this->globals = $globals;
    }

    /**
     *
     * Sets the mobile agents.
     *
     * @param array $mobile_agents The mobile agent strings.
     *
     * @return null
     *
     */
    public function setMobileAgents(array $mobile_agents)
    {
        $this->mobile_agents = $mobile_agents;
    }

    /**
     *
     * Sets the crawler agents.
     *
     * @param array $crawler_agents The crawler agent strings.
     *
     * @return null
     *
     */
    public function setCrawlerAgents(array $crawler_agents)
    {
        $this->crawler_agents = $crawler_agents;
    }

    /**
     *
     * Sets the content type decoders.
     *
     * @param array $decoders The content-type to decoder callables.
     *
     * @return null
     *
     */
    public function setDecoders(array $decoders)
    {
        $this->decoders = $decoders;
    }

    /**
     *
     * Sets the file .extension mappings to media types.
     *
     * @param array $types The file .extension to media type mappings.
     *
     * @return null
     *
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     *
     * Sets the HTTP method override field.
     *
     * @param string $method_field The field name.
     *
     */
    public function setMethodField($method_field)
    {
        $this->method_field = $method_field;
    }

    /**
     *
     * Sets the list of trusted proxies.
     *
     * @param array $proxies The list of trusted proxies.
     *
     */
    public function setProxies($proxies)
    {
        $this->proxies = $proxies;
    }

    /**
     *
     * Returns a new Request object.
     *
     * @return Request
     *
     */
    public function newRequest()
    {
        return new Request(
            $this->newRequestClient(),
            $this->newRequestContent(),
            $this->newRequestGlobals(),
            $this->newRequestHeaders(),
            $this->newRequestMethod(),
            $this->newRequestParams(),
            $this->newRequestUrl()
        );
    }

    /**
     *
     * Returns a request client object.
     *
     * @return Request\Client
     *
     */
    public function newRequestClient()
    {
        return new Request\Client(
            $this->get('_SERVER'),
            $this->mobile_agents,
            $this->crawler_agents,
            $this->proxies
        );
    }

    /**
     *
     * Returns a request content object.
     *
     * @return Request\Content
     *
     */
    public function newRequestContent()
    {
        return new Request\Content(
            $this->get('_SERVER'),
            $this->decoders
        );
    }

    /**
     *
     * Returns a request cookies object.
     *
     * @return Request\Values
     *
     */
    public function newRequestCookies()
    {
        return new Request\Values($this->get('_COOKIE'));
    }

    /**
     *
     * Returns a request environment object
     *
     * @return Request\Values
     *
     */
    public function newRequestEnv()
    {
        return new Request\Values($this->get('_ENV'));
    }

    /**
     *
     * Returns a request files object.
     *
     * @return Request\Files
     *
     */
    public function newRequestFiles()
    {
        return new Request\Files($this->get('_FILES'));
    }

    /**
     *
     * Returns a request globals object containing cookies, environment,
     * files, post, query, and server objects.
     *
     * @return Request\Globals
     *
     */
    public function newRequestGlobals()
    {
        return new Request\Globals(
            $this->newRequestCookies(),
            $this->newRequestEnv(),
            $this->newRequestFiles(),
            $this->newRequestPost(),
            $this->newRequestQuery(),
            $this->newRequestServer()
        );
    }

    /**
     *
     * Returns a request headers object.
     *
     * @return Request\Headers
     *
     */
    public function newRequestHeaders()
    {
        return new Request\Headers($this->get('_SERVER'));
    }

    /**
     *
     * Returns a request method object.
     *
     * @return Request\Method
     *
     */
    public function newRequestMethod()
    {
        return new Request\Method(
            $this->get('_SERVER'),
            $this->get('_POST'),
            $this->method_field
        );
    }

    /**
     *
     * Returns a request params object.
     *
     * @param array $data A parameters array.
     *
     * @return Request\Params
     *
     */
    public function newRequestParams(array $data = array())
    {
        return new Request\Params($data);
    }

    /**
     *
     * Returns a request post-values object.
     *
     * @return Request\Values
     *
     */
    public function newRequestPost()
    {
        return new Request\Values($this->get('_POST'));
    }

    /**
     *
     * Returns a request query-values object.
     *
     * @return Request\Values
     *
     */
    public function newRequestQuery()
    {
        return new Request\Values($this->get('_GET'));
    }

    /**
     *
     * Returns a request server-values object.
     *
     * @return Request\Values
     *
     */
    public function newRequestServer()
    {
        return new Request\Values($this->get('_SERVER'));
    }

    /**
     *
     * Returns a request URL object.
     *
     * @return Request\Url
     *
     */
    public function newRequestUrl()
    {
        return new Request\Url($this->get('_SERVER'));
    }

    /**
     *
     * Returns a new Response object.
     *
     * @return Response
     *
     */
    public function newResponse()
    {
        $status   = $this->newResponseStatus();
        $headers  = $this->newResponseHeaders();
        $cookies  = $this->newResponseCookies();
        $content  = $this->newResponseContent($headers);
        $cache    = $this->newResponseCache($headers);
        $redirect = $this->newResponseRedirect($status, $headers, $cache);
        return new Response(
            $status,
            $headers,
            $cookies,
            $content,
            $cache,
            $redirect
        );
    }

    /**
     *
     * Returns a response cache object.
     *
     * @param Response\Headers $headers A headers object.
     *
     * @return Response\Cache
     *
     */
    public function newResponseCache(Response\Headers $headers)
    {
        return new Response\Cache($headers);
    }

    /**
     *
     * Returns a response content object.
     *
     * @param Response\Headers $headers A headers object.
     *
     * @return Response\Content
     *
     */
    public function newResponseContent(Response\Headers $headers)
    {
        return new Response\Content($headers);
    }

    /**
     *
     * Returns a response cookies object.
     *
     * @return Response\Cookies
     *
     */
    public function newResponseCookies()
    {
        return new Response\Cookies;
    }

    /**
     *
     * Returns a response headers object.
     *
     * @return Response\Headers
     *
     */
    public function newResponseHeaders()
    {
        return new Response\Headers;
    }

    /**
     *
     * Returns a response status object.
     *
     * @return Response\Status
     *
     */
    public function newResponseStatus()
    {
        return new Response\Status;
    }

    /**
     *
     * Returns a response redirect object.
     *
     * @param Response\Status $status A status object.
     *
     * @param Response\Headers $headers A headers object.
     *
     * @param Response\Cache $cache A cache object.
     *
     * @return Response\Redirect
     *
     */
    public function newResponseRedirect(
        Response\Status $status,
        Response\Headers $headers,
        Response\Cache $cache
    ) {
        return new Response\Redirect($status, $headers, $cache);
    }

    /**
     *
     * Returns a $globals array value.
     *
     * @param string $key The $globals array key.
     *
     * @return array The $globals array value.
     *
     */
    protected function get($key)
    {
        return isset($this->globals[$key])
             ? $this->globals[$key]
             : array();
    }
}
