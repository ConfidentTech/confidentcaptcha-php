<?php

/**
 * This is a demonstration of the Confident CAPTCHA library.  To use this
 * demo, you must sign up for a free account at:
 *  http://confidenttechnologies.com/purchase/CAPTCHA_signup.php
 *
 * The code is a bit complex, indended to be viewed from a browser rather than
 * read as code.  See 'sample_before.php' and 'sample_after.php' for a
 * practical example of how to add Confident CAPTCHA to your own forms.  Be
 * sure to also look for a plugin for your platform, which is even easier.
 */

require_once('config.php');
require_once('confidentcaptcha/ccap_api.php');
require_once('confidentcaptcha/ccap_persist.php');
require_once('confidentcaptcha/ccap_policy_factory.php');

// Get a value from an array, or NULL
function array_get($a, $key) {
    return (isset($a[$key])) ? $a[$key] : NULL;
}

// Use configured state as the working API
$ccap_api_good = new CCAP_API(
    $ccap_api_settings['customer_id'],
    $ccap_api_settings['site_id'],
    $ccap_api_settings['api_username'],
    $ccap_api_settings['api_password'],
    $ccap_server_url);

// Has the configuration been tested?
$settings_good = array_get($_REQUEST, 'ccap_settings_good');
if (!$settings_good) {
    $cred_check = $ccap_api_good->check_credentials();
    if ($cred_check->status != 200) {
        $settings_good = FALSE;
    } else {
        $settings_good = (FALSE === stripos($cred_check->body,
            "api_failed='True'"));
    }
}

// Use a working system or a bad one?
// TODO
$ccap_api = $ccap_api_good;
$ccap_persist = new CCAP_PersistSession();

// Pick the policy
$policy = array_get($_REQUEST, 'ccap_policy');
$valid_policies = Array('CCAP_ProductionFailOpen',
    'CCAP_ProductionFailClosed', 'CCAP_DevelopmentPolicy');
if (empty($policy)) {
    $policy_text = 'unset, defaults to ';
    $used_policy = $ccap_default_policy;
} elseif (!in_array($policy, $valid_policies)) {
    $policy_text = "\"$policy\" is not valid, defaults to ";
    $used_policy = $ccap_default_policy;
} else {
    $policy_text = '';
    $used_policy = $policy;
}

if (!in_array($used_policy, $valid_policies)) {
    $policy_text = "\"$used_policy\", but that's not valid either, so using ";
    $used_policy = $valid_policies[0];
}

$ccap_policy = CCAP_PolicyFactory::create($used_policy, $ccap_api,
    $ccap_persist);

if ($used_policy == 'CCAP_ProductionFailOpen') {
    $policy_text .= "CCAP_ProductionFailOpen - When CAPTCHA creation fails,
        the form will succeed.  Useful for contact forms, where you want
        the form to work even when the CAPTCHA doesn't.";
} elseif ($used_policy == 'CCAP_ProductionFailClosed') {
    $policy_text .= "CCAP_ProductionFailClosed - When CAPTCHA creation fails,
        the form will fail.  Useful for account creation forms, where you
        don't want the form to proceed without a CAPTCHA check.";
} elseif ($used_policy == 'CCAP_DevelopmentPolicy') {
    $policy_text .= "CCAP_DevelopmentPolicy - Records all calls made to the
        CAPTCHA API server.  Useful for initial form development and
        troubleshooting, but will leak secrets if used in production.";
}

// Load CAPTCHA parameters
$display_style = array_get($_REQUEST, 'ccap_display');
$include_audio = array_get($_REQUEST, 'ccap_include_audio');
$height = array_get($_REQUEST, 'ccap_height');
$width = array_get($_REQUEST, 'ccap_width');
$length = array_get($_REQUEST, 'ccap_length');
$code_color = array_get($_REQUEST, 'ccap_code_color');

// Calculate CAPTCHA strength
function factorial ($x) 
{
    if ($x <= 1)
        return 1;
    else
        return ($x * factorial ($x-1));
}

function captcha_strength($height, $width, $length)
{
    $images = $height * $width;
    if ($length >= $images)
        return 0;
    else
        return factorial($images) / factorial($images - $length);
}

