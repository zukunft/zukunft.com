<?php

/**
 * remove all empty field from a json
 */
function json_clean(array $in_json): array
{
    foreach ($in_json as &$value)
    {
        if (is_array($value))
        {
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
