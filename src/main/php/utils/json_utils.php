<?php

/**
 * remove all empty field from a json
 */
function json_clean(array $in_json): array
{
    foreach ($in_json as &$value) {
        if (is_array($value)) {
            $value = json_clean($value);
        }
    }

    return array_filter($in_json);

}

/**
 * check if the import JSON array matches the export JSON array
 * @param array|null $json_in a JSON array that is can contain empty field
 * @param array|null $json_ex a JSON that can have other empty field than $json_in and in a different order
 * @return bool true if the JSON have the same meaning
 */
function json_is_similar(?array $json_in, ?array $json_ex): bool
{
    // this is for compare, so a null value is considered to be the same as an empty array
    if ($json_in == null) {
        $json_in = [];
    }
    if ($json_ex == null) {
        $json_ex = [];
    }
    // remove empty JSON fields
    $json_in_clean = json_encode(json_clean($json_in));
    $json_ex_clean = json_encode(json_clean($json_ex));
    // compare the JSON object not the array to ignore the order
    return json_decode($json_in_clean) == json_decode($json_ex_clean);

}

/**
 * get the diff of a multidimensional array where the sub item can ba matched by a key
 *
 * @param array $needle the smaller array that is expected to be part of the haystack array
 * @param array $haystack the bigger array that is expected to contain all items from the needle
 * @param string $key_name the key name to find the matching item in the haystack
 * @return array an empty array if all item and sub items from the needle are in the haystack
 */
function array_recursive_diff(array $needle, array $haystack, string $key_name = 'id'): array
{
    $result = array();

    //
    foreach ($needle as $key => $value) {
        if (array_key_exists($key, $haystack)) {
            if (is_array($value)) {
                // find the matching haystack entry if a key name is set
                $key_value = '';
                $haystack_key = -1;
                // loop over the inner needle items
                foreach ($value as $inner_key => $inner_value) {
                    if (is_array($inner_value)) {
                        if ($key_name != '') {
                            $key_value = $inner_value[$key_name];
                        }
                    } else {
                        if ($inner_value != $haystack[$key][$inner_key]) {
                            $result[$inner_key] = $inner_value;
                        }
                    }
                    // find the entry in the haystack that matches the key value
                    if ($key_value != '') {
                        foreach ($haystack[$key] as $search_key => $inner_haystack) {
                            if ($inner_haystack[$key_name] == $key_value) {
                                $haystack_key = $search_key;
                            }
                        }
                    }
                    if ($haystack_key >= 0) {
                        $inner_haystack = array_recursive_diff($inner_value, $haystack[$key][$haystack_key]);
                        if (count($inner_haystack)) {
                            $result[$key] = $inner_haystack;
                        }
                    }
                }
                if ($haystack_key < 0) {
                    $inner_haystack = array_recursive_diff($value, $haystack[$key]);
                    if (count($inner_haystack)) {
                        $result[$key] = $inner_haystack;
                    }
                }
            } else {
                if ($value != $haystack[$key]) {
                    $result[$key] = $value;
                }
            }
        } else {
            $result[$key] = $value;
        }
    }
    return $result;
}

/**
 * check if the import JSON array matches the export JSON array
 * @param array|null $json_needle a JSON array that is can contain empty field
 * @param array|null $json_haystack a JSON that can have additional fields than $json_needle and in a different order
 * @return bool true if the JSON have the same meaning
 */
function json_contains(?array $json_needle, ?array $json_haystack): bool
{
    // this is for compare, so a null value is considered to be the same as an empty array
    if ($json_needle == null) {
        $json_needle = [];
    }
    if ($json_haystack == null) {
        $json_haystack = [];
    }
    // remove empty JSON fields
    $json_needle_clean = json_clean($json_needle);
    $json_haystack_clean = json_clean($json_haystack);
    // compare the JSON object not the array to ignore the order
    $diff = array_recursive_diff($json_needle_clean, $json_haystack_clean);
    if (count($diff) == 0) {
        return true;
    } else {
        return false;
    }

}