$used_height = intval($height);
$used_width = intval($width);
$used_length = intval($length);
if (empty($height)) $used_height = 3;
if (empty($width)) $used_width = 3;
if (empty($length)) $used_length = 4;
$strength = captcha_strength($used_height, $used_width, $used_length);
$strength_text = "1 in $strength chance of a spam bot randomly guessing
  correctly, based on height, width, length";
if ($strength < 1000) $strength_text .= ' (must be at least 1 in 1000)';

// Get display names of parameters
$valid_display_styles = array('flyout', 'lightbox');
if (empty($display_style)) {
    $display_style_text = 'unset (defaults to "flyout")';
} else {
    $display_style_text = "\"$display_style\"";
    if (!in_array($display_style, $valid_display_styles)) 
        $display_style_text .= ' (not valid)';
}

if (empty($include_audio)) {
    $include_audio_text = 'unset (defaults to FALSE)';
} else {
    $include_audio_text = "$include_audio_text";
    if (!is_bool($include_audio)) $include_audio_text .= ' (non-boolean, not valid)';
}

if (empty($height)) {
    $height_text = 'unset (defaults to 3)';
} else {
    $height_text = "$height";
    if (!is_numeric($height)) {
        $height_text .= ' (non-integer, not valid)';
    } elseif ($used_height < 1) {
        $height_text .= ' (not positive, not valid)';
    } elseif ($strength < 1000) {
        $height_text .= ' (CAPTCHA strength < 1000, not valid)';
    }
}

if (empty($width)) {
    $width_text = 'unset (defaults to 3)';
} else {
    $width_text = "$width";
    if (!is_numeric($width)) {
        $width_text .= ' (non-integer, not valid)';
    } elseif ($used_width < 1) {
        $width_text .= ' (not positive, not valid)';
    } elseif ($strength < 1000) {
        $width_text .= ' (CAPTCHA strength < 1000, not valid)';
    }
}

if (empty($length)) {
    $length_text = 'unset (defaults to 4)';
} else {
    $length_text = "$length";
    if (!is_numeric($length)) {
        $length_text .= ' (non-integer, not valid)';
    } elseif ($used_length < 1) {
        $length_text .= ' (not positive, not valid)';
    } elseif ($strength < 1000) {
        $length_text .= ' (CAPTCHA strength < 1000, not valid)';
    }
}

$valid_colors = array('White', 'Red', 'Orange', 'Yellow', 'Green', 'Teal',
    'Blue', 'Indigo', 'Violet', 'Gray');
if (empty($code_color)) {
    $code_color_text = 'unset (defaults to "White")';
} else {
    $code_color_text = "\"$code_color\"";
    if (!in_array($code_color, $valid_colors))
        $code_color_text .= ' (not valid)';
}

