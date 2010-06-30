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
    protected var $api;

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
    protected var $captcha_type = 'multiple';

    /**
     * Block ID for multiple captcha
     * @var string
     */
    protected var $block_id = NULL;

    /**
     * Visual CAPTCHA display style ('flyout' or 'lightbox')
     * @var string
     */
    protected var $display_style = 'flyout';

    /**
     * Visual CAPTCHA - include the audio CAPTCHA alternative (if enabled)
     * @var boolean
     */
    protected var $include_audio = false;

    /**
     * Visual CAPTCHA - Height in pictures
     * @var integer
     */
    protected var $height = 3;

    /**
     * Visual CAPTCHA - Width in pictures
     * @var integer
     */
    protected var $width = 3;

    /**
     * Visual CAPTCHA - Number of pictures the user has to select
     * @var integer
     */
    protected var $length = 4;

    /**
     * Visual CAPTCHA - The color of the letter code on pictures
     * @var string
     */
    protected var $code_color = 'White';


    /**
     * Construct a CCAP_Policy
     *
     * @param CCAP_Api $api      The API interface to use for calls
     */
    public function CCAP_Policy($api)
    {
        $this->$api = $api;
    }

    /**
     * Handle API failures
     *
     * @param string local_func_name Name of the CCAP_Policy function that was called
     * @param array  local_func_args Array of arguments to the CCAP_Policy function
     * @param string api_func_name Name of the CCAP_Api function that failed
     * @param CCAP_ApiResponse response The response from {@link CCAP_Api}
     * @return string HTML to inject into the page
     */
    abstract protected function on_api_fail($local_func_name, $local_func_args,
        $api_func_name, $response);

    /**
     * Create a visual CAPTCHA
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
    public function create_visual($captcha_type=NULL,
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
                $response = $api->create_block();
                if ($response->status == 200) {
                    $this->block_id = $response->body;
                } else {
                    return $this->on_api_fail('create_visual', func_get_args(),
                        'create_block', $response);
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
            $response = $api->create_visual($this->block_id,
                $this->display_style, $this->include_audio, $this->height,
                $this->width, $this->length, $this->code_color);
            if ($response->status != 200) {
                return $this->on_api_fail('create_visual', func_get_args(),
                    'create_visual', $response);
            }
        } else {
            // Create a single visual CAPTCHA
            $response = $api->create_captcha($this->display_style,
                $this->include_audio, $this->height,
                $this->width, $this->length, $this->code_color);
            if ($response->status != 200) {
                return $this->on_api_fail('create_visual', func_get_args(),
                    'create_captcha', $response);
            }
        }

        // Success
        return $this->on_create_visual_success($response);
    }

    /**
     * Handle visual CAPTCHA creation success
     *
     * Standard action is to return the response body, but other policies
     * may want to set session state or something else.
     *
     * @param CCAP_ApiResponse response The response from {@link CCAP_Api}
     * @return string HTML to inject into the page
     */
    protected function on_create_visual_success($response)
    {
        return $response->body;
    }
}

/**
 * Confident CAPTCHA policy for page development
 *
 * Most web developers start by adding Confident CAPTCHA to an existing page
 * in their development environment.  Configuration and usage errors are more
 * common during this phase.  This policy causes these errors to be noisy, so
 * that a developer can quickly fix issues without resorting to logs.
 *
 * @package confidentcaptcha-php
 */

class CCAP_DevelopmentPolicy extends CCAP_Policy
{

}

/**
 * Confident CAPTCHA policy for production, failures leave page open
 *
 * Occasionally, the call to create a Confident CAPTCHA will fail, due to
 * upstream issues (failed API credentials, maintenance downtime, etc.).
 * For a page like a contact form, it is better that some automated spam gets
 * through instead of blocking all legimate users.
 *
 * When there is an upstream issue, this policy will hide the CAPTCHA, and
 * CAPTCHA confirmation will succeed.  It requires that the server stores
 * the CAPTCHA state in a session variable or some other persistance scheme.
 *
 * @package confidentcaptcha-php
 */
class CCAP_ProductionFailOpen extends CCAP_Policy
{

}

/**
 * Confident CAPTCHA policy for production, failures leave page closed
 *
 * Occasionally, the call to create a Confident CAPTCHA will fail, due to
 * upstream issues (failed API credentials, maintenance downtime, etc.).
 * For a page like an account signup form, it is better that the page
 * remains closed to all users than to let some automated accounts be
 * created.
 *
 * When there is an upstream issue, this policy will display a message
 * that CAPTCHA creation failed, and to try again later.
 *
 * @package confidentcaptcha-php
 */
class CCAP_ProductionFailClosed extends CCAP_Policy
{

}
