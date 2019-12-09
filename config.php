<?php

$GLOBALS['config'] = [
    'db' => [
        'host' => 'localhost',
        'name' => 'books',
        'user' => 'root',
        'pass' => '',
    ]
];

/**
 * @param string $name
 * @return mixed
 */
function config(string $name)
{
    $config = $GLOBALS['config'];
    $name_array = explode('.', $name);
    $count = count($name_array);
    for ($i = 0; $i < $count; $i++) {
        if (array_key_exists($name_array[$i], $config)) {
            $config = $config[$name_array[$i]];
        }
    }
    return $config;
}

/**
 * @param mixed $var
 * @return void
 */
function dd($var): void
{
    print_r($var);
    exit;
}

/**
 * @param string $message
 * @param int $status
 * @return void
 */
function error_response($message, $status = 500): void
{
    header("HTTP/1.1 " . $status . " " . request_status($status));
    echo json_encode(['message' => $message]);
}

/**
 * @param array $messages
 * @param int $status
 * @return void
 */
function validator_response($messages, $status = 422): void
{
    header("HTTP/1.1 " . $status . " " . request_status($status));
    echo json_encode($messages);
}

/**
 * @param mixed $data
 * @return void
 */
function success_response($data): void
{
    header("HTTP/1.1 200 OK");
    echo json_encode($data);
}

function request_status($code)
{
    $status = array(
        200 => 'OK',
        404 => 'Not Found',
        422 => 'Unprocessable Entity',
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error',
    );
    return ($status[$code]) ? $status[$code] : $status[500];
}
