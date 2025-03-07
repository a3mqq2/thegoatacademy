<?php 


function get_area_name() {
   if (auth()->check()) {
       return str_replace("-", "_", explode('/', request()->url())[3]);
   }
}


if (!function_exists('greetUser')) {
   function greetUser($name) {
       return "Hello, {$name}!";
   }
}