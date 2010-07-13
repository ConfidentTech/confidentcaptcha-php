<?php

/*
You must elect to use this software under one of:
 * The GNU GPL v2.0 License or later
 * The Simplified BSD License

If licensed under GNU GPL v2.0 or later:
 Copyright 2010 Confident Technologies, Inc.  All rights reserved.

 This file is part of the Confident CAPTCHA Library for PHP.

 The Confident CAPTCHA Library for PHP is free software: you can
 redistribute it and/or modify it under the terms of the GNU General Public
 License as published by the Free Software Foundation, either version 2 of
 the License, or (at your option) any later version.

 The Confident CAPTCHA Library for PHP is distributed in the hope that it will
 be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 Public License for more details.

 You should have received a copy of the GNU General Public License along with
 the Confident CAPTCHA Library for PHP.  If not, see
 <http://www.gnu.org/licenses/>.

If licensed under the Simplified BSD License:
 Copyright 2010 Confident Technologies, Inc. All rights reserved.

 Redistribution and use in source and binary forms, with or without
 modification, are permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, 
      this list of conditions and the following disclaimer.

   2. Redistributions in binary form must reproduce the above copyright 
      notice, this list of conditions and the following disclaimer in the 
      documentation and/or other materials provided with the distribution.

   THIS SOFTWARE IS PROVIDED BY CONFIDENT TECHNOLOGIES, INC. ``AS IS'' AND ANY
   EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
   DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> OR CONTRIBUTORS BE LIABLE
   FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
   DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
   SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER 
   CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
   LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
   OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
   DAMAGE.

   The views and conclusions contained in the software and documentation are 
   those of the authors and should not be interpreted as representing official
   policies, either expressed or implied, of Confident Technologies, Inc.
*/

/**
 * Confident CAPTCHA library for PHP - Policy interface
 *
 * Confident CAPTCHA is free image-based challenge that relies on image
 * categorization being easy for humans (easier than text-based CAPTCHAs)
 * but hard for computers.  It is provided as a web service.  To use this
 * library, you'll need to sign up for a free account on the
 * {@link http://confidenttechnologies.com/purchase/CAPTCHA_signup.php Confident Technologies signup page}
 *
 * For the latest version of this library, or to file issues, request
 * features, etc., see
 * {@link http://github.com/ConfidentTech/confidentcaptcha-php the project page on GitHub}.
 *
 * Confident Technologies dual-licenses this code under GNU GPL v2.0 or later
 * and the Simplified BSD License.  Contact Confident Technologies if you need
 * different licensing terms.
 *
 * @package   confidentcaptcha-php
 * @author    John Whitlock <jwhitlock@confidenttech.com>
 * @copyright Copyright (c) 2010, Confident Technologies, Inc.
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2.0 or later
 * @license   http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version   20100621_PHP_2
 */

/**
 * Policy interface for the Confident CAPTCHA API
 *
 * In most cases, the web site will call the Confident CAPTCHA API and use the
 * returned data.  However, if something goes wrong, such as failed API
 * credentials or the site is unavailable due to maintenance, then the status
 * of the API call and the web site policy will determine what happens.  This
 * class provides the policy interface, so that the website code can stay the
 * same when the policy is switched.
 *
 * @package confidentcaptcha-php
 */
abstract class CCAP_Policy
{
    /**
     * API interface to use for calls
     * @var CCAP_Api
     */
    public $api = NULL;

    /**
     * Debug Level for API calls
     *
     * 0 - No debugging
     * 1 - Only exceptions (non-200s)
     * 2 - All API calls
     * @var integer
     */
    public $api_debug_level = 0;

    /**
     * Persistence interface to use for storing data
     * @var CCAP_Persistence
     */
    public $persist = NULL;

    /**
     * CAPTCHA type - multiple or single
     *
     * With multiple CAPTCHA, JavaScript can give a user instant feedback and
     * a chance to try again without refreshing the page.  With single CAPTCHA,
     * checking is always done on form POST, and failure requires a page
     * refresh.
     *
     * Since single CAPTCHA is just a special case of multiple CAPTCHA, it
     * will probably go away in future API versions.
     */
    public $captcha_type = 'multiple';

