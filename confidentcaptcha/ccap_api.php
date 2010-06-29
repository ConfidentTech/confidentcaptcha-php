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

 Redistribution and use in source and binary forms, with or without modification, are
 permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this list of
      conditions and the following disclaimer.

   2. Redistributions in binary form must reproduce the above copyright notice, this list
      of conditions and the following disclaimer in the documentation and/or other materials
      provided with the distribution.

   THIS SOFTWARE IS PROVIDED BY CONFIDENT TECHNOLOGIES, INC. ``AS IS'' AND ANY EXPRESS OR IMPLIED
   WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
   FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> OR
   CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
   CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
   SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
   ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
   NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
   ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

   The views and conclusions contained in the software and documentation are those of the
   authors and should not be interpreted as representing official policies, either expressed
   or implied, of Confident Technologies, Inc.
*/

/**
 * Confident CAPTCHA Library for PHP - API interface
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
 * @author    Chad Blomquist <cblomquist@confidenttech.com>
 * @author    John Whitlock <jwhitlock@confidenttech.com>
 * @copyright Copyright (c) 2010, Confident Technologies, Inc.
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2.0 or later
 * @license   http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version   20100621_PHP_2
 */

/**
 * Response from a Confident CAPTCHA API call
 * @package confidentcaptcha-php
 */
class CCAP_ApiResponse
{
    /**
     * HTTP status code returned by API
     *
     * Standard HTTP codes are used, as well as 0 to signify that the server
     * did not respond.
     * @var integer
     */
    var $status;

    /**
     * HTTP body returned by API
     *
     * If the {@link $status} is 200, then the body is the response from the
     * API. Otherwise, the response is the cURL error.
     * @var string
     */
    var $body;

    /**
     * Construct a CCAP_ApiResponse
     *
     * @param integer $status HTTP status code
     * @param string  $body   HTTP response body
     */
    public function CCAP_ApiResponse($status, $body)
    {
        $this->$status = $status;
        $this->$body   = $body;
    }
}


/**
 * Access functions for the Confident CAPTCHA API
 *
 * The Confident CAPTCHA API, with documentation, is found at:
 * {@link http://captcha.confidenttechnologies.com}
 * @package confidentcaptcha-php
 */
class CCAP_Api
{
    /**
     * API Customer ID (same for all sites created by your account)
     * @var string
     */
    protected var $customer_id;

    /**
     * API Site ID (unique to the a website or even webpage)
     * @var string
     */
    protected var $site_id;

    /**
     * API Username (secret associated with site)
     * @var string
     */
    protected var $api_username;

    /**
     * API Password (secret associated with site)
     * @var string
     */
    protected var $api_password;

    /**
     * Confident CAPTCHA API Server URL
     *
     * This should always be http://captcha.confidenttechnologies.com, unless
     * told differently by Confident Technologies technical support.
     * @var string
     */
    protected var $captcha_server_url;

    /**
     * Version of this library
     *
     * A version string such as 20100621_PHP_1, passed as library_version in
     * each API call.
     *
     * The first part, 20100621_, tells the Confident CAPTCHA server that
     * you expect the server to use the June 21st, 2010 version of the API.
     * There are only a few recognized values, and using an unrecognized
     * value is undefined.
     *
     * The combined 20100621_PHP is used to determine if the library is
     * supported.  For a list of supported libraries, see
     * {@link http://captcha.confidenttechnologies.com/version
     *  the version XML returned from the server}.
     * Contact Confident Technologies to add your own library to the list.
     *
     * Everything after this is ignored by the API, and can be used for
     * versioning your library.  This may be useful for diagnosing issues -
     * if a known bad library_version appears in request, we can contact the
     * customer to request them to update their library.
     * 
     * @var string
     */
    protected var $library_version;


    /**
     * Construct a CCAP_Api with API credentials
     *
     * All API credentials come from your account on
     * {@link https://login.confidenttechnologies.com}
     * , created from the
     * {@link http://confidenttechnologies.com/purchase/CAPTCHA_signup.php
     *  Confident Technologies signup page}
     *
     * @param string $customer_id        API Customer ID
     * @param string $site_id            API Site ID
     * @param string $api_username       API Username
     * @param string $api_password       API Password
     * @param string $library_version    This library's version string
     * @param string $captcha_server_url Confident CAPTCHA API Server URL
     */
    public function CCAP_Api($customer_id, $site_id, $api_username,
        $api_password, $library_version = '20100621_PHP_1',
        $captcha_server_url = 'http://captcha.confidenttechnologies.com',
        )
    {
        $this->customer_id = $customer_id;
        $this->site_id = $site_id;
        $this->api_username = $api_username;
        $this->api_password = $api_password;
        $this->library_version = $library_version;
        $this->captcha_server_url = $captcha_server_url;
    }

