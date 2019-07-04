<?php

//Function for checking parameters
function validate_parameters($request, $rules) {
    for ($i = 0; $i < count($rules); ++$i) {
        if (!isset($request[$rules[$i][0]])) {
            return false;
        }
        $item = $request[$rules[$i][0]];
        switch ($rules[$i][1]) {
            case "s":
                if (!is_string($item) or empty(trim($item))) {
                    return false;
                }
            break;
            case "i":
                if (!is_numeric($item)) {
                    return false;
                }
            break;
            case "e":
                if(!is_string($item) or !filter_var($item, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
            break;
            case "p":
                if(!is_string($item) or empty(trim($item)) or strlen($item) < 6) {
                    return false;
                }
            break;
            default:
            return false;
        }
    }
    return true;
}

?>