    /**
     * Block ID for multiple captcha
     * @var string
     */
    public $block_id = NULL;
    
    /**
     * Visual CAPTCHA - No more CAPTCHAs can be created with this block
     * @var boolean
     */
    public $block_done = NULL;

    /**
     * Visual CAPTCHA display style ('flyout' or 'lightbox')
     *
     * If unset, the CAPTCHA API server defaults to 'lightbox'
     * @var string
     */
    public $display_style = NULL;

    /**
     * Visual CAPTCHA - include the audio CAPTCHA alternative (if enabled)
     *
     * If unset, the CAPTCHA API server defaults to FALSE
     * @var boolean
     */
    public $include_audio = NULL;

    /**
     * Visual CAPTCHA - Height in pictures
     *
     * If unset, the CAPTCHA API server defaults to 3
     * @var integer
     */
    public $height = NULL;

    /**
     * Visual CAPTCHA - Width in pictures
     *
     * If unset, the CAPTCHA API server defaults to 3
     * @var integer
     */
    public $width = NULL;

    /**
     * Visual CAPTCHA - Number of pictures the user has to select
     *
     * If unset, the CAPTCHA API server defaults to 4
     * @var integer
     */
    public $length = NULL;

    /**
     * Visual CAPTCHA - The color of the letter code on pictures
     *
     * If unset, the CAPTCHA API server defaults to 'White'
     * @var string
     */
    public $code_color = NULL;

    /**
     * Visual CAPTCHA - TRUE if last create_visual_html succeeded
     * @var boolean
     */
    public $visual_creation_succeeded = NULL;
    
    /**
     * Visual CAPTCHA - TRUE if authentication succeeded
     * @var boolean
     */
    public $visual_authenticated = NULL;
    
    /**
     * Visual CAPTCHA - Visual CAPTCHA ID
     * @var string
     */
    public $visual_id = NULL;
    
    /**
     * Audio CAPTCHA - TRUE if last start_audio succeeded
     * @var boolean
     */
    public $audio_creation_succeeded = NULL;

    /**
     * Audio CAPTCHA - Authenticated
     * @var boolean
     */
    public $audio_authenticated = NULL;
    
    /**
     * Audio CAPTCHA - Audio CAPTCHA ID
     * @var string
     */
    public $audio_id = NULL;
    
    /**
     * Callback - Endpoint for checking callback
     * @var string
     */
    const CALLBACK_CHECK = 'callback_check';
    
    /**
     * Callback - Value returned by endpoint check
     * @var string
     */
    const CALLBACK_OK = 'The callback is working';

    /**
     * Construct a CCAP_Policy
     *
     * @param CCAP_Api $api The API interface to use for calls
     */
    public function __construct($api, $persist)
    {
        $this->api = $api;
        $this->persist = $persist;
    }

    /**
     * Start a page containing CAPTCHA
     */
    public function start_captcha_page()
    {
        $this->persist->load($this);
    }

