<?php 

require_once("config.php");
require_once ("confidentcaptcha/ccap_api.php");
require_once ("confidentcaptcha/ccap_persist.php");

$ccap_api = new CCAP_API($api_settings['customer_id'],
    $api_settings['site_id'], $api_settings['api_username'],
    $api_settings['api_password'], $api_settings['captcha_server_url']);
$ccap_persist = new CCAP_Persist_Session();

/* Pick one of the following, or develop your own */

/* Good policy for initial development
 * Puts status information on the page, makes errors explicit
 */
require_once("confidentcaptcha/ccap_dev_policy.php");
$ccap_policy = new CCAP_DevelopmentPolicy($ccap_api, $ccap_persist);

/* Safe policy for production, on contact form
 * If CAPTCHA creation fails, then the form still works
 */
// require_once("confidentcaptcha/ccap_prod_open_policy.php");
// $ccap_policy = new CCAP_ProductionFailOpen($ccap_api, $ccap_persist);

/* Safe policy for production, on account registration form 
 * If CAPTCHA creation fails, then the form will not work
 */
// require_once("confidentcaptcha/ccap_prod_closed_policy.php");
// $ccap_policy = new CCAP_ProductionFailClosed($ccap_api, $ccap_persist);

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

$ret = captcha_callback($ccap_policy);
$content = $ret[0];
$headers = $ret[1];
foreach ($headers as $h) header($h);
echo $content;
