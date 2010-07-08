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
     * Visual CAPTCHA display style ('flyout' or 'lightbox')
     * @var string
     */
    public $display_style = 'flyout';

    /**
     * Visual CAPTCHA - include the audio CAPTCHA alternative (if enabled)
     * @var boolean
     */
    public $include_audio = false;

    /**
     * Visual CAPTCHA - Height in pictures
     * @var integer
     */
    public $height = 3;

    /**
     * Visual CAPTCHA - Width in pictures
     * @var integer
     */
    public $width = 3;

    /**
     * Visual CAPTCHA - Number of pictures the user has to select
     * @var integer
     */
    public $length = 4;

    /**
     * Visual CAPTCHA - The color of the letter code on pictures
     * @var string
     */
    public $code_color = 'White';

    /**
     * Visual CAPTCHA - TRUE if last create_visual_html succeeded
     * @var boolean
     */
    public $visual_creation_succeeded = NULL;

    /**
     * Audio CAPTCHA - TRUE if last start_audio succeeded
     * @var boolean
     */
    public $audio_creation_succeeded = NULL;

    /**
     * Construct a CCAP_Policy
     *
     * @param CCAP_Api $api The API interface to use for calls
     */
    public function __construct($api)
    {
        $this->api = $api;
    }

    /**
     * Start a page containing CAPTCHA
     * @todo Is this a good default?
     */
    public function start_captcha_page()
    {
        if (session_id === "") session_start();
    }

    /**
     * Check local and remote configuration
     *
     * @return array with keys 'html' (HTML string) and 'passed' (boolean)
     * @todo Add local config check
     */
    public function check_config()
    {
        $response = $this->api->check_credentials();
        if ($response->status == 200) {
            $html = $response->body;
            $passed = (false === strstr($html, "api_failed='True'"));
        } else {
            $html  = "check_credentials call failed with status code: ";
            $html .= $response->status.'.';
            $html .= '<br />response body: <br />'.$response->body;
            $passed = false;
        }
        return array('html' => $html, 'passed' => $passed);
    }

    /**
     * Handle failures on create_visual_html function
     *
     * Using die() is rude.  All policies should override this function.
     * See {@link CCAP_ProductionFailClosed} for one quiet solution.
     *
     * @param string $api_func_name Name of the CCAP_Api function that failed
     * @param CCAP_ApiResponse $response The response from {@link CCAP_Api}
     * @return string HTML to inject into the page
     */
    protected function on_create_visual_html_fail($api_func_name, $response)
    {
        $this->visual_creation_succeeded = NULL;
        $msg = "<p>CAPTCHA creation failed.  Please try again later.</p>";
        $msg .= "<p>Response: ".$response->status." - ".$response->body."</p>";
        die($msg);
        return $msg;
    }

    /**
     * Handle success on create_visual_html function
     *
     * In most policies, you'll want to handle success by returning the
     * response body.
     * @param string $api_func_name Name of the CCAP_Api function that succeeded
     * @param CCAP_ApiResponse $response The response from {@link CCAP_Api}
     * @return string HTML to inject into the page
     */
    protected function on_create_visual_html_success($api_func_name, $response)
    {
        $this->visual_creation_succeeded = TRUE;
        return $response->body;
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
    public function create_visual_html($captcha_type=NULL,
        $display_style=NULL, $include_audio=NULL, $height=NULL, $width=NULL,
        $length=NULL, $code_color=NULL)
    {
        // Pick CAPTCHA type, preferring multiple
        if (!is_null($captcha_type)) {
            if ($captcha_type == 'single') {
                $this->captcha_type = 'single';
            } else {
                $this->captcha_type = 'multiple';
            }
        }

        if ($this->captcha_type == 'multiple') {
            // Get a block_id if needed
            if (is_null($this->block_id))  {
                $response = $this->api->create_block();
                if ($response->status == 200) {
                    $block_id = $this->on_create_visual_html_success(
                        'create_block', $response);
                    $this->block_id = $block_id;
                } else {
                    return $this->on_create_visual_html_fail('create_block',
                        $response);
                }
            }
        }

        // Store CAPTCHA creation parameters for future calls
        if (!is_null($display_style)) $this->display_style = display_style;
        if (!is_null($include_audio)) $this->include_audio = include_audio;
        if (!is_null($height)) $this->height = height;
        if (!is_null($width)) $this->width = width;
        if (!is_null($length)) $this->length = length;
        if (!is_null($code_color)) $this->code_color = code_color;

        if ($this->captcha_type == 'multiple') {
            // Create the visual CAPTCHA instance in multiple-CAPTCHA block
            $response = $this->api->create_visual($this->block_id,
                $this->display_style, $this->include_audio, $this->height,
                $this->width, $this->length, $this->code_color);
            if ($response->status != 200) {
                return $this->on_create_visual_html_fail('create_visual',
                    $response);
            } else {
                return $this->on_create_visual_html_success('create_visual',
                    $response);
            }
        } else {
            // Create a single visual CAPTCHA
            $response = $this->api->create_captcha($this->display_style,
                $this->include_audio, $this->height,
                $this->width, $this->length, $this->code_color);
            if ($response->status != 200) {
                return $this->on_create_visual_html_fail('create_captcha',
                    $response);
            } else {
                return $this->on_create_visual_html_success('create_captcha',
                    $response);
            }
        }
    }

    /**
     * Check that the CAPTCHA is valid (creation was successful)
     *
     * @param string $block_id The block ID from the from
     * @param string $captcha_id The CAPTCHA ID from the form
     * @param string $code The guessed code from the form
     *
     * @return string with return HTML if invalid, NULL if valid
     */
    public function check_valid_captcha($block_id, $captcha_id, $code)
    {
        return NULL;
    }

    /**
     * Handle API failures on check function
     *
     * In most policies, you'll want to stop form submission by returning FALSE
     *
     * @param string $api_func_name Name of the CCAP_Api function that failed
     * @param CCAP_ApiResponse $response The response from {@link CCAP_Api}
     * @return boolean TRUE if form should pass, FALSE if should fail
     */
    protected function on_check_fail($api_func_name, $response)
    {
        $this->visual_creation_succeeded = NULL;
        return FALSE;
    }

    /**
     * Handle API success on check function
     *
     * In most policies, you'll want to handle success by returning TRUE if
     *  the response body is the string 'True'.
     *
     * @param string api_func_name Name of the CCAP_Api function that succeeded
     * @param CCAP_ApiResponse response The response from {@link CCAP_Api}
     * @return boolean TRUE if form should pass, FALSE if should fail
     */
    protected function on_check_success($api_func_name, $response)
    {
        $this->visual_creation_succeeded = NULL;
        return ($response->body == 'True');
    }

    /**
     * Check CAPTCHA submission
     *
     * @param string  $block_id   Block ID from form, (NULL if not included)
     * @param string  $captcha_id CAPTCHA ID from form
     * @param string  $code       User's guess from form
     *
     * @return boolean true if success, false if failure
     */
    public function check($block_id, $captcha_id, $code)
    {
        // Check policy's pre-check logic
        $pre_check = $this->check_valid_captcha($block_id, $captcha_id, $code);
        if (!is_null($precheck)) {
            return $precheck;
        }

        // Set the block_id, if any
        if (!empty($block_id)) {
            $this->block_id = $block_id;
        }

        if (is_null($this->block_id))
        {
            // empty block_id - assume single CAPTCHA
            $response = $this->api->check_captcha($captcha_id, $code);
            if ($response->status == 200) {
                $result = $this->on_check_success('check_captcha', $response);
            } else {
                $result = $this->on_check_fail('check_captcha', $response);
            }
        } else {
            // Assume multiple CAPTCHA
            $response = $this->api->check_visual($this->block_id, $captcha_id,
                $code);
            if ($response->status == 200) {
                $result = $this->on_check_success('check_visual', $response);
            } else {
                $result = $this->on_check_fail('check_visual', $response);
            }
        }
        return $result;
    }

    /**
     * Handle failures on start_audio function
     *
     * @param string $api_func_name Name of the CCAP_Api function that failed
     * @param CCAP_ApiResponse $response The response from {@link CCAP_Api}
     * @return string XML to return
     */
    protected function on_start_audio_fail($api_func_name, $response)
    {
        $this->audio_creation_succeeded = NULL;
        $status = $response['status'];
        $body = $response['body'];
        return "<?xml version=\"1.0\"?>\n<response><status>$status</status><onekey_id>$body</onekey_id></response>";
    }

    /**
     * Handle success on start_audio function
     *
     * In most policies, you'll want to handle success by returning the
     * response body.
     * @param string $api_func_name Name of the CCAP_Api function that succeeded
     * @param CCAP_ApiResponse $response The response from {@link CCAP_Api}
     * @return string XML to return
     */
    protected function on_start_audio_success($api_func_name, $response)
    {
        $this->audio_creation_succeeded = TRUE;
        $status = $response['status'];
        $body = $response['body'];
        return "<?xml version=\"1.0\"?>\n<response><status>$status</status><onekey_id>$body</onekey_id></response>";
    }

    /**
     * Start an audio CAPTCHA
     *
     * If $block_id is set, then it will be used.  Otherwise, it will be a 
     * single audio CAPTCHA
     *
     * @param string  $block_id   Block ID from form, NULL if not included
     * @param string  $phone_number US phone number with area code
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

        if ($this->captcha_type == 'multiple') {
            // Did the user pass a block_id?
            if (!is_null($block_id)) {
                $this->block_id = $block_id;
            }

            // Get a block_id if needed
            if (is_null($this->block_id))  {
                $response = $this->api->create_block();
                if ($response->status == 200) {
                    $this->block_id = $response->body;
                } else {
                    return $this->on_start_audio_fail('create_block',
                        $response);
                }
            }
        }

        if ($this->captcha_type == 'multiple') {
            // Create the audio CAPTCHA instance in multiple-CAPTCHA block
            $response = $this->api->start_audio($this->block_id,
                $phone_number);
            if ($response->status != 200) {
                return $this->on_start_audio_fail('start_audio',
                    $response);
            } else {
                return $this->on_start_audio_success('start_audio',
                    $response);
            }
        } else {
            // Create a single audio CAPTCHA
            $response = $this->api->start_onekey($phone_number);
            if ($response->status != 200) {
                return $this->on_start_audio_fail('start_onekey',
                    $response);
            } else {
                return $this->on_start_audio_success('start_onekey',
                    $response);
            }
        }
    }
}
