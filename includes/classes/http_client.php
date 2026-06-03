<?php

/**
 * httpClient Class.
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2001 Leo West <west_leo@yahoo-REMOVE-.com> Net_HTTP_Client v0.6
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * httpClient Class.
 * This class is used mainly by payment modules to simulate a browser session
 * when communicating back to another server to collect information
 *
 * @since ZC v1.0.3
 */
class httpClient extends base
{
    public array $url = []; // array containing server URL, similar to parseurl() returned array
    public int|string $reply = 0; // response code
    public string $replyString = ''; // full response
    public string $protocolVersion = '1.1';
    public string $requestBody;
    public array $requestHeaders;
    public string $request;
    public string $responseBody;
    public array $responseHeaders;
    /** @var resource|bool */
    public $socket = false;
// proxy stuff
    public bool $useProxy = false;
    public string $proxyHost;
    public string|int $proxyPort;
    public int $timeout = 8; // 8-second default timeout

    /**
     * Note: when host and port are defined, the connection is immediate
     **/
    public function __construct(string $host = '', string $port = '')
    {
        $this->requestHeaders = [];
        $this->requestBody = '';
        $this->responseHeaders = [];
        $this->responseBody = '';

        if (!empty($host)) {
            $this->connect($host, $port);
        }
    }

    /**
     * turn on proxy support
     *
     * @param string $proxyHost proxy host address eg "proxy.mycorp.com"
     * @param int|string $proxyPort proxy port usually 80 or 8080
     * @since ZC v1.0.3
     **/
    public function setProxy(string $proxyHost, int|string $proxyPort): void
    {
        $this->useProxy = true;
        $this->proxyHost = $proxyHost;
        $this->proxyPort = $proxyPort;
    }

    /**
     * setProtocolVersion
     * define the HTTP protocol version to use
     *
     * @param string $version the version number with one decimal: "0.9", "1.0", "1.1"
     * when using 1.1, you MUST set the mandatory headers "Host"
     * @return boolean false if the version number is bad, true if ok
     * @since ZC v1.0.3
     **/
    public function setProtocolVersion(string $version): bool
    {
        if (($version > 0) && ($version <= 1.1)) {
            $this->protocolVersion = $version;
            return true;
        }

        return false;
    }

    /**
     * set a username and password to access a protected resource
     * Only "Basic" authentication scheme is supported yet
     *
     * @param string $username identifier
     * @param string $password clear password
     * @since ZC v1.0.3
     **/
    public function setCredentials(string $username, string $password): void
    {
        $this->addHeader('Authorization', 'Basic ' . base64_encode($username . ':' . $password));
    }

    /**
     * define a set of HTTP headers to be sent to the server
     * header names are lowercased to avoid duplicated headers
     *
     * @param array $headers array containing the headers as headerName => headerValue pairs
     * @since ZC v1.0.3
     **/
    public function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->requestHeaders[$name] = $value;
        }
    }

    /**
     * addHeader
     * set a unique request header
     *
     * @param string $headerName the header name
     * @param string $headerValue the header value, ( unencoded)
     * @since ZC v1.0.3
     **/
    public function addHeader(string $headerName, string $headerValue): void
    {
        $this->requestHeaders[$headerName] = $headerValue;
    }

    /**
     * unset a request header
     *
     * @since ZC v1.0.3
     **/
    public function removeHeader(string $headerName): void
    {
        unset($this->requestHeaders[$headerName]);
    }

    /**
     * open a connection to the server
     *
     * @param string $host server address (or IP)
     * @param string|int $port server listening port - defaults to 80
     * @return boolean false is connection failed, true otherwise
     * @since ZC v1.0.3
     **/
    public function Connect(string $host, int|string $port = ''): bool
    {
        $this->url['scheme'] = 'http';
        $this->url['host'] = $host;
        if (!empty($port)) {
            $this->url['port'] = $port;
        }

        return true;
    }

    /**
     * Disconnect
     * close the connection to the server
     *
     * @since ZC v1.0.3
     **/
    public function Disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }

    /**
     * issue a HEAD request
     *
     * @param string $uri URI of the document
     * @return string response status code (200 if ok)
     * @since ZC v1.0.3
     **/
    public function Head(string $uri): string
    {
        $this->responseHeaders = [];
        $this->responseBody = '';

        $uri = $this->makeUri($uri);

        if ($this->sendCommand('HEAD ' . $uri . ' HTTP/' . $this->protocolVersion)) {
            $this->processReply();
        }

        return $this->reply;
    }

    /**
     * issue a GET http request
     *
     * @param string $url URI (path on server) or full URL of the document
     * @return string response status code (200 if ok)
     * @since ZC v1.0.3
     **/
    public function Get(string $url): string
    {
        $this->responseHeaders = [];
        $this->responseBody = '';

        $uri = $this->makeUri($url);

        if ($this->sendCommand('GET ' . $uri . ' HTTP/' . $this->protocolVersion)) {
            $this->processReply();
        }

        return $this->reply;
    }

    /**
     * issue a POST http request
     *
     * @param string $uri URI of the document
     * @param array $query_params parameters to send in the form "parameter name" => value
     * @return string response status code (200 if ok)
     * @since ZC v1.0.3
     **/