    /**
     * Call the Confident CAPTCHA API
     *
     * @param string  $resource        The resource to call
     * @param string  $method          The HTTP method to use
     * @param array   $params          The parameters to send
     * @param boolean $use_credentials Include API credentials in call
     * @return CCAP_ApiResponse  The response
     */
    protected function call($resource, $method = "POST", $params=null,
        $use_credentials=True
        )
    {
        $url = $this->$captcha_server_url . $resource;

        if ($params == null) {
            $params = array();
        }
        $params["library_version"] = $this->$library_version;
        if ($use_credentials) {
            $params['customer_id']  = $customer_id;
            $params['site_id']      = $site_id;
            $params['api_password'] = $api_password;
            $params['api_username'] = $api_username;
        }

        $ch = curl_init();

        if (strtolower($method) == 'post') {
            curl_setopt($ch, CURLOPT_POST, true);

            if ($params) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
        } elseif (strtolower($method) == 'get') {
            if ($params) {
                $url .= '?' . http_build_query($params);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($ch);

        if ($body === false) {
            $response = CCAP_ApiResponse(
                curl_getinfo($ch, CURLINFO_HTTP_CODE),
                curl_error($ch)
            );
        } else {
            $response = CCAP_ApiResponse(
                curl_getinfo($ch, CURLINFO_HTTP_CODE),
                $body
            );
        }

        curl_close($ch);
        return $response;
    }

    /**
     * Get the user's IP address and User Agent
     *
     * These are used by Confident Technologies to build a risk profile for
     * the CAPTCHA user.
     */
    protected function get_user_info()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            // IP from share internet
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // IP passed from proxy
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            // IP directly from caller
            $ip=$_SERVER['REMOTE_ADDR'];
        } else {
            $ip="";
        }

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            // User-Agent header
            $agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $agent = "";
        }

