<?php

/*

    model/value/value_object_select.php - just to select the best fitting class for a value
    -----------------------------------


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

namespace cfg\value;

include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_VALUE_PATH . 'value_time.php';
include_once MODEL_VALUE_PATH . 'value_text.php';
include_once MODEL_VALUE_PATH . 'value_geo.php';
include_once MODEL_GROUP_PATH . 'group.php';
include_once MODEL_USER_PATH . 'user.php';

use cfg\group\group;
use cfg\user\user;
use DateTime;

class value_obj
{

    /**
     * get the best fitting value object for the given value
     * @param user $usr the user who requested to see this value
     * @param float|DateTime|string|null $val the value used to select the best fitting value object
     * @param group|null $grp the phrases for unique identification of this value
     */
    function get(
        user                       $usr,
        float|DateTime|string|null $val,
        ?group                     $grp = null
    ): value|value_time|value_text|value_geo
    {
        if (is_string($val)) {
            if ($this->is_geo($val)) {
                return new value_geo($usr, $val, $grp);
            } else {
                return new value_text($usr, $val, $grp);
            }
        } elseif (is_a($val, 'DateTime')) {
            return new value_time($usr, $val, $grp);
        } else {
            return new value($usr, $val, $grp);
        }
    }

    private function is_geo(string $val): bool
    {
        $result = false;
        if (str_contains($val, ',')) {
            $parts = explode(',', $val);
            if (count($parts) == 2) {
                if (is_numeric(trim($parts[0]))
                    and is_numeric(trim($parts[1]))) {
                    $result = true;
                }
            }
        }
        return $result;
    }

}