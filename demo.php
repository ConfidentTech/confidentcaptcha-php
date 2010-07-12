<?php
require_once ("config.php");
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
// $ccap_policy = new CCAP_ProductionFailOpen($ccap_api);

/* Safe policy for production, on account registration form 
 * If CAPTCHA creation fails, then the form will not work
 */
// require_once("confidentcaptcha/ccap_prod_closed_policy.php");
// $ccap_policy = new CCAP_ProductionFailClosed($ccap_api);

// Set these to non-NULL try out different CAPTCHAs 
$display_style = NULL; // 'flyout' or 'lightbox'
$include_audio = NULL; // true or false, audio CAPTCHA must be enabled for your account
$height = NULL;        // Height of visual CAPTCHA in pictures
$width = NULL;         // Width of visual CAPTCHA in pictures
$length = NULL;        // How many pictures the user must pick (minimum 3)
$code_color = NULL;    // Color of letter code on pictures (White, Red, Orange, Yellow, Green, Teal, Blue, Indigo, Violet, Gray)

/* Page Templates */

/* Generate a page from a template and an array of key=>value */
function generate_page($template, $tags)
{
    $page = $template;
    $keys = array();
    $values = array();
    foreach ($tags as $key => $value) {
        $page = str_replace('{'.$key.'}', $value, $page);
    }
    return $page;
}

// Insertion to get debug messages
// Don't use on your page
$debug_area = <<< DEBUG
<div id="confidentcaptcha_debug" style="display: none">
<h2>CONFIDENT CAPTCHA DEBUG AREA</h2>
<p>
Debug messages will appear here if you are using CCAP_DevelopmentPolicy.
Don't use this debug code in production - it will leak your API credentials.
</p><p>
Use the <a href="#confidentcaptcha_actions">links at the bottom</a> to get more
debug information.
</p>
<ul></ul>
<a name="confidentcaptcha_actions">Actions:</a>
<a href="#" class='confidentcaptcha_debug_refresh'>Fetch new debug
 messages</a>
<a href="#" class='confidentcaptcha_debug_dump'>Dump policy state</a>
</div>
<script type="text/javascript">
    function confidentcaptcha_get_debug(depth, first_call, method)
    {
        if (depth > 5) { return; }
        $.ajax({
            type: 'POST',
            url: "$callback_url",
            data: {endpoint: method},
            dataType: 'text',
            success: function(html) {
                $("#confidentcaptcha_debug").css("display","block");
                if (html) {
                    $("#confidentcaptcha_debug ul").append(
                        "<li>"+html+"</li>");
                    // Recursively call until empty string is returned
                    if (method == 'get_api_debug') {
                        confidentcaptcha_get_debug(depth + 1, false, method);
                    }
                } else if (depth == 1) {
                    $("#confidentcaptcha_debug ul").append(
                        "<li>No new debug messages</li>");
                }
            },
            error: function() {
                if (!first_call) {
                    // Will return 400 if CCAP_DevelopmentPolicy is not used
                    $("#confidentcaptcha_debug ul").append(
                        "<li><b>Error: callback failed.  Are you using" +
                        "CCAP_DevelopmentPolicy?</b></li>"
                    );
                }
            }
        });
    };
    $(document).ready(function() {
        confidentcaptcha_get_debug(1, true, 'get_api_debug');
        
        $("a.confidentcaptcha_debug_refresh").click(function() {
            confidentcaptcha_get_debug(1, false, 'get_api_debug');
            return false;
        });
        $("a.confidentcaptcha_debug_dump").click(function() {
            confidentcaptcha_get_debug(1, false, 'get_policy_dump');
            return false;
        });
    });
</script>
DEBUG;

// Shared page header
$header_template = <<<TEMPLATE
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head runat="server">
    <title>{TITLE}</title>
    <script type='text/javascript'
      src='http://code.jquery.com/jquery-1.4.2.min.js'></script>
</head>
TEMPLATE;

// Error page template
$error_template = $header_template . <<<TEMPLATE
  <p>We're sorry, something went wrong.  Please try again later.</p>
  {ERROR}
  $debug_area
</body>
</html>
TEMPLATE;

// CAPTCHA page template
$captcha_template = $header_template . <<<TEMPLATE
  <p>
    This is a sample page for the {METHOD} CAPTCHA method of Confident 
    CAPTCHA. If this were a real page, then this would be part of a form, such 
    as a sign-up form, a blog comment form, or some other page where you want
    to prove that the user is human before allowing them access.
  </p>
  <p>{WHEN_CHECKED}</p>
  <p>Things to try:</p>
  <ol>
    {THINGS_TO_TRY}
  </ol>
  {CAPTCHA_JAVASCRIPT}
  <form method='POST'>
      <!-- Your other form inputs (email, comments, etc.) go here -->
      {CAPTCHA_HTML}
      <input type='submit' name='submit' value='Submit'>
  </form>
  <p>{CHECK_CAPTCHA_TEXT}</p>
  $debug_area
</body>
</html>
TEMPLATE;

