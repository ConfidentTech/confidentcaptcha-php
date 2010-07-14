<?php

/* Enter your login.confidenttechnologies.com account information here */
$ccap_api_settings = array(
    'customer_id' => '',
    'site_id' => '',
    'api_username' => '',
    'api_password' => ''
);

/* URL of the CAPTCHA API server.  You shouldn't need to modify this, unless
 * you are running your own captcha service.
 */
$ccap_server_url = 'http://captcha.confidenttechnologies.com/';

/* The path to the callback function, used in the multiple CAPTCHA method.
 *
 * If you place callback.php in the same folder as the form that contains
 * Confident CAPTCHA, then the relative URL of 'callback.php' will work.
 * If you place the Confident CAPTCHA library files in a different folder,
 * you'll need a full URL path like '/confident/callback.php'
 */
$ccap_callback_url = 'callback.php';

/* The name of the default policy
 * 
 * The following polices are included in the library:
 * CCAP_DevelopmentPolicy - This policy records API calls, and can add debug 
 *  messages to the page.  However, this leaks secrets, so don't use in 
 *  production. Good for initial page development and debugging.
 * CCAP_ProductionFailOpen - This policy will silently remove the CAPTCHA if
 *  there is an issue at CAPTCHA creation (bad credentials, server
 *  unavailable, etc.). This will allow the form to work as if there was no
 *  CAPTCHA. Good for contact forms, download forms.
 * CCAP_ProductionFailClosed - This policy will keep the form from submitting
 *  if there is an issue at CAPTCHA creation (bad credentials, server
 *  unavailable, etc.). This will prevent the form from working. Good for
 *  account creation forms.
 */
$ccap_default_policy = 'CCAP_ProductionFailOpen';

# Local overrides - used by Confident Technologies for testing.
if (file_exists('local_config.php')) {
   include('local_config.php');
}
