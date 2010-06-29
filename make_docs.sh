#!/bin/sh
# Make HTML documentation
# Requires phpDocumeter 1.4 and PHP 5.x with tokenizer support
phpdoc -o HTML:frames:earthli -ti "Confident CAPTCHA PHP Library" -d confidentcaptcha -t docs
