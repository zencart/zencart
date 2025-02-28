<?php

use http\Exception\BadQueryStringException;

/**
 *
 * Communication-related functions, such as for making CURL requests
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v2.2.0 $
 *
 */

  function zenDoCurlRequest(string $url, string $method = 'GET', string|array|null $payload = null, $encodePayloadArraysAsJson = false, $decodeJsonResponses = false): string|false
  {
      $base_UA_host = defined('HTTP_CATALOG_SERVER') ? HTTP_CATALOG_SERVER : HTTP_SERVER;
      $referrer = $base_UA_host . DIR_WS_CATALOG;
      $userAgent = empty($_SERVER['HTTP_USER_AGENT']) ? $base_UA_host . DIR_WS_CATALOG : $_SERVER['HTTP_USER_AGENT'];

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
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_VERBOSE, 0);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
      curl_setopt($ch, CURLOPT_REFERER, $referrer);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      if (!empty($payload) && strtoupper($method) === 'POST') {
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
      }

      if (CURL_PROXY_REQUIRED === 'True') {
          $proxy_tunnel_flag = !((defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) === 'FALSE'));
          curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
          curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
          curl_setopt($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
      }

      $response = curl_exec($ch);
      $error = curl_error($ch);
      $info = curl_getinfo($ch);

      curl_close($ch);

      if (!empty($error)) {
          // only give messageStack responses on admin-side
          if (IS_ADMIN_FLAG === true) {
              global $messageStack;
              if (is_object($messageStack)) {
                  $messageStack->add_session('CURL communication ERROR: ' . $error, 'error');
              }
          }
          // log the error, and return false
          trigger_error("CURL communication error: $error\n\n$info", E_USER_WARNING);
          return false;
      }

      if ($response !== '') {
          // json decode if requested and if possible
          if ($decodeJsonResponses && str_contains($info['content_type'], 'application/json')) {
              $decoded = json_decode($response);
              return $decoded ?? $response;
          }
      }
      return $response;
  }

