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

if (!function_exists('formatLibyanPhone')) {
    function formatLibyanPhone($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '00')) {
            $phone = substr($phone, 2);
        }
        if (str_starts_with($phone, '218')) {
            return $phone;
        }
        if (str_starts_with($phone, '0')) {
            return '218' . substr($phone, 1);
        }
        return '218' . $phone;
    }
}