        return Array('ip_addr' => $ip, 'user_agent' => $agent);
    }

    /**
     * Check that API credentials and other settings are valid.
     *
     * @return CCAP_ApiResponse  body is an HTML table describing
     *  if the API credentials are valid.  If the string "api_failed='True'"
     *  appears in the response, then other calls will fail.
     */
    public function check_credentials()
    {
        return $this->call('/check_credentials', 'GET');
    }

    /**
     * Get API version XML
     */
    public function version()
    {
        return $this->call('/version', 'GET', null, false);
    }

    /**
     * Create a multiple-CAPTCHA block.
     *
     * This can be used to give the end user quick feedback if the CAPTCHA
     * attempt succeeded or failed, and generate a new visual CAPTCHA without
     * refreshing the page (JavaScript and a callback required).
     *
     * @return CCAP_ApiResponse  If success, body is block_id
     */
    public function create_block()
    {
        $params = $this->get_user_info();
        return call('/block', 'POST', $params);
    }

    /**
     * Create a visual CAPTCHA in a multiple-CAPTCHA block
     *
     * For security, the width, height, and length must define a visual
     * CAPTCHA that has a low probability of randomly guessing.  Some
     * acceptable values are 3x3 w/ length 4, or 4x4 w/ length 3.
     *
     * @param string  $block_id      Block ID returned from {@link create_block()}
     * @param string  $display_style 'flyout' or 'lightbox'
     * @param boolean $include_audio Include audio CAPTCHA (if enabled)
     * @param integer $height        Height of visual CAPTCHA in pictures
     * @param integer $width         Width of visual CAPTCHA in pictures
     * @param integer $length        Number of pictures the user must pick
     * @param integer $code_color    Color of letter code on pictures
     *
     * @return CCAP_ApiResponse  If success, status is 200 and body
     *  is partial HTML to inject into page.  If status is 410, then the end
     *  user has used up all their attempts.
     */
    public function create_visual($block_id, $display_style='flyout', 
        $include_audio=false, $height=3, $width=3, $length=4, 
        $code_color='White')
    {
        $params = Array(
            'display_style' => $display_style,
            'include_audio_form' => $include_audio,
        );

        if ($height != null && $height != '') {
            $params['height'] = $height;
        }
        if ($width != null && $width != '') {
            $params['width'] = $width;
        }
        if ($length != null && $length != '') {
            $params['captcha_length'] = $length;
        }
        if ($code_color != null && $code_color != '') {
            $params['image_code_color'] = $code_color;
        }

        return call("/block/$block_id/visual", 'POST', $params, false);
    }

    /**
     * Check a visual CAPTCHA in a multiple-CAPTCHA block
     *
     * For security, the width, height, and length must define a visual
     * CAPTCHA that has a low probability of randomly guessing.  Some
     * acceptable values are 3x3 w/ length 4, or 4x4 w/ length 3.
     *
     * @param string $block_id   Block ID returned from {@link create_block()}
     * @param string $visual_id  Visual ID in return from {@link create_visual()}
     * @param string $code       User's CAPTCHA solution
     *
     * @return CCAP_ApiResponse The body is 'True' if the solution was
     *  correct, 'False' if incorrect or unknown captcha_id
     */
    public function check_visual($block_id, $visual_id, $code)
    {
        $params = Array('code' => $code);
        $resource = "/block/$block_id/visual/$visual_id"
        return call($resource, 'POST', $params, false);
    }

    /**
     * Start an audio CAPTCHA by calling the user
     *
     * Audio CAPTCHA must be enabled for your account.  Only US numbers
     * are currently supported.
     *
     * @param string $block_id     Block ID returned from {@link create_block()}
     * @param string $phone_number User's 10-digit US phone number
     *
     * @return CCAP_ApiResponse If successful, status is 200 and
     *  body is an audio_id used to check if the call completed successfully
     */
    public function start_audio($block_id, $phone_number)
    {
        $params = Array('phone_number' => $phone_number);
        return call("/block/$block_id/audio", 'POST', $params, false);
    }

    /**
     * Check the progress of an audio CAPTCHA
     *
     * @param string $block_id Block ID returned from {@link create_block()}
     * @param string $audio_id Audio ID in return from {@link start_audio()}
     *
     * @return CCAP_ApiResponse
     */
    public function check_audio($block_id, $audio_id)
    {
        return call("/block/$block_id/audio/$audio_id", 'GET', null, false);
    }

    /**
     * Create a single CAPTCHA
     *
     * This CAPTCHA is designed to be checked at form submission
     *
     * For security, the width, height, and length must define a visual
     * CAPTCHA that has a low probability of randomly guessing.  Some
     * acceptable values are 3x3 w/ length 4, or 4x4 w/ length 3.
     *
     * @deprecated Prefer {@link create_block()} / {@link create_visual()}
     *
     * @param string  $display_style 'flyout' or 'lightbox'
     * @param boolean $include_audio Include audio CAPTCHA (if enabled for site)
     * @param integer $height        Height of visual CAPTCHA in pictures
     * @param integer $width         Width of visual CAPTCHA in pictures
     * @param integer $length        Number of pictures the user must pick
     * @param integer $code_color    Color of letter code on pictures
     *
     * @return CCAP_ApiResponse  If success, status is 200 and body
     *  is partial HTML to inject into page.  If status is 410, then the end
     *  user has used up all their attempts.
     */
    public function create_captcha($display_style='flyout',
        $include_audio=false, $height=3, $width=3, $length=4,
        $code_color='White')
    {
        $user_info = $this->get_user_info();
        $params = Array(
            'ipaddr' => $user_info['ipaddr'],
            'user_agent' => $user_info['user_agent'],
            'display_style' => $display_style,
            'height' => $height,
            'width' => $width,
            'captcha_length' => $length,
            'image_code_color' => $code_color,
        );
        return call("/captcha", 'POST', $params);
    }

    /**
     * Check a CAPTCHA instance in a multiple-CAPTCHA block
     *
     * For security, the width, height, and length must define a visual
     * CAPTCHA that has a low probability of randomly guessing.  Some
     * acceptable values are 3x3 w/ length 4, or 4x4 w/ length 3.
     *
     * NOTE - The parameter order is different than the check_captcha
     * call from earlier versions of this library.
     *
     * @deprecated Prefer {@link check_visual()}
     *
     * @param string $captcha_id CAPTCHA ID in return from {@link create_captcha()}
     * @param string $code       User's CAPTCHA solution
     *
     * @return CCAP_ApiResponse The body is 'True' if the solution was
     *  correct, 'False' if incorrect or unknown captcha_id
     */
    public function check_captcha($captcha_id, $code)
    {
        $params = Array('code' => $code);
        return call("/captcha/$captcha_id", 'POST', $params, false);
    }

    /**
     * Start a voice CAPTCHA by calling the user
     *
     * Audio CAPTCHA must be enabled for your account.  Only US numbers
     * are currently supported.
     *
     * @deprecated Prefer {@link create_block()} / {@link start_audio()}
     *
     * @param string $phone_number User's 10-digit US phone number
     *
     * @return CCAP_ApiResponse If successful, the status is 200
     *  and the body is a onekey_id used to check if the call completed
     *  successfully
     */
    public function start_onekey($phone_number)
    {
        $params = Array('phone_number' => $phone_number);
        return call("/onekey", 'POST', $params);
    }

    /**
     * Check the progress of a voice CAPTCHA
     *
     * @deprecated Prefer {@link check_audio()}
     *
     * @param string $onekey_id One Key ID in return from start_audio()
     *
     * @return CCAP_ApiResponse
     */
    public function check_onekey($onekey_id)
    {
        return call("/onekey/$onekey_id", 'POST');
    }
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: 
