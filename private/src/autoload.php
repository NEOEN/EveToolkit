<?php

spl_autoload_register(
    function ($className) {
        if (strpos($className, 'projects') === 0) {
            $filePath = PRIVATE_PATH.str_replace('\\', DIRECTORY_SEPARATOR, $className).'.php';
        } elseif (stripos($className, 'twig') === 0) {
            $filePath = VENDOR_PATH.'Twig-2.2.0'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';
        } else {
            $filePath = SRC_PATH.str_replace('\\', DIRECTORY_SEPARATOR, $className).'.php';
        }
        if (file_exists($filePath)) {
            require_once($filePath);

            return true;
        }

        return false;

    },
    true,
    true
);