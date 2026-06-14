<?php

/*

    cfg/const/xbrl.php - the XBRL format vocabulary used to convert an XBRL fileset to a zukunft.com import
    ------------------

    the XBRL (eXtensible Business Reporting Language) namespace, attribute, member and
    fact field names that import_convert_xbrl uses to read an XBRL instance and
    calculation linkbase; collected here so that the converter only references consts


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\const;

include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;

class xbrl
{

    // the US GAAP taxonomy namespace prefix of the income statement concept ids (triples::US_GAAP)
    const string NS = triples::US_GAAP_XBRL;
    // the us-gaap concepts of the main income statement facts
    // the concept names follow the locator labels of the calculation linkbase
    const string CONCEPT_REVENUES = self::NS . '_Revenues';
    const string CONCEPT_COST_OF_REVENUE = self::NS . '_CostOfRevenue';
    const string CONCEPT_GROSS_PROFIT = self::NS . '_GrossProfit';

    // the trailing marker of an XBRL dimension member e.g. "OperatingSegmentsMember"
    const string MEMBER_SUFFIX = 'Member';

    // "for" is not a base setup verb so the token stays a word
    // and the concept name is linked with the closest verb "used for"
    const string CONNECTOR_FOR = 'for';

    // the XBRL fact tag attributes
    const string ATTR_CONTEXT = 'contextRef';
    const string ATTR_UNIT = 'unitRef';

    // the field names of an extracted XBRL fact
    const string FACT_PREFIX = 'prefix';
    const string FACT_CONCEPT = 'concept';
    const string FACT_CONTEXT = 'context';
    const string FACT_UNIT = 'unit';
    const string FACT_VALUE = 'value';

    // keys used inside the segment array returned by import_convert_xbrl::extract_segment_sales()
    const string SEG_SECTOR = words::SECTOR;
    const string SEG_VALUE = self::FACT_VALUE;

    // suffix added to the facts file name for the created import json
    // e.g. "sample.xml" is converted to "sample_json.json"
    const string FACTS_JSON_SUFFIX = '_json';
    // the file type of an XBRL facts file used in error messages
    const string FACTS_FILE_TYPE = 'XBRL facts';

}