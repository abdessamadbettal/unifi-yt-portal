<?php

Logger::info('Loading database configuration');

$host_ip = $_SERVER['HOST_IP'];
$db_user = $_SERVER['DB_USER'];
$db_pass = $_SERVER['DB_PASS'];
$db_name = $_SERVER['DB_NAME'];
$table_name = $_SERVER['TABLE_NAME'];

Logger::debug('Database connection parameters', [
    'host' => $host_ip,
    'user' => $db_user,
    'database' => $db_name,
    'table' => $table_name
]);

$con = mysqli_connect($host_ip, $db_user, $db_pass, $db_name);

if (mysqli_connect_errno()) {
  $error = mysqli_connect_error();
  Logger::error('Database connection failed', [
      'error' => $error,
      'errno' => mysqli_connect_errno()
  ]);
  echo "Failed to connect to SQL: " . $error;
} else {
  Logger::success('Database connected successfully');
}

mysqli_report(MYSQLI_REPORT_OFF);