    /**
     * Check local and remote configuration
     *
     * @return array with keys 'html' (HTML string) and 'passed' (boolean)
     * @todo Add local config check
     */
    public function check_config($callback_url)
    {
        // Local checks
        $local_config = array(array("Item", "Value",
            "Required Value", "Acceptable?"));

        // Check PHP version 5.x
        $php_version = phpversion();
        $php_minimum = "5.0.0";
        if (version_compare($php_version, $php_minimum, '>=')) {
            $php_supported = 'Yes';
        } else {
            $php_supported = 'No';
        }
        $local_config[] = array('PHP version', $php_version, 
            $php_minimum, $php_supported);

        // Check cURL extension
        if (extension_loaded('curl')) {
            $curl_version = phpversion('cURL');
            if (empty($curl_version)) $curl_version = '(installed)';
            $curl_supported = 'Yes';
        } else {
            $curl_version = "(not installed)";
            $curl_supported = 'No';
        }
        $local_config[] = array('cURL extension', $curl_version,
            '(installed)', $curl_supported);
        
        // Check SimpleXML extension
        if (extension_loaded('SimpleXML')) {
            $sxml_version = phpversion('SimpleXML');
            if (empty($curl_version)) $sxml_version = '(installed)';
            $sxml_supported = 'Yes';
        } else {
            $sxml_version = "(not installed)";
            $sxml_supported = 'No';
        }
        $local_config[] = array('SimpleXML extension', $sxml_version,
            '(installed)', $sxml_supported);
        
        // Check CAPTCHA API server URL
        $not_set = '(NOT SET)';
        $url = $this->api->captcha_server_url;
        $expected_url = 'http://captcha.confidenttechnologies.com/';
        if ($url == $expected_url) {
            $url_supported = 'Yes';
        } elseif (empty($url)) {
            $url = $not_set;
            $url_supported = 'No';
        } elseif ((0 == substr_compare($url, 'http', 0, 4)) and
                  (substr($url, -1) == '/')) {
            $url_supported = 'Maybe';
        } else {
            $url_supported = 'No';
        }
        $local_config[] = array('ccap_server_url', $url, $expected_url,
            $url_supported);

        // Check API parameters
        $customer_id = $this->api->customer_id;
        if (empty($customer_id)) {
            $customer_id = $not_set;
            $customer_id_ok = 'No';
        } else {
            $customer_id_ok = 'Yes';
        }
        $local_config[] = array('customer_id', $customer_id, '(some value)',
            $customer_id_ok);

        $site_id = $this->api->site_id;
        if (empty($site_id)) {
            $site_id = $not_set;
            $site_id_ok = 'No';
        } else {
            $site_id_ok = 'Yes';
        }
        $local_config[] = array('site_id', $site_id, '(some value)',
            $site_id_ok);

        $api_username = $this->api->api_username;
        if (empty($api_username)) {
            $api_username = $not_set;
            $api_username_ok = 'No';
        } else {
            $api_username_ok = 'Yes';
        }
        $local_config[] = array('api_username', $api_username, '(some value)',
            $api_username_ok);

        $api_password = $this->api->api_password;
        if (empty($api_password)) {
            $api_password = $not_set;
            $api_password_ok = 'No';
        } else {
            $api_password_ok = 'Yes';
        }
        $local_config[] = array('api_password', $api_password, '(some value)',
            $api_password_ok);

        if (empty($callback_url)) {
            $callback_url = $not_set;
            $callback_ok = 'No';
        } else {
            $callback_ok = 'Yes';
        }
        $local_config[] = array('callback_url', $callback_url,
            "(Local URL)", $callback_ok);

        # Make local tables
        $local = "<h1>Local Configuration</h1>\n";
        $local .= "<table border=\"1\">\n<tr><th>";
        $head_row = array_shift($local_config);
        $local .= implode('</th><th>', $head_row) . "</th></tr>\n";
        $local_ok = TRUE;
        foreach($local_config as $row) {
            $local .= '<tr><td>'.implode('</td><td>', $row)."</td></tr>\n";
            if ($row[-1] == 'No')
                $local_ok = FALSE;
        }
        $local .= '</table>';

        # Add callback check button
        # TODO: Use javascript to check
        $ok = self::CALLBACK_OK;
        $local .= "<br/>
            <form name='callback_check' action='$callback_url' method='post'>
            <input type='hidden' name='endpoint' value='callback_check' />
            <input type='submit' value='Click to check the callback' />
        </form>
        <p>
        Response to clicking above should be '$ok'.
        </p>
        ";
        $local .= "<br/>\n<h1>Remote Configuration</h1>\n";
        
        // Check credentials with API server
        $response = $this->call_api('check_credentials');
        if ($response->status == 200) {
            $html = $response->body;
            $api_passed = (false === strstr($html, "api_failed='True'"));
        } else {
            $html  = "check_credentials call failed with status code: ";
            $html .= $response->status.'.';
            $html .= '<br />response body: <br />'.$response->body;
            $api_passed = false;
        }
        $response =  array(
            'html' => $local . $html, 
            'passed' => $local_ok and $api_passed
        );
        $this->persist->save($this, 'check_config', $response);
        return $response;
    }

