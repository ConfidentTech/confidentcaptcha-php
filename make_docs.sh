#!/bin/sh
# Make HTML documentation
# Requires phpDocumeter 1.4 and PHP 5.x with tokenizer support
CMD="phpdoc"
CMD="$CMD --output HTML:frames:earthli"
CMD="$CMD --title \"Confident CAPTCHA PHP Library\""
CMD="$CMD --directory confidentcaptcha"
CMD="$CMD --target docs"
CMD="$CMD --ignore captchalib.php"
CMD="$CMD --sourcecode on"
CMD="$CMD --undocumentedelements on"
$CMD
