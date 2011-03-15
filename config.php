<?php

/* Enter your login.confidenttechnologies.com account information here */
$ccap_api_settings = array(
    'customer_id' => '',
    'site_id' => '',
    'api_username' => '',
    'api_password' => ''
);

/* URL of the CAPTCHA API server.
 * Use the first option if your page is an HTTP page.
 * Use the second option if your page is an HTTPS page.
 * Most browsers won't care either way, but if your page is an HTTPS page, IE
 *  will display a warning unless you use the second option.
 */
$ccap_server_url = 'http://captcha.confidenttechnologies.com/';
#$ccap_server_url = 'https://captcha.confidenttechnologies.com/';

/* If you are using the HTTPS URL, you can verify the certificate in one
 * of two ways.  You can set ccap_curlopt_cainfo to the absolute path to a
 * certificate file.  You can set ccap_curlopt_capath to the absolute path to
 * a certificate folder.  If you leave both empty, then certificate chain
 * verification is skipped.  See CURLOPT_CAINFO and CURLOPT_CAPATH in
 * http://www.php.net/manual/en/function.curl-setopt.php
 * for more details.
 */
$ccap_curlopt_capath = '';
$ccap_curlopt_cainfo = '';

/* The path to the callback function, used in the multiple CAPTCHA method.
 * If you place callback.php in the same folder as the form that contains
 * Confident CAPTCHA, then the relative URL of 'callback.php' will work.
 * If you place the Confident CAPTCHA library files in a different folder,
 * you'll need a full URL path like '/confident/callback.php'
 */
$ccap_callback_url = 'callback.php';

/* Default CAPTCHA options
 *
 */
$ccap_options = array(
    /**
     * Visual CAPTCHA - Display style ('flyout', 'lightbox', or 'modal')
     * If unset, the CAPTCHA API server defaults to 'lightbox'
     */
    // 'display_style' => 'lightbox',

    /**
     * Visual CAPTCHA - Include the audio CAPTCHA alternative (if enabled)
     * If unset, the CAPTCHA API server defaults to FALSE
     */
    // 'include_audio' => FALSE,

    /**
     * Visual CAPTCHA - Height in pictures
     * If unset, the CAPTCHA API server defaults to 3
     */
    // 'height' => 3,

    /**
     * Visual CAPTCHA - Width in pictures
     * If unset, the CAPTCHA API server defaults to 3
     */
    // 'width' => 3,

    /**
     * Visual CAPTCHA - Number of pictures the user has to select
     * If unset, the CAPTCHA API server defaults to 4
     */
    // 'length' => 4,

    /**
     * Visual CAPTCHA - The color of the letter code on pictures
     * Valid values are 'White', 'Red', 'Orange', 'Yellow', 'Green', 'Teal',
     * 'Blue', 'Indigo', 'Violet', and 'Gray'.
     * If unset, the CAPTCHA API server defaults to 'White'
     */
    // 'code_color' => 'White',

); // end $ccap_options


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
