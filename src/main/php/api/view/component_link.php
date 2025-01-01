<?php

/*

    api/view/component_link.php - the minimal component link object
    ---------------------------


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

namespace api\view;

include_once API_SANDBOX_PATH . 'sandbox.php';
include_once SHARED_PATH . 'json_fields.php';

use api\sandbox\sandbox as sandbox_api;
use api\view\view as view_api;
use api\component\component as component_api;
use shared\json_fields;
use JsonSerializable;

class component_link extends sandbox_api implements JsonSerializable
{

    /*
     * object vars
     */

    // the triple components
    // TODO api object vars can be public!
    private view_api $msk;
    private component_api $cmp;
    private int $pos;
    private int $pos_type;
    private ?int $style;


    /*
     * construct and map
     */

    function __construct(int $id = 0)
    {
        parent::__construct($id);
        $this->msk = new view_api();
        $this->cmp = new component_api();
        $this->pos = 0;
        $this->pos_type = 0;
        $this->style = 0;
    }


    /*
     * set and get
     */

    function set(view_api $msk, component_api $cmp, int $pos): void
    {
        if ($msk->id() > 0) {
            $this->set_view($msk);
        }
        if ($cmp->id() > 0) {
            $this->set_component($cmp);
        }
        if ($pos > 0) {
            $this->set_pos($pos);
        }
    }

    function set_view(view_api $msk): void
    {
        $this->msk = $msk;
    }

    function set_component(component_api $cmp): void
    {
        $this->cmp = $cmp;
    }

    function set_pos(int $pos): void
    {
        $this->pos = $pos;
    }

    function set_pos_type(int $pos_type): void
    {
        $this->pos_type = $pos_type;
    }

    function set_style(int $style): void
    {
        $this->style = $style;
    }

    function view(): view_api
    {
        return $this->msk;
    }

    function component(): component_api
    {
        return $this->cmp;
    }

    function pos(): int
    {
        return $this->pos;
    }

    function pos_type(): int
    {
        return $this->pos_type;
    }

    function style(): int
    {
        return $this->style;
    }


    /*
     * interface
     */

    /**
     * @return string the json api message as a text string
     */
    function get_json(): string
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * @return array with the sandbox vars without empty values that are not needed
     */
    function jsonSerialize(): array
    {
        $vars = parent::jsonSerialize();
        $vars = array_merge($vars, get_object_vars($this->component()));
        $vars[json_fields::LINK_ID] = $this->id();
        $vars[json_fields::POS] = $this->pos();
        $vars[json_fields::POS_TYPE] = $this->pos_type();
        if ($this->style() != 0) {
            $vars[json_fields::STYLE] = $this->style();
        }
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

}