// * $params = array( "login" => "tiger", "password" => "secret" );
// * $http->post( "/login.php", $params );
    public function Post(string $uri, array $query_params = []): string
    {
        $uri = $this->makeUri($uri);

        if (!empty($query_params)) {
            $postArray = [];
            foreach ($query_params as $k => $v) {
                $postArray[] = urlencode($k) . '=' . urlencode($v);
            }

            $this->requestBody = implode('&', $postArray);
        }

        // set the content type for post parameters
        $this->addHeader('Content-Type', 'application/x-www-form-urlencoded');

        if ($this->sendCommand('POST ' . $uri . ' HTTP/' . $this->protocolVersion)) {
            $this->processReply();
        }

        $this->removeHeader('Content-Type');
        $this->removeHeader('Content-Length');
        $this->requestBody = '';

        return $this->reply;
    }

    /**
     * Send a PUT request
     * PUT is the method to sending a file on the server. it is *not* widely supported
     *
     * @param string $uri the location of the file on the server. dont forget the heading "/"
     * @param string $filecontent the content of the file. binary content accepted
     * @return string response status code 201 (Created) if ok
     * @see RFC2518 "HTTP Extensions for Distributed Authoring WEBDAV"
     * @since ZC v1.0.3
     **/
    public function Put(string $uri, string $filecontent): string
    {
        $uri = $this->makeUri($uri);
        $this->requestBody = $filecontent;

        if ($this->sendCommand('PUT ' . $uri . ' HTTP/' . $this->protocolVersion)) {
            $this->processReply();
        }

        return $this->reply;
    }

    /**
     * return the response headers
     * to be called after a Get() or Head() call
     *
     * @return array headers received from server in the form headername => value
     * @since ZC v1.0.3
     **/
    public function getHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * return the specified response header
     *
     * @param string $headername the name of the header
     * @return ?string header value or NULL if no such header is defined
     * @since ZC v1.0.3
     **/
    public function getHeader(string $headername): ?string
    {
        if (!isset($this->responseHeaders[$headername])) {
            return null;
        }

        return $this->responseHeaders[$headername];
    }

    /**
     * return the response body
     * invoke it after a Get() call for instance, to retrieve the response
     *
     * @return string body content
     * @since ZC v1.0.3
     **/
    public function getBody(): string
    {
        return $this->responseBody;
    }

    /**
     * getStatus return the server response's status code
     *
     * @return string a status code
     * code are divided in classes (where x is a digit)
     *  - 20x : request processed OK
     *  - 30x : document moved
     *  - 40x : client error ( bad url, document not found, etc...)
     *  - 50x : server error
     * @see RFC2616 "Hypertext Transfer Protocol -- HTTP/1.1"
     * @since ZC v1.0.3
     **/
    public function getStatus(): string
    {
        return $this->reply;
    }

    /**
     * getStatusMessage return the full response status, of the form "CODE Message"
     * eg. "404 Document not found"
     *
     * @return string the message
     * @since ZC v1.0.3
     **/
    public function getStatusMessage(): string
    {
        return $this->replyString;
    }


    /**
     * send a request
     * data sent are in order
     * a) the command
     * b) the request headers if they are defined
     * c) the request body if defined
     *
     * @return bool false on error
     * @since ZC v1.0.3
     **/
    public function sendCommand(string $command): bool
    {
        $this->responseHeaders = [];
        $this->responseBody = '';

        // connect if necessary
        if ($this->socket === false || feof($this->socket)) {
            if ($this->useProxy) {
                $host = $this->proxyHost;
                $port = $this->proxyPort;
            } else {
                $host = $this->url['host'] ?? '';
                $port = $this->url['port'] ?? '';
            }

            if (empty($host)) {
                return false;
            }

            if (empty($port)) {
                $port = 80;
            }

            if (!$this->socket = @fsockopen($host, $port, $this->reply, $this->replyString, $this->timeout)) {
                return false;
            }

            if (!empty($this->requestBody)) {
                $this->addHeader('Content-Length', strlen($this->requestBody));
            }

            $this->request = $command;
            $cmd = $command . "\r\n";
            if (!empty($this->requestHeaders)) {
                foreach ($this->requestHeaders as $k => $v) {
                    $cmd .= $k . ': ' . $v . "\r\n";
                }
            }

            if (!empty($this->requestBody)) {
                $cmd .= "\r\n" . $this->requestBody;
            }

            // unset body (in case of successive requests)
            $this->requestBody = '';

            fwrite($this->socket, $cmd . "\r\n");

            return true;
        }

        return false;
    }

    /**
     * Parse the reply.
     * @since ZC v1.0.3
     */
    public function processReply(): string
    {
        $line = fgets($this->socket, 1024);
        if ($line === false) {
            $this->replyString = '';
            $this->reply = 'Bad Response';
            return $this->reply;
        }

        $this->replyString = trim($line);

        if (preg_match('|^HTTP/\S+ (\d+) |i', $this->replyString, $a)) {
            $this->reply = $a[1];
        } else {
            $this->reply = 'Bad Response';
        }

        //get response headers and body
        $this->responseHeaders = $this->processHeader();
        $this->responseBody = $this->processBody();

        return $this->reply;
    }

    /**
     * processHeader() reads header lines from socket until the line equals $lastLine
     *
     * @return array of headers with header names as keys and header content as values
     * @since ZC v1.0.3
     **/
    public function processHeader($lastLine = "\r\n"): array
    {
        $headers = [];
        $finished = false;

        while ((!$finished) && (!feof($this->socket))) {
            $str = fgets($this->socket, 1024);
            if ($str === false) {
                break;
            }

            $finished = ($str == $lastLine);
            if (!$finished) {
                $parts = explode(': ', $str, 2);
                if (count($parts) < 2) {
                    continue;
                }
                [$hdr, $value] = $parts;
// nasty workaround broken multiple same headers (eg. Set-Cookie headers) @FIXME
                if (isset($headers[$hdr])) {
                    $headers[$hdr] .= '; ' . trim($value);
                } else {
                    $headers[$hdr] = trim($value);
                }
            }
        }

        return $headers;
    }

    /**
     * processBody() reads the body from the socket
     * the body is the "real" content of the reply
     *
     * @return string body content
     * @since ZC v1.0.3
     **/
    public function processBody(): string
    {
        $data = '';
        $counter = 0;

        do {
            $status = stream_get_meta_data($this->socket);
            if (!empty($status['eof'])) {
                break;
            }

            if ($status['unread_bytes'] > 0) {
                $buffer = fread($this->socket, $status['unread_bytes']);
                $counter = 0;
            } else {
                $buffer = fread($this->socket, 128);
                $counter++;
                usleep(2);
            }

            if ($buffer === false) {
                $buffer = '';
            }

            $data .= $buffer;
        } while (($status['unread_bytes'] > 0) || ($counter++ < 10));

        return $data;
    }

    /**
     * Calculate and return the URI to be sent ( proxy purpose )
     *
     * @param string $uri the local URI
     * @return string URI to be used in the HTTP request
     * @since ZC v1.0.3
     **/
    public function makeUri(string $uri): string
    {
        $a = parse_url($uri);

        if ($a === false) {
            return '';
        }

        if (isset($a['scheme'], $a['host'])) {
            $this->url = $a;
        } else {
            if (!is_array($this->url)) {
                $this->url = [];
            }
            unset($this->url['query'], $this->url['fragment']);
            $this->url = array_merge($this->url, $a);
        }

        if (empty($this->url['path'])) {
            $this->url['path'] = '/';
        }

        if ($this->useProxy) {
            if (empty($this->url['host'])) {
                return '';
            }
            $requesturi = 'http://' . $this->url['host'] . (empty($this->url['port']) ? '' : ':' . $this->url['port']) . $this->url['path'] . (empty($this->url['query']) ? '' : '?' . $this->url['query']);
        } else {
            $requesturi = $this->url['path'] . (empty($this->url['query']) ? '' : '?' . $this->url['query']);
        }

        return $requesturi;
    }
}
