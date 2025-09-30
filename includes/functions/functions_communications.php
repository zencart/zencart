<?php

use http\Exception\BadQueryStringException;

/*
 * Communication-related functions, such as for making CURL requests
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v2.2.0 $
 */

/**
 * Make connection to $url and get a response back.
 * For GET requests only the $url is needed.
 * For POST requests, $url, $method, and $payload are needed.
 *
 * @param string $url URL to be connected to.
 * @param string $method GET or POST
 * @param string|array|null $payload POST data
 * @param bool $encodePayloadArraysAsJson submit POST as JSON
 * @param bool $decodeJsonResponses decode response to JSON
 * @param array|null $extraCurlOptions curl setup overrides
 * @param bool $returnWithMetadata Default is to receive direct response; or set this to true to get an array containing response and headers for advanced parsing
 * @return string|array|false False on failure; string for normal response; array if advanced metaData requested
 * @since ZC v2.2.0
 */
  function zenDoCurlRequest(string $url, string $method = 'GET', string|array|null $payload = null, bool $encodePayloadArraysAsJson = false, bool $decodeJsonResponses = false, ?array $extraCurlOptions = [], bool $returnWithMetadata = false): string|array|false
  {
      $base_UA_host = defined('HTTP_CATALOG_SERVER') ? HTTP_CATALOG_SERVER : HTTP_SERVER;
      $referrer = $base_UA_host . DIR_WS_CATALOG;
      $userAgent = empty($_SERVER['HTTP_USER_AGENT']) ? $base_UA_host . DIR_WS_CATALOG : $_SERVER['HTTP_USER_AGENT'];

      $curlDefaultOptions = [
          CURLOPT_CONNECTTIMEOUT => 10,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_FORBID_REUSE => true,
          CURLOPT_FRESH_CONNECT => true,
          CURLOPT_HEADER => false,
          CURLOPT_REFERER => $referrer,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_TIMEOUT => 45,
          CURLOPT_USERAGENT => $userAgent,
          CURLOPT_VERBOSE => false,
      ];

      if (is_array($payload)) {
          if ($encodePayloadArraysAsJson) {
              $payload = json_encode($payload);
          } else {
              $payload = http_build_query($payload);
          }
          if ($payload === false) {
              throw new BadQueryStringException('Could not encode the provided array $payload.');
          }
      }

      $ch = curl_init();
      if (empty($ch)) {
          if (IS_ADMIN_FLAG === true) {
              global $messageStack;
              if (is_object($messageStack)) {
                  $messageStack->add_session('Communications curl_init() failed. Contact server administrator.', 'error');
              }
          }
          trigger_error("CURL instantiation error. Could not do curl_init().", E_USER_WARNING);
          return false;
      }

      curl_setopt_array($ch, array_replace($curlDefaultOptions, $extraCurlOptions ?? []));
      curl_setopt($ch, CURLOPT_URL, $url);

      if (!empty($payload) && strtoupper($method) === 'POST') {
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
      }

      $proxy = false;
      if (CURL_PROXY_REQUIRED === 'True') {
          $proxy = true;
          $proxy_tunnel_flag = !((defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) === 'FALSE'));
          curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
          curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
          curl_setopt($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
      }

      $response = curl_exec($ch);
      $error = curl_error($ch);
      $info = curl_getinfo($ch);
      $httpCode = curl_getinfo($ch, $proxy ? CURLINFO_HTTP_CONNECTCODE : CURLINFO_RESPONSE_CODE);

      curl_close($ch);

      if (!empty($error)) {
          // only give messageStack responses on admin-side
          if (IS_ADMIN_FLAG === true) {
              global $messageStack;
              if (is_object($messageStack)) {
                  $messageStack->add_session("CURL communication ERROR: $error", 'error');
              }
          }
          // log the error, and return false
          trigger_error("CURL communication error: $error\n\n" . print_r($info, true), E_USER_WARNING);
          return false;
      }

      // json decode if requested and if possible
      if ($response !== false && $decodeJsonResponses && str_contains($info['content_type'], 'application/json')) {
          $rawResponse = $response;
          $jsonResponse = json_decode($response);
          return $returnWithMetadata ? compact($jsonResponse, $httpCode, $info, $error, $rawResponse) : $jsonResponse;
      }

      return $returnWithMetadata ? compact($response, $httpCode, $info, $error) : $response;
  }