    /**
     * Call an API function
     */
    protected function call_api()
    {
        $raw_args = func_get_args();
        $func_name = $raw_args[0];
        unset($raw_args[0]);
        $args = array_values($raw_args);
        $resp = call_user_func_array(array($this->api, $func_name), $args);
        if (($this->api_debug_level >= 2) or
            ($this->api_debug_level == 1 and $resp->status != 200))
        {
            $debug = $this->generate_debug($func_name, $args, $resp);
            $this->handle_debug($debug);
        }
        return $resp;
    }

    /**
     * Generate a debug statement for an API call
     * @param string $api_func_name Name of the CCAP_Api function
     * @param array  $api_func_args Arguments to the CCAP_Api function
     * @param CCAP_ApiResponse $response The response from {@link CCAP_Api}
     */
    protected function generate_debug($api_func_name, $api_func_args,
        $response)
    {
        $method = $response->method;
        $url = $response->url;
        $form = htmlentities($response->form);
        $status = $response->status;
        $body = htmlentities($response->body);
        $from_remote = $response->from_remote;

        $d_success = ($status == 200 ? '(success)' : '(failure)');
        $d_form = ($form ? "\nwith form \"".$form."\"" : "");
        $d_shortcut = ($from_remote ? '' : 'shortcut');
        if ($body) {
            $d_body = "and return body:\n".$body."\n";
        } else {
            $d_body = "and NO return body.\n";
        }

        $debug = "
CCAP_Api function \"$api_func_name\" called
HTTP $method $url $d_form
with $d_shortcut return code $status $d_success
$d_body";
        return $debug;
    }
    
    /**
     * Handle a debug statement
     *
     * The default implementation does nothing
     * 
     * @param string $debug The debug statement
     */
    protected function handle_debug($debug)
    {
    }

    /**
     * Reset to clean state
     *
     * Should be called after a CAPTCHA session
     */
    public function reset()
    {
        $this->captcha_type = 'multiple';
        $this->block_id = NULL;
        $this->block_done = NULL;
        $this->display_style = NULL;
        $this->include_audio = NULL;
        $this->height = NULL;
        $this->width = NULL;
        $this->length = NULL;
        $this->code_color = NULL;
        $this->visual_creation_succeeded = NULL;
        $this->visual_authenticated = NULL;
        $this->visual_id = NULL;
        $this->audio_creation_succeeded = NULL;
        $this->audio_authenticated = NULL;
        $this->audio_id = NULL;
        $this->persist->reset($this);
    }

    /**
     * Create a multiple-CAPTCHA block
     */
    protected function create_block()
    {
        $response = $this->call_api('create_block');
        if ($response->status == 200) {
            $this->block_id = $response->body;
            $this->block_done = FALSE;
        } else {
            $this->block_id = NULL;
            $this->block_done = TRUE;
        }
    }

