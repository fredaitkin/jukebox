<?php

/**
 * CLI bridge used by server.cjs: reads a JSON request from stdin, runs it
 * through the Laravel kernel and writes a JSON response envelope to stdout.
 */

use Illuminate\Http\Request;

ob_start();

require __DIR__ . '/vendor/autoload.php';

$input = json_decode(stream_get_contents(STDIN), true);
$body = base64_decode($input['body']);
$headers = array_change_key_case($input['headers'], CASE_LOWER);

$server = [];
foreach ($headers as $name => $value) {
    $key = strtoupper(str_replace('-', '_', $name));
    $server[in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true) ? $key : 'HTTP_' . $key] = $value;
}

// config/view.php detects the device from $_SERVER while the app boots, before the request object exists
$_SERVER = array_merge($_SERVER, $server);

$cookies = [];
foreach (explode(';', $headers['cookie'] ?? '') as $pair) {
    if (strpos($pair, '=') !== false) {
        [$name, $value] = explode('=', trim($pair), 2);
        $cookies[$name] = urldecode($value);
    }
}

$parameters = [];
if (stripos($headers['content-type'] ?? '', 'application/x-www-form-urlencoded') === 0) {
    parse_str($body, $parameters);
}

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Request::create(
    'http://' . ($headers['host'] ?? 'localhost') . $input['url'],
    $input['method'],
    $parameters,
    $cookies,
    [],
    $server,
    $body
);

$response = $kernel->handle($request);

ob_start();
$response->sendContent();
$content = ob_get_clean();

$responseHeaders = $response->headers->allPreserveCaseWithoutCookies();
$setCookies = array_map('strval', $response->headers->getCookies());

$kernel->terminate($request, $response);

ob_end_clean();

echo json_encode([
    'status' => $response->getStatusCode(),
    'headers' => $responseHeaders,
    'cookies' => $setCookies,
    'body' => base64_encode($content),
]);
