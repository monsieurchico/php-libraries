<?php

class UnserializeException extends \ErrorException
{
}

function safe_unserialize($text)
{
    $result = null;
    /**
     * Unserialize process will raise a E_NOTICE on fail.
     * We convert the notice to an exception to be catched.
     */
    set_error_handler(function ($severity, $message, $filename, $lineno) {
        if (error_reporting() && E_NOTICE === $severity  && false !== strpos($message, "unserialize")) {
            throw new UnserializeException($message, 0, $severity, $filename, $lineno);
        }
        return;
    });

    try {
        $result = unserialize($text);
    } catch(\UnserializeException $e) {
        $fixed_serialized_data = preg_replace_callback('!s:(\d+):"(.*?)";!', function($match) {
            return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
        }, $text);
        $result = unserialize($fixed_serialized_data);
    }
    restore_exception_handler();

    return $result;
}
