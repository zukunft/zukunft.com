<?php

/*

    shared/sources.php - sources used by the system for testing
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

namespace shared;

class sources
{

    // references used by the system for testing
    // persevered reference names for unit and integration tests
    // * is the name of the predefined source used for testing
    // *_ID the fixed database due to the initial setup
    // *_CODE is the code_id for testing
    // *_COM is the tooltip/description of the link to the external reference
    // *_URL is the url overwrite for this reference

    // persevered source names for unit and integration tests (TN means TEST NAME)
    // TN_* is the name of the predefined source used for testing
    // TI_* is the id after adding the predefined sources
    // TC_* is the code_id for testing
    // TD_* is the description  of the predefined source
    // TU_* is the URL of the predefined source
    const SIB_ID = 1;
    const SIB = 'The International System of Units';
    const SIB_COM = 'Bureau International des Poids et Mesures - The intergovernmental organization through which Member States act together on matters related to measurement science and measurement standards';
    const SIB_URL = 'https://www.bipm.org/documents/20126/41483022/SI-Brochure-9.pdf';
    const SIB_CODE = 'BIPM';
    const MATH_CONST = 'Mathematical constant';
    const WIKIDATA = 'wikidata';
    const WIKIDATA_ID = 2;
    const SYSTEM_TEST_ADD = 'System Test Source';
    const SYSTEM_TEST_ADD_COM = 'System Test Source Description';
    const SYSTEM_TEST_ADD_URL = 'https://www.zukunft.com/';
    const SYSTEM_TEST_ADD_VIA_FUNC = 'System Test Source added via sql function';
    const SYSTEM_TEST_ADD_VIA_SQL = 'System Test Source added via sql insert';
    const SYSTEM_TEST_RENAMED = 'System Test Source Renamed';

    // must be the same as in /resource/api/source/source_put.json
    const SYSTEM_TEST_ADD_API = 'System Test Source API added';
    const SYSTEM_TEST_ADD_API_COM = 'System Test Source Description API';
    const SYSTEM_TEST_ADD_API_URL = 'https://api.zukunft.com/';
    const SYSTEM_TEST_UPD_API = 'System Test Source API renamed';
    const SYSTEM_TEST_UPD_API_COM = 'System Test Source Description renamed API';
    const IPCC_AR6_SYNTHESIS = 'IPCC AR6 Synthesis Report: Climate Change 2022';
    const IPCC_AR6_SYNTHESIS_URL = 'https://www.ipcc.ch/report/sixth-assessment-report-cycle/';

    // parameters used for unit and integration tests
    const TEST_URL_CHANGED = 'https://api.zukunft.com/';
    const TEST_DESCRIPTION_CHANGED = 'System Test Source Description Changed';

    // array of source names that used for testing and remove them after the test
    const RESERVED_NAMES = array(
        self::WIKIDATA, // the source for all data imported from wikidata that does not yet have a source defined in wikidata
        self::SIB,
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_ADD_API,
        self::SYSTEM_TEST_RENAMED
    );

    // array of source names that used for db read testing and that should not be renamed
    const FIXED_NAMES = array(
        self::SIB
    );

    const TEST_SOURCES = array(
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_ADD_API,
        self::SYSTEM_TEST_RENAMED
    );

}