/* Generate the multiple captcha page */
function captcha_page($captcha_type, $ccap_policy)
{
    global $captcha_template, $error_template, $callback_url;

    global $display_style, $include_audio, $height, $width, $length, 
        $code_color;

    $title = ucwords('Confident CAPTCHA - '.$captcha_type.' CAPTCHA Method');

    // Peform any setup needed at the start of a page w/ CAPTCHA
    $start_error = $ccap_policy->start_captcha_page();
    if ($start_error !== NULL) {
        $tags = array(
            'ERROR'       => $start_error,
            'TITLE'       => $title.' - Configuration Error',
            'HEAD_SCRIPT' => ''
        );
        $error_page = generate_page($error_template, $tags);
        return $error_page;
    }

    // If POST, then check last CAPTCHA
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $block_id = $_REQUEST['confidentcaptcha_block_id'];
        $captcha_id = $_REQUEST['confidentcaptcha_captcha_id'];
        $code = $_REQUEST['confidentcaptcha_code'];
        $captcha_solved = $ccap_policy->check_visual($block_id, $captcha_id,
            $code);
        // For this sample, just print if successful or not.
        if ($captcha_solved) {
            $check_captcha_text = 'Success!  Try another';
        } else {
            $check_captcha_text = 'Incorrect.  Try again';
        }
        $check_captcha_text.=", or go back to the
            <a href='sample_classy.php'>config check</a>";
    } else {
        $check_captcha_text = "Solve the CAPTCHA above, then click Submit.";
    }

    // On both POST and GET, Generate new CAPTCHA HTML
    $ccap_policy->reset();
    $captcha_html = $ccap_policy->create_visual($captcha_type,
        $display_style, $include_audio, $height, $width, $length, $code_color);

    // Insert the Confident CAPTCHA into page template
    if ($captcha_type == 'single')
    {
        // Single CAPTCHA versions
        $things_to_try = "
            <li>Solve the CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA, then Submit.</li>
            <li>Submit without attempting the CAPTCHA.</li>";
        $captcha_javascript = "";
        $when_checked = "
            When you solve the CAPTCHA below, nothing will happen until you
            submit the form.  At that point, the CAPTCHA will be checked.";
    } else {
        // Multiple CAPTCHA versions
        $things_to_try = "
            <li>Solve the CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA, then solve the second CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA three times, then Submit.</li>
            <li>Submit without attempting the CAPTCHA.</li>";
        $captcha_javascript = "
            <!-- Needed for ConfidentSecure Multiple CAPTCHA -->
            <script type='text/javascript'>
                var CONFIDENTCAPTCHA_CALLBACK_URL = \"$callback_url\";
                var CONFIDENTCAPTCHA_INCLUDE_AUDIO = true;
            </script>";
        $when_checked = "
            When you solve the CAPTCHA below, it will immediately confirm if
            the CAPTCHA is correct.  The result will be stored in the 
            server-side session data store.  When you then submit the form, 
            this data store will be checked to see what the result was.";
    }
    $tags = array(
        'TITLE' => $title,
        'HEAD_SCRIPT' => "",
        'METHOD' => $method,
        'WHEN_CHECKED' => $when_checked,
        'THINGS_TO_TRY' => $things_to_try,
        'CAPTCHA_JAVASCRIPT' => $captcha_javascript,
        'CAPTCHA_HTML' => $captcha_html,
        'CHECK_CAPTCHA_TEXT' => $check_captcha_text
    );
    $captcha_page = generate_page($captcha_template, $tags);

    return $captcha_page;
}

// Sample index page template
$index_template = $header_template . <<<TEMPLATE
<body>
 <p>Welcome to the Confident CAPTCHA PHP sample.  The table below 
  details if your configuration is supported by Confident CAPTCHA.  Local
  settings are set in <tt>config.php</tt>, and remote settings come from
  <a href="http://captcha.confidenttechnologies.com/">captcha.confidenttechnologies.com</a>.
 </p>
 {CHECK_CONFIG_HTML}
 <p>{CHECK_INSTRUCTIONS}</p>
 <p>There are two CAPTCHA configurations available:</p>
 <ul>
   <li><a href="?captcha_type=multiple">Multiple CAPTCHA Method</a> - Multiple
       CAPTCHA attempts, checked at CAPTCHA completion</li>
   <li><a href="?captcha_type=single">Single CAPTCHA Method</a> - One CAPTCHA 
       attempt, checked at form submit</li>
 </ul>
 $debug_area
</body>
</html>
TEMPLATE;

/* Generate the index page */
function index_page($ccap_policy)
{
    global $index_template;
    
    $ccap_policy->reset();
    $check_config_response = $ccap_policy->check_config();
    $check_config_html = $check_config_response['html'];
    $credentials_good = $check_config_response['passed'];
    if ($credentials_good) {
        $check_instructions = "Your configuration is supported by the 
            Confident CAPTCHA PHP sample code. Use this <tt>config.php</tt> in
            your own project.";
    } else {
        $check_instructions = "<b>Your configuration is <i>not</i> supported
            by the Confident CAPTCHA PHP sample code</b>.  Please fix the 
            errors before trying the samples and integrating into your own 
            project.";
    }

    $tags = array(
        'TITLE'              => 'Welcome to the Confident CAPTCHA Sample Code',
        'HEAD_SCRIPT'        => '',
        'CHECK_CONFIG_HTML'  => $check_config_html,
        'CHECK_INSTRUCTIONS' => $check_instructions
    );
    $index_page = generate_page($index_template, $tags);
    return $index_page;
}

// Handle the request
if (isset($_REQUEST['captcha_type'])) {
    $page = captcha_page($_REQUEST['captcha_type'], $ccap_policy);
} else {
    $page = index_page($ccap_policy);
}
echo $page;