    /**
     * Create HTML for a visual CAPTCHA
     *
     * If 'multiple' is chosen (recommended), then a block is created as
     * needed, and the block_id is stored in {@link $block_id}.
     *
     * @param string  $captcha_type  'multiple' or 'single'
     * @param string  $display_style 'flyout' or 'lightbox'
     * @param boolean $include_audio Include audio CAPTCHA (if enabled)
     * @param integer $height        Height of visual CAPTCHA in pictures
     * @param integer $width         Width of visual CAPTCHA in pictures
     * @param integer $length        Number of pictures the user must pick
     * @param integer $code_color    Color of letter code on pictures
     *
     * @return string HTML fragment to inject into page
     */
    public function create_visual($captcha_type=NULL, $display_style=NULL, 
        $include_audio=NULL, $height=NULL, $width=NULL, $length=NULL,
        $code_color=NULL)
    {
        // Pick CAPTCHA type, preferring multiple
        if (!is_null($captcha_type)) {
            if ($captcha_type == 'single') {
                $this->captcha_type = 'single';
            } else {
                $this->captcha_type = 'multiple';
            }
        }

        // Get a block_id if needed
        $block_failed = FALSE;
        if ($this->captcha_type == 'multiple') {
            if (is_null($this->block_id))  {
                $response = $this->create_block();
                if (is_null($this->block_id))  {
                    $block_failed = TRUE;
                }
            }
        }

        // Store CAPTCHA creation parameters for future calls
        if (!$block_failed) {
            if (!is_null($display_style)) 
                $this->display_style = display_style;
            if (!is_null($include_audio)) 
                $this->include_audio = include_audio;
            if (!is_null($height)) $this->height = height;
            if (!is_null($width)) $this->width = width;
            if (!is_null($length)) $this->length = length;
            if (!is_null($code_color)) $this->code_color = code_color;
        }

        if ($block_failed) {
            // Response is failed response from block creation
        } elseif ($this->captcha_type == 'multiple') {
            // Create the visual CAPTCHA instance in multiple-CAPTCHA block
            $response = $this->call_api('create_visual', $this->block_id,
                $this->display_style, $this->include_audio, $this->height,
                $this->width, $this->length, $this->code_color);
        } else {
            // Create a single visual CAPTCHA
            $response = $this->call_api('create_captcha', 
                $this->display_style, $this->include_audio, $this->height,
                $this->width, $this->length, $this->code_color);
        }
        
        // Set visual state
        if ($response->status == 200) {
            $this->visual_creation_succeeded = TRUE;
            $this->visual_authenticated = NULL;
            
            // Find visual_id
            // <input name="..._captcha_id" value='theVisualID'
            // Super complex, but no regex
            $html = $response->body;
            $name_pos = strpos($html, '_captcha_id');
            $v = 'value=';
            $value_pos = strpos($html, $v, $name_pos);
            $quote_char = substr($html, $value_pos + strlen($v), 1);
            $end_value_pos = strpos($html, $quote_char, 
                ($value_pos + strlen($v) + 1));
            $vid_len = $end_value_pos - $value_pos - strlen($v) - 1;
            $visual_id = substr($html, $value_pos + strlen($v) + 1, $vid_len);
            $this->visual_id = $visual_id;
        } elseif ($response->status == 410) {
            // Too many failures, no more visual CAPTCHAs in block
            $this->block_done = TRUE;
            $this->visual_id = NULL;
        } else {
            // Some other failure - candidate for fail open / closed
            $this->visual_creation_succeeded = FALSE;
            $this->visual_authenticated = FALSE;
            $this->visual_id = NULL;
        }
        
        $this->persist->save($this, 'create_visual', $result);
        return $this->respond_create_visual($response);
    }
    
    /**
     * Create the response HTML for create_visual
     *
     * @param CCAP_ApiResponse $response The response from {@link CCAP_Api}
     * @return string HTML to inject into the page
     */
    protected function respond_create_visual($response)
    {
        if ($response->status == 200) {
            return $response->body;
        } else {
            return "";
        }
    }

    /**
     * Check visual CAPTCHA submission
     *
     * @param string  $block_id   Block ID from form, (NULL if not included)
     * @param string  $captcha_id CAPTCHA ID from form
     * @param string  $code       User's guess from form
     *
     * @return boolean true if success, false if failure
     */
    public function check_visual($block_id, $captcha_id, $code)
    {
        // Did creating the visual CAPTCHA succeed?
        $response = NULL;
        $check_on_server = TRUE;
        if ($this->visual_creation_succeeded !== TRUE) {
            $check_on_server = FALSE;
        } else {
            // Check that form matches captcha creation
            if (!is_null($this->visual_id) and 
                $captcha_id != $this->visual_id)
            {
                $check_on_server = FALSE;
            }
            if ($this->captcha_type == 'multiple' and
                !is_null($this->block_id) and
                ($block_id != $this->block_id))
            {
                $check_on_server = FALSE;
            }
        }

        // Don't check if we're already authenticated
        if ($this->visual_authenticated === TRUE) {
            $check_on_server = FALSE;
        }
        
        if (!$check_on_server) {
            // Skip calling CAPTCHA API server
        } elseif (is_null($this->block_id)) {
            // empty block_id - assume single CAPTCHA
            $response = $this->call_api('check_captcha', $captcha_id, $code);
        } else {
            // Assume multiple CAPTCHA
            $response = $this->call_api('check_visual', $this->block_id, 
                $captcha_id, $code);
        }
        
        // Set state
        if (!is_null($response)) {
            if ($response->status == 200) {
                $auth = (strtolower($response->body) == 'true');
                $this->visual_authenticated = $auth;
            } else {
                $this->visual_authenticated = FALSE;
            }
        }
        
        $this->persist->save($this, 'check_visual', $result);
        return $this->respond_check_visual($result);
    }
    
