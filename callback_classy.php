<?php 

/* Callback resource for Confident CAPTCHA AJAX calls
 */

require_once("config.php");
require_once ("confidentcaptcha/ccap_api.php");
require_once ("confidentcaptcha/ccap_persist.php");

session_start();

$ccap_api = new CCAP_API(
    $ccap_api_settings['customer_id'],
    $ccap_api_settings['site_id'],
    $ccap_api_settings['api_username'],
    $ccap_api_settings['api_password'],
    $ccap_server_url);
$ccap_persist = new CCAP_Persist_Session();

if (isset($_SESSION['CONFIDENTCAPTCHA_POLICY_NAME'])) {
    $policy = $_SESSION['CONFIDENTCAPTCHA_POLICY_NAME'];
} else {
    $policy = $ccap_default_policy;
}

if ($policy == 'CCAP_ProductionFailOpen') {
    require_once("confidentcaptcha/ccap_prod_open_policy.php");
    $ccap_policy = new CCAP_ProductionFailOpen($ccap_api, $ccap_persist);
} elseif ($policy == 'CCAP_ProductionFailClosed') {
    require_once("confidentcaptcha/ccap_prod_closed_policy.php");
    $ccap_policy = new CCAP_ProductionFailClosed($ccap_api, $ccap_persist);
} elseif ($policy == 'CCAP_DevelopmentPolicy') {
    require_once("confidentcaptcha/ccap_dev_policy.php");
    $ccap_policy = new CCAP_DevelopmentPolicy($ccap_api, $ccap_persist);
}

/* Generate callback response */
function captcha_callback($ccap_policy)
{
    // Peform any setup needed at the start of a page w/ CAPTCHA
    $start_error = $ccap_policy->start_captcha_page();
    if ($start_error !== NULL) {
        return "Error starting callback page: $start_error";
    }
    
    $endpoint = $_REQUEST['endpoint'];
    return $ccap_policy->callback($endpoint, $_REQUEST);
}

if ($ccap_policy) {    
    $ret = captcha_callback($ccap_policy);
    $content = $ret[0];
    $headers = $ret[1];
    foreach ($headers as $h) header($h);
    echo $content;
} else {
    header("HTTP/1.0 500 Server Error");
    echo 'Policy is not set, and $ccap_default_policy is invalid';
}
