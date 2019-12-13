<?php
spl_autoload_register(function ($class) {
    if ($tokens = preg_split("/[\\\\]+/", $class)) {
        $classname = array_pop($tokens);
        if ($classname) {
            include_once $classname . '.php';
        }
    }
});