    /**
     * Create the response boolean for check_visual
     *
     * @param CCAP_ApiResponse $response The response from {@link CCAP_Api}
     * @return boolean TRUE if authenticated
     */
    protected function respond_check_visual($response)
    {
        return ($this->visual_authenticated === TRUE);
    }

    /**
     * Start an audio CAPTCHA
     *
     * If $block_id is set, then it will be used.  Otherwise, it will be a 
     * single audio CAPTCHA
     *
     * @param string  $phone_number US phone number with area code
     * @param string  $captcha_type 'multiple' or 'single'
     * @param string  $block_id     Block ID from form, NULL if not included
     * @return string Audio response XML
     */
    public function start_audio($phone_number, $captcha_type=NULL,
        $block_id=NULL)
    {
        // Pick CAPTCHA type, preferring multiple
        if (!is_null($captcha_type)) {
            if ($captcha_type == 'single') {
                $this->captcha_type = 'single';
            } else {
                $this->captcha_type = 'multiple';
            }
        }

        // Get a block_id if needed
        $block_failed = FALSE;
        if ($this->captcha_type == 'multiple') {
            if (is_null($this->block_id)) {
                // Did the user pass a block_id?
                if (!is_null($block_id)) {
                    $this->block_id = $block_id;
                } else {
                    $response = $this->create_block();
                    if (is_null($this->block_id))  {
                        $block_failed = TRUE;
                    }
                }
            }
        }

        if ($block_failed) {
            // Let response from create_block be response
        } elseif ($this->captcha_type == 'multiple') {
            // Create the audio CAPTCHA instance in multiple-CAPTCHA block
            $response = $this->call_api('start_audio', $this->block_id,
                $phone_number);
        } else {
            // Create a single audio CAPTCHA
            $response = $this->call_api('start_onekey', $phone_number);
        }
        
        // Update the audio state
        if ($response->status == 200) {
            $this->audio_creation_succeeded = TRUE;
            $this->audio_authenticated = NULL;
            $this->audio_id = $response->body;
        } else {
            $this->audio_creation_succeeded = FALSE;
            $this->audio_authenticated = FALSE;
            $this->audio_id = NULL;
        }
        
        $this->persist->save($this, 'start_audio', $result);
        return $this->respond_create_audio($response);
    }
    
    /**
     * Create the response XML for start_audio
     *
     * @param CCAP_ApiResponse $response The response from {@link CCAP_Api}
     * @return  string Audio response XML
     */
     protected function respond_create_audio($response)
     {
         if ($response->status == 200) {
             return $response->body;
         } else {
             return "";
         }
     }

