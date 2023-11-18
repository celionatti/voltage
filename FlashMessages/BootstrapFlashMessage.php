<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Voltage\FlashMessages;

/**
 * ==============================================
 * ==================           =================
 * FlashMessage Class
 * ==================           =================
 * ==============================================
 */

class BootstrapFlashMessage extends FlashMessage
{
    public static function alert()
    {
        $message = self::getAndClearMessage();

        if ($message) {
            $messageContent = $message['message'];
            $messageType = $message['type'];
            $messageAttributes = $message['attributes'];

            // Render and display the message with its attributes
            echo FlashMessage::render($messageContent, $messageType, "alert-{$messageType} alert-dismissible fade show mt-5 mx-2 shadow-lg text-uppercase text-center position-fixed position-absolute top-0 start-50 translate-middle", $messageAttributes);
        }
    }
}