/* URL query for these settings */
function url($extra = NULL)
{
    global $display_style, $include_audio, $height, $width, $length,
        $code_color, $policy, $settings_good;

    $p = array();
    if ($settings_good) $p['ccap_settings_good'] = $settings_good;
    if (!empty($display_style)) $p['ccap_display'] = $display_style;
    if (!empty($include_audio)) $p['include_audio'] = $include_audio;
    if (!empty($height)) $p['ccap_height'] = $height;
    if (!empty($width)) $p['ccap_width'] = $width;
    if (!empty($length)) $p['ccap_length'] = $length;
    if (!empty($code_color)) $p['ccap_code_color'] = $code_color;
    if (!empty($policy)) $p['ccap_policy'] = $policy;
    
    if (is_array($extra)) $p = array_merge($p, $extra);

    $url = $_SERVER['SCRIPT_NAME'];
    if ($p) $url .= '?' . http_build_query($p);
    return $url;
}

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
Use the <a href="#confidentcaptcha_actions">links at the bottom</a> to get
more debug information.
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
        if (!"$ccap_callback_url") {
            $("#confidentcaptcha_debug ul").append(
                "<li>ccap_callback_url is not set</li>");
            return;
        }
        $.ajax({
            type: 'POST',
            url: "$ccap_callback_url",
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

// Settings list
$settings_list = <<<SETTINGS
<ul>
  <li>Policy: $policy_text</li>
  <li>Display Style: $display_style_text</li>
  <li>Include Audio?: $include_audio_text</li>
  <li>Height: $height_text</li>
  <li>Width: $width_text</li>
  <li>Length: $length_text</li>
  <li>Code Color: $code_color_text</li>
</ul>
<p>
CAPTCHA strength: $strength_text.
</p>
SETTINGS;

// CAPTCHA page template
$captcha_template = $header_template . <<<TEMPLATE
<body>
  <h1>Confident CAPTCHA Demonstration</h1>
  <p>
    This is a demo of Confident CAPTCHA. If this were a real page, then this
    would be part of a form, such as a sign-up form, a blog comment form, or
    some other page where you want to prove that the user is human before
    allowing them access.
  </p>
  <p>{WHEN_CHECKED}</p>
  <p>Things to try:</p>
  <ol>
    {THINGS_TO_TRY}
  </ol>
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

/* Generate the captcha page */
function captcha_page($with_callback, $ccap_policy)
{
    global $captcha_template, $error_template, $ccap_callback_url;

    global $display_style, $include_audio, $height, $width, $length, 
        $code_color;

    $title = ucwords('Confident CAPTCHA Demonstration');

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
        $block_id = array_get($_REQUEST, 'confidentcaptcha_block_id');
        $captcha_id = array_get($_REQUEST, 'confidentcaptcha_captcha_id');
        $code = array_get($_REQUEST, 'confidentcaptcha_code');
        $captcha_solved = $ccap_policy->check_visual($block_id, $captcha_id,
            $code);
        // For this sample, just print if successful or not.
        if ($captcha_solved) {
            $check_captcha_text = 'Success!  Try another, or ';
        } else {
            $check_captcha_text = 'Incorrect.  Try again, or ';
        }
    } else {
        $check_captcha_text = "Solve the CAPTCHA above, then click Submit.
            The result will appear here.  Or, you can ";
    }
    $url = url();
    $check_captcha_text .= "<a href=\"$url\">go back to the previous page</a>
        to change the CAPTCHA type or settings.";
    

    // On both POST and GET, Generate new CAPTCHA HTML
    $ccap_policy->reset();
    $callback_url = ($with_callback) ? $ccap_callback_url : NULL;
    $captcha_html = $ccap_policy->create_visual($callback_url,
        $display_style, $include_audio, $height, $width, $length,
        $code_color);

    // Insert the Confident CAPTCHA into page template
    if ($with_callback) {
        // CAPTCHA with a callback for instant feedback
        $things_to_try = "
            <li>Solve the CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA, then solve the second CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA three times, then Submit.</li>
            <li>Submit without attempting the CAPTCHA.</li>";
        $when_checked = "
            When you solve the CAPTCHA below, it will immediately confirm if
            the CAPTCHA is correct.  The result will be stored in the 
            server-side session data store.  When you then submit the form, 
            this data store will be checked to see what the result was.";
    } else {
        // CAPTCHA without a callback
        $things_to_try = "
            <li>Solve the CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA, then Submit.</li>
            <li>Submit without attempting the CAPTCHA.</li>";
        $when_checked = "
            When you solve the CAPTCHA below, nothing will happen until you
            submit the form.  At that point, the CAPTCHA will be checked.";
    }
    $tags = array(
        'TITLE' => $title,
        'HEAD_SCRIPT' => "",
        'WHEN_CHECKED' => $when_checked,
        'THINGS_TO_TRY' => $things_to_try,
        'CAPTCHA_HTML' => $captcha_html,
        'CHECK_CAPTCHA_TEXT' => $check_captcha_text,
        'CONFIG_URL' => url()
    );
    $captcha_page = generate_page($captcha_template, $tags);

    return $captcha_page;
}

// New settings form
function new_settings_form()
{
    global $display_style, $include_audio, $height, $width, $length, 
        $code_color, $policy, $settings_good;
    global $valid_policies, $valid_display_styles, $valid_colors;

    $policy_options = "\n    <option value=\"\"";
    if (empty($policy)) $policy_options .= 'selected = "selected"';
    $policy_options .= '>(unset)</option>';
    foreach($valid_policies as $p) {
        $sel = ($p == $policy) ? 'selected = "selected"' : '';
        $policy_options .= "\n    <option value=\"$p\" $sel>$p</option>";
    }
    $policy_options .= '\n  ';
    
    $display_options = "\n    <option value=\"\"";
    if (empty($display_style)) $display_options .= 'selected = "selected"';
    $display_options .= '>(unset)</option>';
    foreach($valid_display_styles as $d) {
        $sel = ($d == $display_style) ? 'selected = "selected"' : '';
        $display_options .= "\n    <option value=\"$d\" $sel>$d</option>";
    }
    $display_options .= '\n  ';
    
    $ia_selected = ($include_audio) ? 'selected' : '';
    
    $color_options = '';
    $color_options = "\n    <option value=\"\"";
    if (empty($code_color)) $color_options .= 'selected = "selected"';
    $color_options .= '>(unset)</option>';
    foreach($valid_colors as $c) {
        $sel = ($c == $code_color) ? 'selected = "selected"' : '';
        $color_options .= "\n    <option value=\"$c\" $sel>$c</option>";
    }
    $color_options .= '\n  ';
    
    $form = <<<FORM
<form name="settings" action="$_SERVER[SCRIPT_NAME]" method="get">
  <input type="hidden" name="ccap_settings_good" value="$settings_good" />
  <label>Policy:</label>
  <select name="ccap_policy">$policy_options</select>
  <br/>
  <label>Display Type:</label>
  <select name="ccap_display">$display_options</select>
  <br/>
  <label>Include Audio?</label>
  <input type="checkbox" name="ccap_include_audio" value="1" $ia_selected />
  <br/>
  <label>Height:</label>
  <input type="text" name="ccap_height" value="$height" />
  <br />
  <label>Width:</label>
  <input type="text" name="ccap_width" value="$width" />
  <br />
  <label>Length:</label>
  <input type="text" name="ccap_length" value="$length" />
  <br />
  <label>Code Color:</label>
  <select name="ccap_code_color">$color_options</select>
  <br/>
  <input type="submit" value="Submit" />
</form>
<p>Or, <a href="$_SERVER[SCRIPT_NAME]">reset to defaults</a></p>
FORM;
    return $form;
}


// Sample index page template - good config
$good_index_template = $header_template . <<<TEMPLATE
<body>
  <h1>Confident CAPTCHA Demonstration</h1>
  <p>There are two Confident CAPTCHA types available:</p>
  <ul>
  <li><a href="{IN_PAGE_URL}">Instant Verification CAPTCHA Method</a> -
    CAPTCHA is checked on completion, and the user gets multiple chances.
    This requires a callback resource for the instant verification.
  </li>
  <li><a href="{AT_POST_URL}">Delayed Verification CAPTCHA Method</a> -
    CAPTCHA is checked after form submission, and the user gets one chance.
  </li>
 </ul>
 <h2>Current Settings</h2>
 $settings_list
 <h2>New Settings</h2>
 {NEW_SETTINGS_FORM}
 $debug_area
</body>
</html>
TEMPLATE;

// Sample index page template - bad config
$bad_index_template = $header_template . <<<TEMPLATE
<body>
 <h1>Confident CAPTCHA Demonstration - Errors Detected</h1>
 <p>
 Your configuration is NOT supported by Confident CAPTCHA.  Please visit the
 <a href="check.php">configuration check page</a> and fix any problems.
 </p>
 $debug_area
</body>
</html>
TEMPLATE;

/* Generate the index page */
function index_page($ccap_policy)
{
    global $good_index_template, $bad_index_template, $ccap_callback_url;
    
    $ccap_policy->reset();
    $check_config_response = $ccap_policy->check_config($ccap_callback_url);
    $check_config_html = $check_config_response['html'];
    $config_good = $check_config_response['passed'];
    if ($config_good) {
        $template = $good_index_template;
        $new_settings_form = new_settings_form();
    } else {
        $template = $bad_index_template;
        $new_settings_form = '';
    }

    $tags = array(
        'TITLE'              => 'Welcome to the Confident CAPTCHA Sample Code',
        'HEAD_SCRIPT'        => '',
        'NEW_SETTINGS_FORM'  => $new_settings_form,
        'IN_PAGE_URL'        => url(array('with_callback'=>'1')),
        'AT_POST_URL'        => url(array('with_callback'=>'0'))
    );
    $index_page = generate_page($template, $tags);
    return $index_page;
}


// Handle the request
if (isset($_REQUEST['with_callback'])) {
    $page = captcha_page(('1' == $_REQUEST['with_callback']), $ccap_policy);
} elseif (isset($_REQUEST['ccap_config_page'])) {
    $page = config_page($ccap_policy);
} else {
    $page = index_page($ccap_policy);
}
echo $page;