    /**
     * Check audio CAPTCHA submission
     *
     * @param string  $block_id   Block ID from form, (NULL if not included)
     * @param string  $captcha_id CAPTCHA ID from form
     *
     * @return boolean true if success, false if failure
     */
    public function check_audio($block_id, $captcha_id)
    {
        // Did creating the audio CAPTCHA succeed?
        $response = NULL;
        $check_on_server = FALSE;
        if (!$this->audio_creation_succeeded === FALSE) {
            $check_on_server = FALSE;
        } else {
            // Check that form matches captcha creation
            if (!is_null($this->audio_id) and 
                $captcha_id != $this->audio_id)
            {
                $check_on_server = FALSE;
            }
            if ($this->captcha_type == 'multiple' and
                !is_null($this->block_id) and
                ($block_id != $this->block_id))
            {
                $check_on_server = FALSE;
            }
        }

        // Don't check if we're already authenticated
        if ($this->audio_authenticated === TRUE) {
            $check_on_server = FALSE;
        }
        
        if (!$check_on_server) {
            // Skip calling CAPTCHA API server
        } elseif (is_null($this->block_id)) {
            // empty block_id - assume single CAPTCHA
            $response = $this->call_api('check_onekey', $captcha_id);
        } else {
            // Assume multiple CAPTCHA
            $response = $this->call_api('check_audio', $this->block_id, 
                $captcha_id);
        }
        
        // Set state
        if (!is_null($response)) {
            if ($response->status == 200) {
                $xml = strtolower($response->body);
                if (strpos($xml, '<authenticated>true') !== FALSE) {
                    $this->audio_authenticated = TRUE;
                } elseif (strpos($xml, '<authenticated>false') !== FALSE)  {
                    $this->audio_authenticated = FALSE;
                } else {
                    $this->audio_authenticated = NULL;
                }
            } else {
                $this->audio_authenticated = FALSE;
            }
        }
        
        $this->persist->save($this, 'check_audio', $result);
        return $this->respond_check_audio($result);
    }

    /**
     * Create the response boolean for check_audio
     *
     * @param CCAP_ApiResponse $response The response from {@link CCAP_Api}
     * @return boolean TRUE if authenticated
     */
    protected function respond_check_audio($response)
    {
        return ($this->audio_authenticated === TRUE);
    }
    
    /**
     * Check if we're already authenticated
     * @return boolean TRUE if authenticated, FALSE if failed, NULL if not
     * attempted.
     */
    protected function check()
    {
        if (!is_null($this->visual_authenticated)) {
            return $this->visual_authenticated;
        } elseif (!is_null($this->audio_authenticated)) {
            return $this->audio_authenticated;
        } else {
            return NULL;
        }
    }
    
    /**
     * Provide the callback function
     *
     * @param string $endpoint The desired callback endpoint
     * @param array  $request  The request parameters
     * @return array First element is content, second is array of headers
     */
    public function callback($endpoint, $request)
    {
        $content = "";
        $headers = Array();
        $result = NULL;
        if ($endpoint == 'block_onekey_start') {
            $content = $this>start_audio($_REQUEST['block_id'],
                $_REQUEST['phone_number']);
            $headers[] = "Content-type: text/xml"; 
        } elseif ($endpoint == 'block_onekey_verify') {
            $content = $this->check_audio($_REQUEST['block_id'], 
                $_REQUEST['captcha_id']);
            $headers[] = "Content-type: text/xml";
        } elseif ($endpoint == 'create_captcha_instance') {
            $content = $this->create_visual();
            if ($this->block_done) {
                $headers[] = $_SERVER["SERVER_PROTOCOL"]." 410 Gone";
            }
        } elseif ($endpoint == 'verify_block_captcha') {
            $check = $this->check_visual($_REQUEST['block_id'],
                $_REQUEST['captcha_id'], $_REQUEST['code']);
            $content = ($check ? 'true' : 'false');
        } elseif ($endpoint == self::CALLBACK_CHECK) {
            $content = self::CALLBACK_OK;
        } else {
            $result = $this->callback_extensions($endpoint, $request);
            if (!$result) {
                $headers[] = $_SERVER["SERVER_PROTOCOL"]." 400 Bad Request";
            }
        }
        if (!$result) $result = Array($content, $headers);
        $this->persist->save($this, 'callback', $result);
        return $result;
    }

    /*
     * Provide extended callback functions
     *
     * Return NULL if you can't handle the callback either.
     *
     * @param string $endpoint The desired callback endpoint
     * @param array  $request  The request parameters
     * @return array First element is content, second is array of headers
     */
    protected function callback_extensions($endpoint, $request)
    {
        return NULL;
    }
}