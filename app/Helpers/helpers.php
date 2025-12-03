<?php

if (! function_exists('studly_case')) {
    function studly_case($string)
    {
        return str($string)->studly();
    }
}
