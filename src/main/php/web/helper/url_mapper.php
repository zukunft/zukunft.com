<?php

/*

    web/helper/url_mapper.php - create human-readable or pod exchangeable urls
    -------------------------


    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class url_mapper
{

    /**
     * get the standard url array from all allowed url formats
     * the url string can be the short form or in human-readable format or in pod independent format
     * @param array $url_array in any possible format of the array keys
     * @param user_message $usr_msg to enrich with potential errors
     * @return array with the standard keys
     */
    function url_to_standard(array $url_array, user_message $usr_msg): array
    {
        // detect the url format and get the view id or code id
        if (array_key_exists(url_var::MASK_HUMAN, $url_array)) {
            $std_array = $this->human_url_to_standard($url_array, $usr_msg);
        } elseif (array_key_exists(url_var::MASK_POD, $url_array)) {
            $std_array = $this->pod_url_to_standard($url_array, $usr_msg);
        } else {
            $std_array = $url_array;
        }
        $std_array = $this->add_url_default($std_array, $usr_msg);
        // TODO Prio 2 review
        // the standard url always carries the numeric view id; an input that used the view code id
        // (e.g. a human url with mask_id=word_add) is converted to the numeric id so the rendered
        // standard urls and the '9'-prefixed back targets built from it use the numeric id, while
        // standard_url_to_human maps it back to the code id for display. a numeric mask is returned
        // unchanged and keeps its type so the action routing's strict comparisons still match
        if (array_key_exists(url_var::MASK, $std_array)) {
            $std_array[url_var::MASK] = $this->map_mask_to_std($std_array[url_var::MASK]);
        }
        return $std_array;
    }

    private function human_url_to_standard(array $url_array, user_message $usr_msg): array
    {
        return $this->map_url_to_standard(
            $url_array,
            $usr_msg,
            url_var::HUMAN_TO_STD,
            'url_var::HUMAN_TO_STD'
        );
    }

    private function pod_url_to_standard(array $url_array, user_message $usr_msg): array
    {
        return $this->map_url_to_standard(
            $url_array,
            $usr_msg,
            url_var::POD_TO_STD,
            'url_var::POD_TO_STD'
        );
    }

    function standard_url_to_human(array $url_array, user_message $usr_msg): string
    {
        return $this->array_to_url($this->map_standard_to(
            $url_array,
            $usr_msg,
            url_var::HUMAN_TO_STD,
            'url_var::HUMAN_TO_STD'
        ));
    }

    function standard_url_to_pod(array $url_array, user_message $usr_msg): string
    {
        return $this->array_to_url($this->map_standard_to(
            $url_array,
            $usr_msg,
            url_var::POD_TO_STD,
            'url_var::POD_TO_STD'
        ));
    }

    /**
     * // TODO Prio 2 review
     * the human-readable url as a pretty json object: the normal url vars become human-keyed
     * top-level fields, the '8'-prefixed pre values are grouped under 'original_data' and the
     * '9'-prefixed back targets under 'back' (each prefix stripped and the rest human-keyed)
     *
     * @param array $url_array the standard url (flat [key => value]) including the 8- and 9-prefixed vars
     * @param user_message $usr_msg enriched with a message for each url key that has no human mapping
     * @return string the pretty-printed json of the human-readable url
     */
    function human_url_to_json(array $url_array, user_message $usr_msg): string
    {
        $main = [];
        $original = [];
        $back = [];
        foreach ($url_array as $key => $val) {
            if (str_starts_with($key, url_var::PRE)) {
                $original[substr($key, strlen(url_var::PRE))] = $val;
            } elseif (str_starts_with($key, url_var::BACK)) {
                $back[substr($key, strlen(url_var::BACK))] = $val;
            } else {
                $main[$key] = $val;
            }
        }
        $json = $this->to_human_assoc($main, $usr_msg);
        if (!empty($original)) {
            $json[json_fields::URL_ORIGINAL_DATA] = $this->to_human_assoc($original, $usr_msg);
        }
        if (!empty($back)) {
            $json[json_fields::URL_PART_BACK] = $this->to_human_assoc($back, $usr_msg);
        }
        return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * convert a flat standard url array to a human-keyed associative array (the view id becomes the
     * code id and the step / action values their human text), reusing the standard -> human mapping
     *
     * @param array $flat the standard url part as a flat [key => value] map
     * @param user_message $usr_msg enriched with a message for each key that has no human mapping
     * @return array the human-keyed [human_key => value] map
     */
    private function to_human_assoc(array $flat, user_message $usr_msg): array
    {
        $assoc = [];
        $rows = $this->map_standard_to($flat, $usr_msg, url_var::HUMAN_TO_STD, 'url_var::HUMAN_TO_STD');
        foreach ($rows as $row) {
            $assoc[$row[0]] = $row[1];
        }
        return $assoc;
    }

    private function map_standard_to(
        array        $std_array,
        user_message $msg,
        array        $map_lst,
        string       $map_name
    ): array
    {
        // accept a flat [key => value] url array (as produced by url_to_standard) as well as the
        // [key, value] row format used internally, so the caller does not need to convert first
        $std_array = $this->to_row_format($std_array);
        $url_array = [];
        $map_keys = [];
        foreach ($map_lst as $map) {
            if (array_key_exists(1, $map)) {
                $map_keys[] = $map[1];
            } else {
                log_err('url map array must have at leat two col');
            }
        }
        $map_pos = array_flip($map_keys);
        foreach ($std_array as $std) {
            if (array_key_exists(0, $std) and array_key_exists(1, $std)) {
                $std_key = $std[0];
                $value = $std[1];
                // an '8'-prefixed original value or a '9'-prefixed back target carries a normal url key
                // after the prefix char; split off the prefix so the base key is mapped (and the action /
                // step / mask value converted) and re-apply the prefix to the human key, so the human url
                // shows e.g. 8name / 9mask_id instead of reporting the prefixed key as missing
                $prefix = '';
                $base_key = $std_key;
                if (str_starts_with($std_key, url_var::PRE)) {
                    $prefix = url_var::PRE;
                    $base_key = substr($std_key, strlen(url_var::PRE));
                } elseif (str_starts_with($std_key, url_var::BACK)) {
                    $prefix = url_var::BACK;
                    $base_key = substr($std_key, strlen(url_var::BACK));
                }
                if (array_key_exists($base_key, $map_pos)) {
                    $pos = $map_pos[$base_key];
                    $target_key = $prefix . $map_lst[$pos][0];
                    if ($base_key == url_var::ACTION) {
                        $value = $this->map_std_action_to($value, $msg);
                    }
                    if ($base_key == url_var::STEP) {
                        $value = $this->map_std_step_to($value, $msg);
                    }
                    if ($base_key == url_var::MASK) {
                        $value = $this->map_std_mask_to($value);
                    }
                    if (array_key_exists(2, $std)) {
                        if (array_key_exists(3, $std)) {
                            $url_array[] = [$target_key, $value, $std[2], $std[3]];
                        } else {
                            $url_array[] = [$target_key, $value, $std[2]];
                        }
                    } else {
                        $url_array[] = [$target_key, $value];
                    }
                } else {
                    $msg->add(msg_id::URL_MAP_MISSING, [
                        msg_id::VAR_URL_KEY => $std_key
                    ]);
                }
            } else {
                log_err($map_name . ' array had not at least two col');
            }
        }
        return $url_array;
    }

    /**
     * normalize a url array to the internal [key, value] row format:
     * a flat [key => value] url array (as produced by url_to_standard) is converted, an array that is
     * already in the row format (its first entry is a [key, value] array) is returned unchanged
     *
     * @param array $url_array the url either as a flat [key => value] map or as [key, value] rows
     * @return array the url as [key, value] rows
     */
    private function to_row_format(array $url_array): array
    {
        $rows = $url_array;
        if (!array_key_exists(0, $url_array) or !is_array($url_array[0])) {
            $rows = [];
            foreach ($url_array as $key => $val) {
                $rows[] = [$key, (string)$val];
            }
        }
        return $rows;
    }

    /**
     * convert a standard numeric view id to the view code id for the human / pod url, using the loaded
     * view cache (global $ui_sys); a view id that is not in the cache is left as the numeric id so the
     * url still works
     *
     * @param string $std_value the numeric view id of the standard url
     * @return string the view code id e.g. 'word_add', or the unchanged numeric id if not in the cache
     */
    private function map_std_mask_to(string $std_value): string
    {
        $result = $std_value;
        global $ui_sys;
        if (is_numeric($std_value) and $ui_sys?->typ_lst_cache != null) {
            $msk = $ui_sys->typ_lst_cache->get_view_by_id((int)$std_value);
            if ($msk != null and $msk->code_id != '') {
                $result = $msk->code_id;
            }
        }
        return $result;
    }

    /**
     * convert a view code id back to the standard numeric view id, using the loaded view cache
     * (global $ui_sys); this is the inverse of map_std_mask_to so a url that came in with the code id
     * (e.g. mask_id=word_add) is stored with the numeric id in the standard url. a value that is
     * already numeric or not a known code id is left unchanged so the url still works
     *
     * @param int|string $value the view mask value of the standard url, a code id or a numeric id
     * @return int|string the numeric view id e.g. 2, or the unchanged value if already numeric or not a
     *                    known code id; a numeric value keeps its original type so strict comparisons match
     */
    private function map_mask_to_std(int|string $value): int|string
    {
        if (is_numeric($value)) {
            return $value;
        }
        global $ui_sys;
        if ($ui_sys?->typ_lst_cache != null) {
            $msk = $ui_sys->typ_lst_cache->get_view($value);
            if ($msk != null and $msk->id() != 0) {
                return $msk->id();
            }
        }
        return $value;
    }

    private function map_human_action_to_std(
        string       $std_value,
        user_message $usr_msg
    ): string
    {
        return $this->map_value_to_std(
            $std_value,
            $usr_msg,
            url_var::HUMAN_TO_STD_ACTIONS_VAL,
            'url_var::HUMAN_TO_STD_ACTIONS_VAL'
        );
    }

    private function map_human_step_to_std(
        string       $std_value,
        user_message $usr_msg
    ): string
    {
        return $this->map_value_to_std(
            $std_value,
            $usr_msg,
            url_var::HUMAN_TO_STD_STEP_VAL,
            'url_var::HUMAN_TO_STD_STEP_VAL'
        );
    }

    private function map_std_action_to(
        string       $std_value,
        user_message $usr_msg
    ): string
    {
        return $this->map_std_value_to(
            $std_value,
            $usr_msg,
            url_var::HUMAN_TO_STD_ACTIONS_VAL,
            'url_var::HUMAN_TO_STD_ACTIONS_VAL'
        );
    }

    private function map_std_step_to(
        string       $std_value,
        user_message $usr_msg
    ): string
    {
        return $this->map_std_value_to(
            $std_value,
            $usr_msg,
            url_var::HUMAN_TO_STD_STEP_VAL,
            'url_var::HUMAN_TO_STD_STEP_VAL'
        );
    }

    private function map_value_to_std(
        string       $non_std_value,
        user_message $usr_msg,
        array        $map_lst,
        string       $map_name
    ): string
    {
        return $this->map_std_value_to($non_std_value, $usr_msg, array_flip($map_lst), $map_name . '_REVERSE');
    }

    private function map_std_value_to(
        string       $std_value,
        user_message $msg,
        array        $map_lst,
        string       $map_name
    ): string
    {
        if (array_key_exists($std_value, $map_lst)) {
            $target_value = $map_lst[$std_value];
        } else {
            $msg->add(msg_id::URL_MAP_MISSING, [
                msg_id::VAR_URL_KEY => $std_value,
                msg_id::VAR_NAME => $map_name
            ]);
            $target_value = $std_value;
        }
        return $target_value;
    }

    private function map_url_to_standard(
        array        $url_array,
        user_message $msg,
        array        $map_lst,
        string       $map_name
    ): array
    {
        $std_array = [];
        foreach ($map_lst as $map) {
            if (array_key_exists(3, $map)) {
                if ($map[3]) {
                    if (array_key_exists($map[0], $url_array)) {
                        $std_array[$map[1]] = $url_array[$map[0]] ?? $map[2];
                    } else {
                        $msg->add(msg_id::URL_KEY_MISSING, [
                            msg_id::VAR_URL_KEY => $map[0]
                        ]);
                    }
                } else {
                    if (array_key_exists($map[0], $url_array)) {
                        $std_array[$map[1]] = $url_array[$map[0]] ?? $map[2];
                    }
                }
            } elseif (array_key_exists(2, $map)) {
                if (array_key_exists($map[0], $url_array)) {
                    $std_array[$map[1]] = $url_array[$map[0]] ?? $map[2];
                }
            } elseif (array_key_exists(1, $map)) {
                if (array_key_exists($map[0], $url_array)) {
                    $std_array[$map[1]] = $url_array[$map[0]];
                }
            } else {
                log_err($map_name . ' array had not at least two col');
            }
        }
        // detect missing mappings
        if (count($std_array) < count($url_array)) {
            $diff = array_diff($url_array, $std_array);
            foreach ($diff as $key => $val) {
                $msg->add(msg_id::URL_MAP_MISSING, [
                    msg_id::VAR_URL_KEY => $key
                ]);
            }
        }
        // map the values
        $key = url_var::ACTION;
        if (array_key_exists($key, $std_array)) {
            $std_array[$key] = $this->map_human_action_to_std($std_array[$key], $msg);
        }
        $key = url_var::STEP;
        if (array_key_exists($key, $std_array)) {
            $std_array[$key] = $this->map_human_step_to_std($std_array[$key], $msg);
        }
        return $std_array;
    }

    private function add_url_default(
        array        $url_array,
        user_message $msg
    ): array
    {
        $std_array = [];
        $map_keys = [];
        $map_lst = url_var::STD_DEFAULT;
        foreach ($map_lst as $map) {
            if (array_key_exists(0, $map)) {
                $map_keys[] = $map[0];
            } else {
                log_err('url map array must have at leat one col');
            }
        }
        $map_pos = array_flip($map_keys);
        foreach ($url_array as $key => $val) {
            if (in_array($key, $map_keys)) {
                $pos = $map_pos[$key];
                $map = $map_lst[$pos];
                if (array_key_exists(2, $map)) {
                    if ($map[2]) {
                        if (array_key_exists($key, $url_array)) {
                            $std_array[$key] = $val ?? $map[1];
                        } else {
                            $msg->add(msg_id::URL_KEY_MISSING, [
                                msg_id::VAR_URL_KEY => $key
                            ]);
                        }
                    } else {
                        if (array_key_exists($key, $url_array)) {
                            $std_array[$key] = $val ?? $map[1];
                        }
                    }
                } elseif (array_key_exists(1, $map)) {
                    if (array_key_exists($key, $url_array)) {
                        $std_array[$key] = $val ?? $map[1];
                    }
                } else {
                    $std_array[$key] = $val;
                }
            } else {
                $std_array[$key] = $val;
            }
        }
        // add missing default values
        foreach ($map_lst as $map) {
            if (array_key_exists(0, $map) and array_key_exists(1, $map)) {
                if (!array_key_exists($map[0], $std_array)) {
                    $std_array[$map[0]] = $map[1];
                } else {
                    $value = $std_array[$map[0]];
                    if ($value == '') {
                        $std_array[$map[0]] = $map[1];
                    }
                }
            }
        }
        return $std_array;
    }

    function name_to_human(string $std_name, user_message $msg): string
    {
        $lib = new library();
        $human_name = $std_name;
        $map = $lib->array_get_first_two_col(url_var::HUMAN_TO_STD);
        $keys = array_flip($map);
        if (array_key_exists($std_name, $keys)) {
            $human_name = $keys[$std_name];
        } else {
            $msg->add(msg_id::URL_KEY_MISSING, [
                msg_id::VAR_URL_KEY => $std_name
            ]);
        }
        return $human_name;
    }

    function array_to_url(array $url_array): string
    {
        $url = '';
        foreach ($url_array as $url_row) {
            if (array_key_exists(0, $url_row) and array_key_exists(1, $url_row)) {
                $name = $url_row[0];
                $par = $url_row[1];
                if (array_key_exists(2, $url_row)) {
                    $last = $url_row[2];
                    $url .= $this->url_par($name, $par, $last);
                } else {
                    $url .= $this->url_par($name, $par);
                }
            } else {
                log_err('url map array must have at leat two col');
            }
        }
        return $url;
    }

    private function url_par(string $name, ?string $par, bool $last = false): string
    {
        if ($par == null) {
            return '';
        } else {
            if ($last) {
                return $name . url_var::EQ . urlencode($par);
            } else {
                return $name . url_var::EQ . urlencode($par) . url_var::ADD;
            }
        }
    }

}


