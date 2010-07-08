<?php 

require_once("config.php");
require_once ("confidentcaptcha/ccap_api.php");

$ccap_api = new CCAP_API($api_settings['customer_id'],
    $api_settings['site_id'], $api_settings['api_username'],
    $api_settings['api_password'], $api_settings['captcha_server_url']);

/* Pick one of the following, or develop your own */

/* Good policy for initial development
 * Puts status information on the page, makes errors explicit
 */
require_once("confidentcaptcha/ccap_dev_policy.php");
$ccap_policy = new CCAP_DevelopmentPolicy($ccap_api);

/* Safe policy for production, on contact form
 * If CAPTCHA creation fails, then the form still works
 */
// require_once("confidentcaptcha/ccap_prod_open_policy.php");
// $ccap_policy = new CCAP_ProductionFailOpen($ccap_api);

/* Safe policy for production, on account registration form 
 * If CAPTCHA creation fails, then the form will not work
 */
// require_once("confidentcaptcha/ccap_prod_closed_policy.php");
// $ccap_policy = new CCAP_ProductionFailClosed($ccap_api);

/* Generate callback response */
function captcha_callback($ccap_policy)
{
    // Peform any setup needed at the start of a page w/ CAPTCHA
    $start_error = $ccap_policy->start_captcha_page();
    if ($start_error !== NULL) {
        return "Error starting callback page: $start_error";
    }

    if ($_REQUEST['endpoint'] == 'block_onekey_start') {
        $xml = $ccap_policy->start_audio($_REQUEST['block_id'],
            $_REQUEST['phone_number']);
        $_SESSION['confidentcaptcha_onekey_id'] = $resp;
        $xml = "<?xml version=\"1.0\"?>\n<response><status>".$resp['status']."</status><onekey_id>".$resp['body']."</onekey_id></response>";
        header("Content-type: text/xml"); 
        return $xml;
    }

elseif ($_REQUEST['endpoint'] == 'block_onekey_verify') {
    $resp = check_block_onekey($_REQUEST['block_id'], $_REQUEST['captcha_id'], $api_settings);
    $xml = simplexml_load_string($resp['body']);
    if ($xml->authenticated == "True") {
        $_SESSION['confidentcaptcha_onekey_verified'] = true;
    }
    header("Content-type: text/xml");
    echo $resp['body'];
}

else if ($_REQUEST['endpoint'] == 'create_block') {
    $block = create_block($api_settings, $_REQUEST['ip_addr'], $_REQUEST['user_agent']);
    echo $block['body'];
}

else if ($_REQUEST['endpoint'] == 'create_captcha_instance') {
    $display_style=get_val('display_style');
    $include_audio=get_val('include_audio');
    $height=get_val('height');
    $width=get_val('width');
    $captcha_length=get_val('captcha_length');
    $resp = create_instance($_REQUEST['block_id'], $api_settings, $display_style,
        $include_audio, $height, $width, $captcha_length);
    if ($resp['status'] == 410) {
        header($_SERVER["SERVER_PROTOCOL"]." 410 Gone");
        exit;
    }
    echo $resp['body'];
}

else if ($_REQUEST['endpoint'] == 'verify_block_captcha') {
    $resp = check_instance($_REQUEST['block_id'], $_REQUEST['captcha_id'],
        $_REQUEST['code'], $api_settings);
    if ($resp['status'] == 200) {
        if ($resp['body'] == 'True') {
            $_SESSION['confidentcaptcha_visual_verified'] = true;
            echo 'true'; exit;
        }
    }
    echo 'false'; exit;
}
