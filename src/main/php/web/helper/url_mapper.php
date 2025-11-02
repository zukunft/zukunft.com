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
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
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
        return $this->add_url_default($std_array, $usr_msg);
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

    private function map_standard_to(
        array        $std_array,
        user_message $usr_msg,
        array        $map_lst,
        string       $map_name
    ): array
    {
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
                if (array_key_exists($std_key, $map_pos)) {
                    $pos = $map_pos[$std_key];
                    $target_key = $map_lst[$pos][0];
                    if ($std_key == url_var::ACTION) {
                        $value = $this->map_std_action_to($value, $usr_msg);
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
                    $usr_msg->add_id_with_vars(msg_id::URL_MAP_MISSING, [
                        msg_id::VAR_URL_KEY => $std_key
                    ]);
                }
            } else {
                log_err($map_name . ' array had not at least two col');
            }
        }
        return $url_array;
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

    private function map_std_value_to(
        string       $std_value,
        user_message $usr_msg,
        array        $map_lst,
        string       $map_name
    ): string
    {
        if (array_key_exists($std_value, $map_lst)) {
            $target_value = $map_lst[$std_value];
        } else {
            $usr_msg->add_id_with_vars(msg_id::URL_MAP_MISSING, [
                msg_id::VAR_URL_KEY => $std_value,
                msg_id::VAR_NAME => $map_name
            ]);
            $target_value = $std_value;
        }
        return $target_value;
    }

    private function map_url_to_standard(
        array        $url_array,
        user_message $usr_msg,
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
                        $usr_msg->add_id_with_vars(msg_id::URL_KEY_MISSING, [
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
                $usr_msg->add_id_with_vars(msg_id::URL_MAP_MISSING, [
                    msg_id::VAR_URL_KEY => $key
                ]);
            }
        }
        return $std_array;
    }

    private     function add_url_default(
        array        $url_array,
        user_message $usr_msg
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
                            $usr_msg->add_id_with_vars(msg_id::URL_KEY_MISSING, [
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


