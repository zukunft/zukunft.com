<?php

/*

    web/sandbox/sandbox.php - extends the frontend db object superclass for user sandbox functions such as share type
    -----------------------


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html\sandbox;

include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once API_SANDBOX_PATH . 'sandbox_named.php';

use html\sandbox\db_object as db_object_dsp;
use html\html_base;
use html\user\user as user_dsp;

class sandbox extends db_object_dsp
{

    // for preloaded types just include the id on the sandbox object
    public ?int $share_id = null;      // id for public, personal, group or private
    public ?int $protection_id = null; // id for no, user, admin or full protection

    protected ?user_dsp $owner = null;


    /**
     * @param string $form_name the name of the html form
     * @return string the html code to select the share type
     */
    protected function share_type_selector(string $form_name): string
    {
        global $usr;
        global $html_share_types;
        $used_share_id = $this->share_id;
        if ($used_share_id == null) {
            $used_share_id = $html_share_types->default_id();
        }
        if ($usr == $this->owner or $this->owner == null) {
            return $html_share_types->selector($form_name, $used_share_id, 'share', html_base::COL_SM_4, 'share:');
        } else {
            return '';
        }
    }

    /**
     * @param string $form_name the name of the html form
     * @return string the html code to select the share type
     */
    protected function protection_type_selector(string $form_name): string
    {
        global $usr;
        global $html_protection_types;
        $used_protection_id = $this->protection_id;
        if ($used_protection_id == null) {
            $used_protection_id = $html_protection_types->default_id();
        }
        if ($usr == $this->owner or $this->owner == null) {
            return $html_protection_types->selector($form_name, $used_protection_id, 'protection', html_base::COL_SM_4, 'protection:');
        } else {
            return '';
        }
    }

}


