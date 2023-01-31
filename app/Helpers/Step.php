<?php

namespace App\Helpers;

class Step
{
    public static int $current_step = 0;
    const STEP_START = 0;
    const STEP_SEARCH = 1;

    public static function get_step() {
        return self::$current_step;
    }
    public static function set_step($param) {
        self::$current_step = $param;
    }
}
