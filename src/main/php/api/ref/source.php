<?php

/*

    api/ref/source.php - the source object for the frontend API
    ------------------


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

namespace api\ref;

use api\sandbox\sandbox_typed as sandbox_typed_api;

include_once API_SANDBOX_PATH . 'sandbox_typed.php';

class source extends sandbox_typed_api
{

    /*
     * const for system testing
     */

    // persevered source names for unit and integration tests (TN means TEST NAME)
    // TN_* is the name of the predefined source used for testing
    // TI_* is the id after adding the predefined sources
    // TC_* is the code_id for testing
    // TD_* is the description  of the predefined source
    // TU_* is the URL of the predefined source
    const TI_READ = 1;
    const TN_READ = 'The International System of Units';
    const TD_READ = 'Bureau International des Poids et Mesures - The intergovernmental organization through which Member States act together on matters related to measurement science and measurement standards';
    const TU_READ = 'https://www.bipm.org/documents/20126/41483022/SI-Brochure-9.pdf';
    const TC_READ = 'BIPM';
    const TN_MATH = 'Mathematical constant';
    const TN_READ_REF = 'wikidata';
    const TI_READ_REF = 2;
    const TN_ADD = 'System Test Source';
    const TD_ADD = 'System Test Source Description';
    const TU_ADD = 'https://www.zukunft.com/';
    const TN_ADD_VIA_FUNC = 'System Test Source added via sql function';
    const TN_ADD_VIA_SQL = 'System Test Source added via sql insert';
    const TN_RENAMED = 'System Test Source Renamed';

    // must be the same as in /resource/api/source/source_put.json
    const TN_ADD_API = 'System Test Source API added';
    const TD_ADD_API = 'System Test Source Description API';
    const TU_ADD_API = 'https://api.zukunft.com/';
    const TN_UPD_API = 'System Test Source API renamed';
    const TD_UPD_API = 'System Test Source Description renamed API';
    const TN_IPCC_AR6_SYNTHESIS = 'IPCC AR6 Synthesis Report: Climate Change 2022';
    const TU_IPCC_AR6_SYNTHESIS = 'https://www.ipcc.ch/report/sixth-assessment-report-cycle/';

    // parameters used for unit and integration tests
    const TEST_URL_CHANGED = 'https://api.zukunft.com/';
    const TEST_DESCRIPTION_CHANGED = 'System Test Source Description Changed';

    // array of source names that used for testing and remove them after the test
    const RESERVED_NAMES = array(
        self::TN_READ_REF, // the source for all data imported from wikidata that does not yet have a source defined in wikidata
        self::TN_READ,
        self::TN_ADD,
        self::TN_ADD_API,
        self::TN_RENAMED
    );

    // array of source names that used for db read testing and that should not be renamed
    const FIXED_NAMES = array(
        self::TN_READ
    );

    const TEST_SOURCES = array(
        self::TN_ADD,
        self::TN_ADD_API,
        self::TN_RENAMED
    );


    /*
     * object vars
     */

    public ?string $url;

}
