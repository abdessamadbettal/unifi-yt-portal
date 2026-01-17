<?php

require 'header.php';
include 'config.php';

Logger::info('Connect page - Starting authorization process');

$mac = $_SESSION["id"];
$apmac = $_SESSION["ap"];
$url = $_SERVER['REDIRECT_URL'];

Logger::debug('Authorization parameters', [
    'mac' => $mac,
    'ap_mac' => $apmac,
    'redirect_url' => $url,
    'user_type' => $_SESSION["user_type"] ?? 'unknown'
]);

$controlleruser = $_SERVER['CONTROLLER_USER'];
$controllerpassword = $_SERVER['CONTROLLER_PASSWORD'];
$controllerurl = $_SERVER['CONTROLLER_URL'];
$controllerversion = $_SERVER['CONTROLLER_VERSION'];
$duration = $_SERVER['DURATION'];
$debug = false;
$site_id = $_SERVER['SITE_ID'];

Logger::info('UniFi controller configuration', [
    'url' => $controllerurl,
    'version' => $controllerversion,
    'site_id' => $site_id,
    'duration' => $duration . ' minutes'
]);

try {
    Logger::info('Connecting to UniFi controller');
    $unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $controllerurl, $site_id, $controllerversion);
    $set_debug_mode = $unifi_connection->set_debug($debug);
    
    Logger::info('Attempting UniFi login');
    $loginresults = $unifi_connection->login();
    
    if ($loginresults) {
        Logger::success('UniFi controller login successful');
    } else {
        Logger::error('UniFi controller login failed');
    }
    
    Logger::info('Authorizing guest on network', [
        'mac' => $mac,
        'duration' => $duration,
        'ap_mac' => $apmac
    ]);
    
    $auth_result = $unifi_connection->authorize_guest($mac, $duration, null, null, null, $apmac);
    
    if ($auth_result) {
        Logger::success('Guest authorized successfully', [
            'mac' => $mac,
            'result' => json_encode($auth_result)
        ]);
    } else {
        Logger::error('Guest authorization failed', [
            'mac' => $mac
        ]);
    }
} catch (Exception $e) {
    Logger::error('UniFi connection exception', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

if ($_SESSION["user_type"] == "new") {

  Logger::info('Processing new user registration');

  $fname = mysqli_real_escape_string($con, $_POST['fname'] ?? '');
  $lname = mysqli_real_escape_string($con, $_POST['lname'] ?? '');
  $email = mysqli_real_escape_string($con, $_POST['email'] ?? '');

  Logger::debug('New user details', [
      'firstname' => $fname,
      'lastname' => $lname,
      'email' => $email,
      'mac' => $mac
  ]);

  $create_table_result = mysqli_query($con, "
    CREATE TABLE IF NOT EXISTS `$table_name` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `firstname` varchar(45) NOT NULL,
    `lastname` varchar(45) NOT NULL,
    `email` varchar(45) NOT NULL,
    `mac` varchar(45) NOT NULL,
    `last_updated` varchar(45) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (mac)
    )");

  if (!$create_table_result) {
      Logger::error('Failed to create table', [
          'error' => mysqli_error($con),
          'table' => $table_name
      ]);
  } else {
      Logger::debug('Table exists or created successfully');
  }

  $insert_result = mysqli_query($con, "INSERT INTO `$table_name` (firstname, lastname, email, mac, last_updated) VALUES ('$fname', '$lname', '$email','$mac', NOW())");
  
  if (!$insert_result) {
      Logger::error('Failed to insert user data', [
          'error' => mysqli_error($con),
          'mac' => $mac
      ]);
  } else {
      Logger::success('New user registered successfully', [
          'firstname' => $fname,
          'lastname' => $lname,
          'email' => $email,
          'mac' => $mac
      ]);
  }
}

Logger::info('Closing database connection');
mysqli_close($con);

Logger::info('Redirecting to thank you page');

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title>
      <?php echo htmlspecialchars($business_name); ?> WiFi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <link rel="stylesheet" href="assets/styles/bulma.min.css"/>
    <link rel="stylesheet" href="vendor/fortawesome/font-awesome/css/all.css"/>
    <meta http-equiv="refresh" content="2;url=<?php echo htmlspecialchars($url); ?>" />
    <link rel="icon" type="image/png" href="assets/images/favicomatic/favicon-32x32.png" sizes="32x32"/>
    <link rel="icon" type="image/png" href="assets/images/favicomatic/favicon-16x16.png" sizes="16x16"/>
    <link rel="stylesheet" href="assets/styles/style.css"/>
</head>
<body>
<div class="page">

    <div class="head">
        <br>
        <figure id="logo">
            <img src="assets/images/logo.png">
        </figure>
    </div>

    <div class="main">
        <seection class="section">
            <div class="container">
                <div id="margin_zero" class="content has-text-centered is-size-6">Please wait, you are being</div>
                <div id="margin_zero" class="content has-text-centered is-size-6">authorized on the network</div>
            </div>
        </seection>
    </div>

</div>

</body>
</html>
