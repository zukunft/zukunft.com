<?php

/*

    model/import/import_convert_xbrl.php - convert an XBRL fileset to a zukunft.com import
    -----------------------------------

    XBRL (eXtensible Business Reporting Language) instance documents are usually
    delivered as a .zip containing the instance, the discoverable taxonomy set
    (schema + linkbases) and any inline attachments. This class drives the
    multi-step conversion of such a fileset into a zukunft.com JSON import,
    analogous to convert_wikipedia_table for wiki tables.

    Steps currently implemented:
      - unzip()                : deterministic unpacking of the fileset into a
                                 uniquely-named target folder so several
                                 filesets can sit side by side
      - read_instance_xml()    : pull the abb-<year>1231.xml instance document
                                 directly out of the .zip (no extraction needed)
      - extract_segment_sales(): regex-extract per-sector us-gaap:Revenues facts
                                 for the year's OperatingSegmentsMember contexts
      - build_data()           : assemble the zukunft.com import structure
                                 (words, triples, formulas, sources, values)
      - convert()              : end-to-end, returns the JSON string
      - convert_to_file()      : writes the JSON to disk, returns the path

    The XBRL-instance extraction is currently ABB-specific (matches the
    abb-<year>1231.xml filename convention and the
    D<year>_OperatingSegmentsMember_<Segment>Member context naming). Other
    issuers can be added by parameterising the instance filename and the
    context-prefix regex.


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

namespace Zukunft\ZukunftCom\main\php\cfg\import;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;



include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL_CONST . 'xbrl.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\const\xbrl;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use RuntimeException;
use ZipArchive;

class import_convert_xbrl
{

    // ABB-specific source identifiers used when building the import JSON
    const string ISSUER_ABB = words::ABB;
    const string MEASURE_SALES = words::SALES;
    const string CURRENCY_CHF = words::CHF;
    const string SCALE_MILLION = words::MIO;
    const string SECTOR = words::SECTOR;
    const string VERB_IS_A = verbs::IS_NAME;

    // non-standard formula fields of the check formula
    const string FORMULA_RESULT = json_fields::RESULT;
    const string FORMULA_COMPONENTS = json_fields::COMPONENTS;

    // the us-gaap concepts of the main income statement facts
    // and the related zukunft.com word names
    // the concept names follow the locator labels of the calculation linkbase
    const array CONCEPT_WORDS = [
        xbrl::CONCEPT_REVENUES => self::MEASURE_SALES,
        xbrl::CONCEPT_COST_OF_REVENUE => triples::COST_OF_REVENUE,
        xbrl::CONCEPT_GROSS_PROFIT => triples::GROSS_PROFIT,
    ];
    // the parent concept of the calculation linkbase used to create the check formula
    // like the summation-item validation of the Arelle XBRL processor
    // the reported parent fact must match the weighted sum of the child facts
    const string CONCEPT_CALC_PARENT = xbrl::CONCEPT_GROSS_PROFIT;

    // the concept names that the base setup defines as a triple
    // the triple is re-declared like in accounting.json
    // so that the import reuses the base concept instead of creating a word with the same name
    const array CONCEPT_TRIPLES = [
        triples::GROSS_PROFIT => [
            json_fields::NAME => triples::GROSS_PROFIT,
            json_fields::EX_FROM => words::PROFIT,
            json_fields::EX_VERB => verbs::KIND_OF_NAME,
            json_fields::EX_TO => words::GROSS,
        ],
        triples::COST_OF_REVENUE => [
            json_fields::EX_FROM => words::COST,
            json_fields::EX_VERB => verbs::OF_NAME,
            json_fields::EX_TO => words::REVENUE,
        ],
    ];

    // the concept name tokens that the base setup defines as a verb
    // a verb token creates a triple linking the previous and the next word
    // e.g. "NetOfTax" leads to the triple "net" "of" "tax"
    const array CONCEPT_NAME_VERBS = [
        verbs::AND,
        verbs::IN,
        verbs::OF,
        verbs::ON,
        verbs::PER,
        verbs::TO,
        verbs::WITH,
    ];

    // the concept name tokens that link the previous to the next phrase
    // with the name of the existing verb that the token represents
    const array CONCEPT_CONNECTOR_VERBS = [
        verbs::AND => verbs::AND_NAME,
        xbrl::CONNECTOR_FOR => verbs::USED_FOR_NAME,
        verbs::IN => verbs::IN_NAME,
        verbs::OF => verbs::OF_NAME,
        verbs::ON => verbs::ON_NAME,
        verbs::PER => verbs::PER_NAME,
        verbs::TO => verbs::TO_NAME,
        verbs::WITH => verbs::WITH_NAME,
    ];


    /**
     * unzip an XBRL fileset .zip into a uniquely-named subfolder of $target_root
     * so several filesets (different periods, different issuers) can be unpacked
     * side by side without overwriting each other.
     *
     * The folder name is derived from the zip basename plus a uniqueness token;
     * pass an explicit token via $unique_suffix to make the path deterministic
     * for tests, an empty string to use the plain zip basename as the folder name,
     * otherwise a fresh uniqid() is used.
     *
     * @param string $zip_path the absolute path to the XBRL .zip file
     * @param string $target_root directory under which the new folder is created (created on demand)
     * @param string|null $unique_suffix optional explicit uniqueness token; '' => no token; null => uniqid()
     * @return string the absolute path to the created extraction folder, terminated by DIRECTORY_SEPARATOR
     */
    function unzip(string $zip_path, string $target_root, ?string $unique_suffix = null): string
    {
        // guard clauses: refuse to operate on invalid inputs
        if (!is_file($zip_path)) {
            throw new RuntimeException("XBRL zip file not found: $zip_path");
        }
        if (!is_dir($target_root) && !mkdir($target_root, 0777, true) && !is_dir($target_root)) {
            throw new RuntimeException("cannot create XBRL target root: $target_root");
        }

        // build a unique folder name from the zip basename + uniqueness token
        $basename = pathinfo($zip_path, PATHINFO_FILENAME);
        $suffix = $unique_suffix ?? uniqid();
        if ($suffix != '') {
            $basename = $basename . '_' . $suffix;
        }
        $folder = rtrim($target_root, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . $basename . DIRECTORY_SEPARATOR;
        if (!mkdir($folder, 0777, true) && !is_dir($folder)) {
            throw new RuntimeException("cannot create XBRL extraction folder: $folder");
        }

        // open the archive and extract every entry into the target folder
        $zip = new ZipArchive();
        $code = $zip->open($zip_path);
        if ($code !== true) {
            throw new RuntimeException("cannot open XBRL zip $zip_path (ZipArchive code $code)");
        }
        $extracted = $zip->extractTo($folder);
        $zip->close();
        if (!$extracted) {
            throw new RuntimeException("XBRL zip extraction failed: $zip_path -> $folder");
        }

        return $folder;
    }

    /**
     * open the zip and return the content of the main XBRL instance document.
     *
     * @param string $zip_path path to the XBRL fileset zip
     * @param string $file_name the name of the instance file e.g. "abb-20131231.xml"
     * @return string the instance XML, or an empty string on failure
     */
    function read_instance_xml(string $zip_path, string $file_name): string
    {
        $result = '';
        $zip = new ZipArchive();
        $opened = $zip->open($zip_path);
        if ($opened === true) {
            $xml = $zip->getFromName($file_name);
            if ($xml !== false) {
                $result = $xml;
            }
            $zip->close();
        }
        return $result;
    }

    /**
     * return the content of the main XBRL instance document of an unpacked fileset.
     *
     * @param string $folder path of the folder with the unpacked XBRL fileset
     * @param string $file_name the name of the instance file e.g. "abb-20131231.xml"
     * @return string the instance XML, or an empty string on failure
     */
    function read_instance_xml_from_folder(string $folder, string $file_name): string
    {
        $result = '';
        $instance_path = rtrim($folder, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . $file_name;
        if (is_file($instance_path)) {
            $xml = file_get_contents($instance_path);
            if ($xml !== false) {
                $result = $xml;
            }
        }
        return $result;
    }

    /**
     * the name of the main XBRL instance document of the issuer fileset
     * which is the file matching abb-<year>1231.xml
     * (not the _cal, _def, _lab, _pre or .xsd companion files)
     *
     * @param string $year four digit year, e.g. "2013"
     * @return string the instance file name e.g. "abb-20131231.xml"
     */
    function instance_file_name(string $year): string
    {
        return 'abb-' . $year . '1231' . files::XML;
    }

    /**
     * the four digit year of an XBRL instance document name
     * e.g. "2013" of the instance file "abb-20131231.xml"
     *
     * @param string $file_name the name of the instance file e.g. "abb-20131231.xml"
     * @return string the four digit year or an empty string if the file name does not contain a year
     */
    function year_of_file_name(string $file_name): string
    {
        $result = '';
        if (preg_match('/(\d{4})1231/', $file_name, $matches) === 1) {
            $result = $matches[1];
        }
        return $result;
    }

    /**
     * extract the per-sector revenue facts for the given year from the instance.
     *
     * Each returned entry has a SEG_SECTOR (the CamelCase member core) and a
     * SEG_VALUE (full number as a string, exactly as tagged in the instance).
     * the build step decomposes the core into a phrase so that a multi word
     * sector name becomes a triple and never a single word with spaces.
     *
     * @param string $instance_xml the XBRL instance document
     * @param string $file_name the name of the instance file e.g. "abb-20131231.xml"
     * @return array list of [SEG_SECTOR => string, SEG_VALUE => string]
     */
    function extract_segment_sales(string $instance_xml, string $file_name): array
    {
        $result = [];
        $context_prefix = 'D' . $this->year_of_file_name($file_name) . '_OperatingSegmentsMember_';
        $pattern = '/<us-gaap:Revenues[^>]*contextRef="'
            . preg_quote($context_prefix, '/')
            . '([A-Za-z0-9]+)Member"[^>]*>(\d+)<\/us-gaap:Revenues>/';
        $found = preg_match_all($pattern, $instance_xml, $matches, PREG_SET_ORDER);
        if ($found !== false && $found > 0) {
            foreach ($matches as $match) {
                $member_core = $match[1];
                $value = $match[2];
                $sector = $member_core;
                $result[] = [
                    xbrl::SEG_SECTOR => $sector,
                    xbrl::SEG_VALUE => $value,
                ];
            }
        }
        return $result;
    }

    /**
     * the name of the calculation linkbase document of the issuer fileset
     * e.g. "abb-20131231_cal.xml" for the instance "abb-20131231.xml"
     *
     * @param string $file_name the name of the instance file e.g. "abb-20131231.xml"
     * @return string the calculation linkbase file name e.g. "abb-20131231_cal.xml"
     */
    function calculation_file_name(string $file_name): string
    {
        return basename($file_name, files::XML) . '_cal' . files::XML;
    }

    /**
     * extract one reported fact of the given concept and context from the instance.
     * a fact is the XBRL term for a single tagged number like the Arelle ModelFact.
     *
     * @param string $instance_xml the XBRL instance document
     * @param string $concept the us-gaap concept e.g. "us-gaap_Revenues"
     * @param string $context_ref the context id e.g. "D2013" for the year 2013
     * @return string the reported number as a string or an empty string if not reported
     */
    function extract_fact(string $instance_xml, string $concept, string $context_ref): string
    {
        $result = '';
        $tag = str_replace('_', ':', $concept);
        $pattern = '/<' . preg_quote($tag, '/')
            . '[^>]*contextRef="' . preg_quote($context_ref, '/') . '"[^>]*>(-?\d+)<\/'
            . preg_quote($tag, '/') . '>/';
        if (preg_match($pattern, $instance_xml, $matches) === 1) {
            $result = $matches[1];
        }
        return $result;
    }

    /**
     * extract the currency of a reported fact based on the unit definition
     * e.g. "USD" if the fact references a unit with the measure iso4217:USD
     * like the Arelle ModelUnit the unit id of the fact is resolved to the measure.
     *
     * @param string $instance_xml the XBRL instance document
     * @param string $concept the us-gaap concept e.g. "us-gaap_Revenues"
     * @param string $context_ref the context id e.g. "D2013" for the year 2013
     * @return string the currency code e.g. "USD" or an empty string if not found
     */
    function extract_fact_currency(string $instance_xml, string $concept, string $context_ref): string
    {
        $result = '';
        $tag = str_replace('_', ':', $concept);
        $pattern = '/<' . preg_quote($tag, '/')
            . '[^>]*contextRef="' . preg_quote($context_ref, '/') . '"[^>]*unitRef="([^"]+)"/';
        if (preg_match($pattern, $instance_xml, $matches) === 1) {
            $unit_pattern = '/<xbrli:unit id="' . preg_quote($matches[1], '/')
                . '">\s*<xbrli:measure>iso4217:([A-Z]+)<\/xbrli:measure>/';
            if (preg_match($unit_pattern, $instance_xml, $unit_matches) === 1) {
                $result = $unit_matches[1];
            }
        }
        return $result;
    }

    /**
     * extract the summation-item arcs of one parent concept from the calculation linkbase.
     * the calculation linkbase declares that the parent fact must match
     * the weighted sum of the child facts e.g. GrossProfit = Revenues - CostOfRevenue
     * which the Arelle XBRL processor uses for the calculation validation.
     *
     * @param string $cal_xml the calculation linkbase document
     * @param string $parent_concept the parent concept e.g. "us-gaap_GrossProfit"
     * @return array with the child concept as key and the weight as value
     */
    function extract_calculation_arcs(string $cal_xml, string $parent_concept): array
    {
        $result = [];
        $pattern = '/<link:calculationArc[^>]*xlink:from="'
            . preg_quote($parent_concept, '/')
            . '"[^>]*xlink:to="([^"]+)"[^>]*weight="([^"]+)"/';
        if (preg_match_all($pattern, $cal_xml, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $result[$match[1]] = floatval($match[2]);
            }
        }
        return $result;
    }

    /**
     * get the unique concept names used in the calculation linkbase
     * e.g. "us-gaap_OtherComprehensiveIncomeLossNetOfTax"
     *
     * @param string $cal_xml the calculation linkbase document
     * @return array with the unique concept names
     */
    function extract_concepts(string $cal_xml): array
    {
        $result = [];
        if (preg_match_all('/xlink:(?:from|to)="([^"]+)"/', $cal_xml, $matches) > 0) {
            $result = array_values(array_unique($matches[1]));
        }
        return $result;
    }

    /**
     * split a concept name into single lowercase tokens
     * e.g. "us-gaap_OtherComprehensiveIncomeLossNetOfTax" is split
     * into "us", "gaap", "other", "comprehensive", "income", "loss", "net", "of" and "tax"
     * a digit group is a separate token e.g. "May2019" is split into "may" and "2019"
     * a run of capitals stays one token e.g. "MIP" stays "mip" and "US" stays "us"
     *
     * @param string $concept the concept name e.g. "us-gaap_OtherComprehensiveIncomeLossNetOfTax"
     * @return array with the single lowercase tokens of the concept name
     */
    function concept_tokens(string $concept): array
    {
        $result = [];
        foreach (preg_split('/[-_]/', $concept) as $part) {
            if (preg_match_all('/[A-Z]+(?![a-z])|[A-Z]?[a-z]+|[0-9]+/', $part, $matches) > 0) {
                foreach ($matches[0] as $token) {
                    $result[] = strtolower($token);
                }
            }
        }
        return $result;
    }

    /**
     * get the XBRL facts from an instance document or a facts snippet
     * a space after the namespace colon as seen in hand-edited samples is tolerated
     *
     * @param string $facts_xml the xml with the XBRL facts
     * @return array list of facts with the prefix, concept, context, unit and value
     */
    function extract_facts(string $facts_xml): array
    {
        $result = [];
        $pattern = '/<([\w\-]+):\s*(\w+)([^>]*?)>\s*(-?[\d.]+)\s*<\/\1:\s*\2\s*>/';
        if (preg_match_all($pattern, $facts_xml, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $fact = [];
                $fact[xbrl::FACT_PREFIX] = $match[1];
                $fact[xbrl::FACT_CONCEPT] = $match[2];
                $fact[xbrl::FACT_CONTEXT] = $this->fact_attribute($match[3], xbrl::ATTR_CONTEXT);
                $fact[xbrl::FACT_UNIT] = $this->fact_attribute($match[3], xbrl::ATTR_UNIT);
                $fact[xbrl::FACT_VALUE] = $match[4];
                $result[] = $fact;
            }
        }
        return $result;
    }

    /**
     * get an attribute value from the attribute part of an XBRL fact tag
     *
     * @param string $attributes the attribute part of the fact tag
     * @param string $name the name of the attribute e.g. "contextRef"
     * @return string the attribute value or an empty string if not set
     */
    private function fact_attribute(string $attributes, string $name): string
    {
        $result = '';
        if (preg_match('/' . $name . '="([^"]+)"/', $attributes, $match) == 1) {
            $result = $match[1];
        }
        return $result;
    }

    /**
     * get the year of an XBRL context reference
     * e.g. "I2013" for the instant end of 2013 leads to "2013"
     *
     * @param string $context_ref the context reference of a fact e.g. "I2013"
     * @return string the year of the context or an empty string if no year is found
     */
    private function context_year(string $context_ref): string
    {
        $result = '';
        if (preg_match('/^[A-Z]+(\d{4})/', $context_ref, $match) == 1) {
            $result = $match[1];
        }
        return $result;
    }

    /**
     * check if an XBRL context reference covers a full year
     * e.g. "I2013" and "I2013_FairValueInputsLevel1Member" cover the year 2013
     * but "D2013Q2_M04" covers only a month and is not converted
     *
     * @param string $context_ref the context reference of a fact
     * @return bool true if the context covers a full year
     */
    private function is_year_context(string $context_ref): bool
    {
        return preg_match('/^[ID]\d{4}(?:_[A-Za-z0-9]+)*$/', $context_ref) == 1;
    }

    /**
     * get the dimension members of an XBRL context reference
     * e.g. "I2013_FairValueInputsLevel1Member_FairValueMeasurementsRecurringMember"
     * leads to "FairValueInputsLevel1Member" and "FairValueMeasurementsRecurringMember"
     *
     * @param string $context_ref the context reference of a fact
     * @return array the dimension members or an empty array for a base context
     */
    private function context_members(string $context_ref): array
    {
        $result = [];
        if (preg_match('/^[ID]\d{4}((?:_[A-Za-z0-9]+)+)$/', $context_ref, $match) == 1) {
            $result = explode('_', trim($match[1], '_'));
        }
        return $result;
    }

    /**
     * convert a full revenue value into the million figure used in the JSON.
     *
     * The instance stores full numbers (e.g. 9915000000) that the model records
     * as millions (9915). The value is divided by one million using integer math.
     *
     * @param string $full_value the full number as a string
     * @return string the figure in millions as a string
     */
    private function value_to_millions(string $full_value): string
    {
        $as_int = intval($full_value);
        $millions = intdiv($as_int, 1000000);
        $result = strval($millions);
        return $result;
    }

    /**
     * build the complete zukunft.com style data array from the extracted facts.
     *
     * Structure follows the existing ABB_<year>.json sample: a time word, the
     * company, the measure, the currency, the scale and one word per sector;
     * one "is a sector" triple per sector; one value per sector plus the
     * income statement facts; one formula stating the total is the sum of the
     * sector sales and one check formula per calculation linkbase parent
     * with a calc-validation entry so that the import can verify the numbers.
     *
     * @param array  $segments list of [SEG_SECTOR => string, SEG_VALUE => string]
     * @param string $file_name the name of the instance file e.g. "abb-20131231.xml"
     * @param string $time     the creation timestamp or empty to use the current time
     * @param array  $statement the income statement facts in millions with the word name as key
     * @param string $currency the reported currency e.g. "USD" or empty to use the CHF fallback
     * @param array  $calc_arcs the calculation linkbase arcs with the child concept as key and the weight as value
     * @param array  $concepts the concept names used in the calculation linkbase
     * @param array  $facts all facts of the instance as extracted with extract_facts
     * @param user_message|null $msg to report the inconsistencies of the facts
     * @return array the data structure ready to be encoded as JSON
     */
    function build_data(
        array         $segments,
        string        $file_name,
        string        $time = '',
        array         $statement = [],
        string        $currency = '',
        array         $calc_arcs = [],
        array         $concepts = [],
        array         $facts = [],
        ?user_message $msg = null
    ): array
    {
        $year = $this->year_of_file_name($file_name);
        if ($time == '') {
            $time = date('Y-m-d H:i:s');
        }
        if ($currency == '') {
            $currency = self::CURRENCY_CHF;
        }
        $src_json = $this->source_json($year);
        $source_name = $src_json[json_fields::NAME];

        // only the year and the measure have a fixed phrase type
        // the other words are declared name-only like in the ABB_<year>.json sample
        // so that an import never overwrites the phrase types of the base words
        $words = [];
        $words[] = [json_fields::NAME => $year, json_fields::TYPE_NAME => phrase_type_shared::TIME];
        $words[] = [json_fields::NAME => self::ISSUER_ABB];
        $words[] = [json_fields::NAME => self::MEASURE_SALES, json_fields::TYPE_NAME => phrase_type_shared::MEASURE];
        $words[] = [json_fields::NAME => $currency];
        $words[] = [json_fields::NAME => self::SCALE_MILLION];
        $words[] = [json_fields::NAME => self::SECTOR];

        $triples = [];
        $values = [];
        $components = [];
        $total = 0;

        foreach ($segments as $segment) {
            // decompose the CamelCase sector core into a phrase
            // so that a multi word sector name becomes a triple and not a spaced word
            $sector = $this->decompose_member($segment[xbrl::SEG_SECTOR], $words, $triples);
            $millions = $this->value_to_millions($segment[xbrl::SEG_VALUE]);

            $triples[] = [
                json_fields::EX_FROM => $sector,
                json_fields::EX_VERB => self::VERB_IS_A,
                json_fields::EX_TO   => self::SECTOR,
            ];

            $values[] = [
                json_fields::WORDS  => [self::ISSUER_ABB, self::MEASURE_SALES, $year,
                                        $currency, self::SCALE_MILLION, $sector, $year],
                json_fields::NUMBER => $millions,
                json_fields::SOURCE => $source_name,
            ];

            $components[] = $sector;
            $total = $total + intval($millions);
        }

        // use the reported total revenues and not the sum of the segments
        // because the segment sales include the sales between the segments
        $total_text = $statement[self::MEASURE_SALES] ?? strval($total);

        $values[] = [
            json_fields::WORDS  => [self::ISSUER_ABB, self::MEASURE_SALES, $year,
                                    $currency, self::SCALE_MILLION, $year],
            json_fields::NUMBER => $total_text,
            json_fields::SOURCE => $source_name,
        ];

        // add the other income statement facts e.g. the cost of revenue and the gross profit
        foreach ($statement as $wrd_name => $millions) {
            if ($wrd_name != self::MEASURE_SALES) {
                if (key_exists($wrd_name, self::CONCEPT_TRIPLES)) {
                    // re-declare the base concept triple e.g. "gross profit"
                    // instead of creating a word with the same name
                    $trp_json = self::CONCEPT_TRIPLES[$wrd_name];
                    $words[] = [json_fields::NAME => $trp_json[json_fields::EX_FROM]];
                    $words[] = [json_fields::NAME => $trp_json[json_fields::EX_TO]];
                    $triples[] = $trp_json;
                } else {
                    $words[] = [json_fields::NAME => $wrd_name];
                }
                $values[] = [
                    json_fields::WORDS  => [self::ISSUER_ABB, $wrd_name, $year,
                                            $currency, self::SCALE_MILLION, $year],
                    json_fields::NUMBER => $millions,
                    json_fields::SOURCE => $source_name,
                ];
            }
        }

        // add the words of the concept names used in the calculation linkbase
        // a token that the base setup defines as a verb creates a triple
        // linking the previous and the next word e.g. "NetOfTax" leads to "net" "of" "tax"
        foreach ($concepts as $concept) {
            $tokens = $this->concept_tokens($concept);
            foreach ($tokens as $i => $token) {
                if (in_array($token, self::CONCEPT_NAME_VERBS)) {
                    if ($i > 0 and $i < count($tokens) - 1
                        and !in_array($tokens[$i - 1], self::CONCEPT_NAME_VERBS)
                        and !in_array($tokens[$i + 1], self::CONCEPT_NAME_VERBS)) {
                        $triples[] = [
                            json_fields::EX_FROM => $tokens[$i - 1],
                            json_fields::EX_VERB => $token,
                            json_fields::EX_TO   => $tokens[$i + 1],
                        ];
                    }
                } else {
                    $words[] = [json_fields::NAME => $token];
                }
            }
        }

        // add the values of all full year facts of the instance
        // e.g. the fair value disclosures with the dimension members as words
        if (count($facts) > 0) {
            if ($msg == null) {
                $msg = new user_message();
            }
            foreach ($this->facts_json($facts, $source_name, $words, $triples, $msg) as $fact_value) {
                $values[] = $fact_value;
            }
        }

        // remove the duplicates e.g. if a token is part of several concept names
        $words = $this->unique_words($words);
        $triples = $this->unique_triples($triples);

        $formulas = [];
        $formulas[] = [
            json_fields::NAME        => self::ISSUER_ABB . ' ' . words::TOTAL_PRE . ' ' . self::MEASURE_SALES . ' ' . $year,
            json_fields::EXPRESSION  => self::ISSUER_ABB . ' ' . self::MEASURE_SALES . ' ' . $year
                . ' = sum of ' . self::ISSUER_ABB . ' ' . self::MEASURE_SALES . ' ' . $year . ' per ' . self::SECTOR,
            self::FORMULA_RESULT     => $total_text,
            self::FORMULA_COMPONENTS => $components,
        ];

        $check_results = [];
        $this->add_calc_check($calc_arcs, $statement, $year, $currency, $formulas, $check_results);

        $sources = [];
        $sources[] = $src_json;

        $result = $this->data_envelope($time, $words, $triples, $formulas, $sources, $values, $check_results);
        return $result;
    }

    /**
     * the surrounding import json structure shared by all XBRL conversions
     *
     * @param string $time the creation timestamp of the import json
     * @param array $words the import json of the words
     * @param array $triples the import json of the triples
     * @param array $formulas the import json of the formulas
     * @param array $sources the import json of the sources
     * @param array $values the import json of the values
     * @param array $check_results the calc-validation entries or empty to skip the section
     * @return array the data structure ready to be encoded as JSON
     */
    private function data_envelope(
        string $time,
        array  $words,
        array  $triples,
        array  $formulas,
        array  $sources,
        array  $values,
        array  $check_results = []): array
    {
        $result = [
            json_fields::VERSION   => def::PRG_VERSION,
            json_fields::TIME      => $time,
            json_fields::USER      => 'timon',
            json_fields::SELECTION => [self::ISSUER_ABB],
            json_fields::WORDS     => $words,
            json_fields::TRIPLES   => $triples,
            json_fields::FORMULAS  => $formulas,
            json_fields::SOURCES   => $sources,
            json_fields::VALUES    => $values,
        ];
        if (count($check_results) > 0) {
            $result[json_fields::CALC_VALIDATION] = $check_results;
        }
        $result[json_fields::VIEWS] = [];
        return $result;
    }

    /**
     * remove the words with a name that is already in the list
     * the first entry is kept because it may have the phrase type set
     *
     * @param array $words list of the import words that may contain duplicates
     * @return array the list of the import words with unique names
     */
    private function unique_words(array $words): array
    {
        $result = [];
        foreach ($words as $wrd_json) {
            if (!key_exists($wrd_json[json_fields::NAME], $result)) {
                $result[$wrd_json[json_fields::NAME]] = $wrd_json;
            }
        }
        return array_values($result);
    }

    /**
     * remove the triples with a from, verb and to combination that is already in the list
     * because the from, verb and to key must be unique within an import
     *
     * @param array $triples list of the import triples that may contain duplicates
     * @return array the list of the import triples with a unique from, verb and to key
     */
    private function unique_triples(array $triples): array
    {
        $result = [];
        foreach ($triples as $trp_json) {
            $key = $trp_json[json_fields::EX_FROM]
                . ' ' . $trp_json[json_fields::EX_VERB]
                . ' ' . $trp_json[json_fields::EX_TO];
            if (!key_exists($key, $result)) {
                $result[$key] = $trp_json;
            }
        }
        return array_values($result);
    }

    /**
     * split a concept name into a hierarchy of words and triples
     * e.g. "AllowanceForDoubtfulAccountsReceivableCurrent" leads to the triple
     * "allowance" "used for" "doubtful accounts receivable current"
     * with the natural name "allowance for doubtful accounts receivable current"
     *
     * a connector token like "for" or "of" links the left to the right phrase;
     * two tokens are joined with the "and" verb
     * and a longer phrase is split into the leading phrase and the trailing word
     * linked with the "kind of" verb like the base setup triple "gross profit"
     *
     * @param array $tokens the lowercase tokens of the concept name
     * @param array $words filled with one entry per single token
     * @param array $triples filled with one entry per created phrase
     * @param string $description the source concept added to the top triple e.g. "us-gaap:GrossProfit"
     * @return string the name of the created phrase
     */
    private function decompose_concept(array $tokens, array &$words, array &$triples, string $description = ''): string
    {
        $result = implode(' ', $tokens);
        if (key_exists($result, self::CONCEPT_TRIPLES)) {
            // re-declare the base setup triple e.g. "gross profit"
            // instead of creating a second triple with the same name
            $trp_json = self::CONCEPT_TRIPLES[$result];
            $words[] = [json_fields::NAME => $trp_json[json_fields::EX_FROM]];
            $words[] = [json_fields::NAME => $trp_json[json_fields::EX_TO]];
            $triples[] = $trp_json;
        } elseif (count($tokens) == 1) {
            if (!in_array($tokens[0], self::CONCEPT_NAME_VERBS)) {
                $words[] = [json_fields::NAME => $tokens[0]];
            }
        } elseif (count($tokens) > 1) {
            // split at the first connector token that links two phrases
            $connector = 0;
            foreach ($tokens as $i => $token) {
                if ($connector == 0 and $i > 0 and $i < count($tokens) - 1
                    and key_exists($token, self::CONCEPT_CONNECTOR_VERBS)
                    and !key_exists($tokens[$i - 1], self::CONCEPT_CONNECTOR_VERBS)
                    and !key_exists($tokens[$i + 1], self::CONCEPT_CONNECTOR_VERBS)) {
                    $connector = $i;
                }
            }
            if ($connector > 0) {
                $token = $tokens[$connector];
                $verb = self::CONCEPT_CONNECTOR_VERBS[$token];
                if (!in_array($token, self::CONCEPT_NAME_VERBS)) {
                    $words[] = [json_fields::NAME => $token];
                }
                $from = $this->decompose_concept(array_slice($tokens, 0, $connector), $words, $triples);
                $to = $this->decompose_concept(array_slice($tokens, $connector + 1), $words, $triples);
                $this->add_phrase_triple($triples, $result, $from, $verb, $to, $description);
            } elseif (count($tokens) == 2) {
                $from = $this->decompose_concept([$tokens[0]], $words, $triples);
                $to = $this->decompose_concept([$tokens[1]], $words, $triples);
                $this->add_phrase_triple($triples, $result, $from, verbs::AND_NAME, $to, $description);
            } else {
                $from = $this->decompose_concept(array_slice($tokens, 0, -1), $words, $triples);
                $to = $this->decompose_concept(array_slice($tokens, -1), $words, $triples);
                $this->add_phrase_triple($triples, $result, $from, verbs::KIND_OF_NAME, $to, $description);
            }
        }
        return $result;
    }

    /**
     * turn a CamelCase XBRL member or sector core into a phrase
     * by splitting it into tokens and decomposing it into words and triples
     * so that a multi word name never becomes a single word with spaces
     * e.g. "DiscreteAutomationAndMotion" leads to the triple
     * "discrete automation" "and" "motion" with the name "discrete automation and motion"
     *
     * @param string $core the CamelCase member or sector core e.g. "FairValueInputsLevel1Member"
     * @param array $words filled with one entry per single token
     * @param array $triples filled with one entry per created phrase
     * @return string the name of the created phrase
     */
    private function decompose_member(string $core, array &$words, array &$triples): string
    {
        if (str_ends_with($core, xbrl::MEMBER_SUFFIX)) {
            $core = substr($core, 0, -strlen(xbrl::MEMBER_SUFFIX));
        }
        return $this->decompose_concept($this->concept_tokens($core), $words, $triples);
    }

    /**
     * add a concept phrase triple to the import triple list
     * the name is only set if it differs from the name that the import derives
     *
     * @param array $triples the import triple list to extend
     * @param string $name the natural name of the phrase e.g. "accounts receivable"
     * @param string $from the name of the left phrase
     * @param string $verb the name of the linking verb
     * @param string $to the name of the right phrase
     * @param string $description the source concept e.g. "us-gaap:GrossProfit" or empty
     * @return void
     */
    private function add_phrase_triple(
        array  &$triples,
        string $name,
        string $from,
        string $verb,
        string $to,
        string $description = ''): void
    {
        $trp_json = [];
        if ($name != $from . ' ' . $verb . ' ' . $to) {
            $trp_json[json_fields::NAME] = $name;
        }
        $trp_json[json_fields::EX_FROM] = $from;
        $trp_json[json_fields::EX_VERB] = $verb;
        $trp_json[json_fields::EX_TO] = $to;
        if ($description != '') {
            $trp_json[json_fields::DESCRIPTION] = $description;
        }
        $triples[] = $trp_json;
    }

    /**
     * the import json entry of the ABB XBRL fileset source
     *
     * @param string $year the year of the fileset e.g. "2013"
     * @return array the import json of the source with the name and the url
     */
    private function source_json(string $year): array
    {
        return [
            json_fields::NAME => self::ISSUER_ABB . ' XBRL files for ' . $year,
            json_fields::URL  => 'https://new.abb.com/investorrelations/calendar-events-and-publications/'
                . 'financial-results-and-presentations/sec-filings/xbrl-' . $year,
        ];
    }

    /**
     * convert the XBRL facts of the full year contexts into import values
     * each concept name is decomposed into a hierarchy of words and triples
     * and each dimension member of the context is added as a word
     * a fact that is repeated with a different value
     * and two facts that lead to the same words but a different value
     * are reported as inconsistencies via the user message
     *
     * @param array $facts the facts as extracted with extract_facts
     * @param string $source_name the name of the import source of the values
     * @param array $words extended with the words of the years, units, concepts and members
     * @param array $triples extended with the triples of the concept phrases
     * @param user_message $msg to report the inconsistencies of the facts
     * @return array the import json of the values
     */
    private function facts_json(
        array        $facts,
        string       $source_name,
        array        &$words,
        array        &$triples,
        user_message $msg): array
    {
        // keep one fact per concept and context
        // and report a fact that is repeated with a different value
        $base = [];
        foreach ($facts as $fact) {
            if ($this->is_year_context($fact[xbrl::FACT_CONTEXT])) {
                $key = $fact[xbrl::FACT_CONCEPT] . ' ' . $fact[xbrl::FACT_CONTEXT];
                if (key_exists($key, $base)) {
                    if ($base[$key][xbrl::FACT_VALUE] != $fact[xbrl::FACT_VALUE]) {
                        $this->report_fact_conflict($fact, $base[$key][xbrl::FACT_VALUE], $msg);
                    }
                } else {
                    $base[$key] = $fact;
                }
            }
        }

        // the words of the years and the units of the converted facts
        $years = [];
        $units = [];
        foreach ($base as $fact) {
            $year = $this->context_year($fact[xbrl::FACT_CONTEXT]);
            if ($year != '' and !in_array($year, $years)) {
                $years[] = $year;
            }
            $unit = $fact[xbrl::FACT_UNIT];
            if ($unit != '' and !in_array($unit, $units)) {
                $units[] = $unit;
            }
        }
        rsort($years);
        foreach ($years as $year) {
            $words[] = [json_fields::NAME => $year, json_fields::TYPE_NAME => phrase_type_shared::TIME];
        }
        foreach ($units as $unit) {
            $words[] = [json_fields::NAME => $unit];
        }

        // one phrase hierarchy per concept and one value per fact
        // a second fact that leads to the same words e.g. of an instant
        // and a duration context of the same year is reported and skipped
        $values = [];
        $phrases = [];
        $value_keys = [];
        foreach ($base as $fact) {
            $concept = $fact[xbrl::FACT_CONCEPT];
            if (!key_exists($concept, $phrases)) {
                $phrases[$concept] = $this->decompose_concept(
                    $this->concept_tokens($concept), $words, $triples,
                    $fact[xbrl::FACT_PREFIX] . ':' . $concept);
            }
            $names = [self::ISSUER_ABB, $phrases[$concept]];
            foreach ($this->context_members($fact[xbrl::FACT_CONTEXT]) as $member) {
                $names[] = $this->decompose_member($member, $words, $triples);
            }
            if ($fact[xbrl::FACT_UNIT] != '') {
                $names[] = $fact[xbrl::FACT_UNIT];
            }
            $names[] = $this->context_year($fact[xbrl::FACT_CONTEXT]);
            $value_key = implode(' ', $names);
            if (key_exists($value_key, $value_keys)) {
                if ($value_keys[$value_key] != $fact[xbrl::FACT_VALUE]) {
                    $this->report_fact_conflict($fact, $value_keys[$value_key], $msg);
                }
            } else {
                $value_keys[$value_key] = $fact[xbrl::FACT_VALUE];
                $values[] = [
                    json_fields::WORDS  => $names,
                    json_fields::NUMBER => $fact[xbrl::FACT_VALUE],
                    json_fields::SOURCE => $source_name,
                ];
            }
        }
        return $values;
    }

    /**
     * report a fact that does not match an already converted value of the same target
     *
     * @param array $fact the fact with the value that differs
     * @param string $value the value of the already converted fact
     * @param user_message $msg to report the inconsistency to the user
     * @return void
     */
    private function report_fact_conflict(array $fact, string $value, user_message $msg): void
    {
        $msg->add(msg_id::XBRL_FACT_VALUE_CONFLICT, [
            msg_id::VAR_NAME      => $fact[xbrl::FACT_PREFIX] . ':' . $fact[xbrl::FACT_CONCEPT],
            msg_id::VAR_ID        => $fact[xbrl::FACT_CONTEXT],
            msg_id::VAR_VALUE     => $value,
            msg_id::VAR_VALUE_CHK => $fact[xbrl::FACT_VALUE],
        ]);
    }

    /**
     * create the check formula and the calc-validation entry
     * based on the summation-item arcs of the calculation linkbase
     * e.g. "gross profit" = "sales" - "cost of revenue"
     * so that the import can verify the reported numbers
     * like the calculation validation of the Arelle XBRL processor
     *
     * @param array  $calc_arcs the calculation arcs with the child concept as key and the weight as value
     * @param array  $statement the income statement facts in millions with the word name as key
     * @param string $year     four digit year, e.g. "2013"
     * @param string $currency the reported currency e.g. "USD"
     * @param array  $formulas list of the import formulas to be extended
     * @param array  $check_results list of the calc-validation entries to be extended
     * @return void
     */
    private function add_calc_check(
        array  $calc_arcs,
        array  $statement,
        string $year,
        string $currency,
        array  &$formulas,
        array  &$check_results
    ): void
    {
        $parent_wrd = self::CONCEPT_WORDS[self::CONCEPT_CALC_PARENT] ?? '';
        if ($parent_wrd == '' or !key_exists($parent_wrd, $statement)) {
            return;
        }

        // build the expression from the weighted arcs e.g. "gross profit" = "sales" - "cost of revenue"
        $exp_part = '';
        $assigned = [];
        foreach ($calc_arcs as $concept => $weight) {
            $wrd_name = self::CONCEPT_WORDS[$concept] ?? '';
            if ($wrd_name == '' or !key_exists($wrd_name, $statement)) {
                return;
            }
            if ($exp_part == '') {
                $exp_part = $weight < 0 ? '- ' : '';
            } else {
                $exp_part .= $weight < 0 ? ' - ' : ' + ';
            }
            $exp_part .= '"' . $wrd_name . '"';
            $assigned[] = $wrd_name;
        }
        if ($exp_part == '') {
            return;
        }

        $frm_name = self::ISSUER_ABB . ' ' . $parent_wrd . ' ' . $year;
        $formulas[] = [
            json_fields::NAME       => $frm_name,
            json_fields::EXPRESSION => '"' . $parent_wrd . '" = ' . $exp_part,
            json_fields::ASSIGNED   => $assigned,
        ];

        $check_results[] = [
            json_fields::CONTEXT      => array_merge(
                [self::ISSUER_ABB, $year, $currency, self::SCALE_MILLION], $assigned),
            json_fields::FORMULA_NAME => $frm_name,
            json_fields::WORDS        => [self::ISSUER_ABB, $parent_wrd, $year,
                                          $currency, self::SCALE_MILLION],
            json_fields::NUMBER       => $statement[$parent_wrd],
        ];
    }

    /**
     * end-to-end conversion: read the zip, extract the sales, build the data,
     * and return the JSON string. Throws on any failure so the caller can
     * decide how to surface it (CLI exit code, HTTP response, test assertion).
     *
     * @param string $zip_path path to the XBRL fileset zip
     * @param string $file_name the name of the instance file e.g. "abb-20131231.xml"
     * @param string $time     the creation timestamp or empty to use the current time
     * @return string a pretty-printed JSON string
     */
    function convert(string $zip_path, string $file_name, string $time = '', ?user_message $msg = null): string
    {
        // guard clauses for the two failure modes the CLI script handled
        if (!is_file($zip_path)) {
            throw new RuntimeException("XBRL zip file not found: $zip_path");
        }

        $instance_xml = $this->read_instance_xml($zip_path, $file_name);
        if ($instance_xml === '') {
            throw new RuntimeException(
                "Could not read instance $file_name from $zip_path"
            );
        }
        $cal_xml = $this->read_instance_xml($zip_path, $this->calculation_file_name($file_name));
        return $this->convert_instance($instance_xml, $cal_xml, $file_name, $time, $zip_path, $msg);
    }

    /**
     * convert an unpacked XBRL fileset folder into the zukunft.com import JSON.
     * same as convert but based on the files unpacked with the unzip function.
     *
     * @param string $folder path of the folder with the unpacked XBRL fileset
     * @param string $file_name the name of the instance file e.g. "abb-20131231.xml"
     * @param string $time   the creation timestamp or empty to use the current time
     * @param user_message|null $msg to report the inconsistencies of the instance facts
     * @return string a pretty-printed JSON string
     */
    function convert_folder(string $folder, string $file_name, string $time = '', ?user_message $msg = null): string
    {
        if (!is_dir($folder)) {
            throw new RuntimeException("XBRL fileset folder not found: $folder");
        }

        $instance_xml = $this->read_instance_xml_from_folder($folder, $file_name);
        if ($instance_xml === '') {
            throw new RuntimeException(
                "Could not read instance $file_name from $folder"
            );
        }
        $cal_xml = $this->read_instance_xml_from_folder($folder, $this->calculation_file_name($file_name));
        return $this->convert_instance($instance_xml, $cal_xml, $file_name, $time, $folder, $msg);
    }

    /**
     * convert an XBRL instance document into the zukunft.com import JSON
     *
     * @param string $instance_xml the XBRL instance document
     * @param string $cal_xml the calculation linkbase document or empty to skip the check formula
     * @param string $file_name the name of the instance file e.g. "abb-20131231.xml"
     * @param string $time         the creation timestamp or empty to use the current time
     * @param string $source_path  the zip or folder used to read the instance for the error message
     * @param user_message|null $msg to report the inconsistencies of the instance facts
     * @return string a pretty-printed JSON string
     */
    private function convert_instance(
        string        $instance_xml,
        string        $cal_xml,
        string        $file_name,
        string        $time,
        string        $source_path,
        ?user_message $msg = null
    ): string
    {
        $segments = $this->extract_segment_sales($instance_xml, $file_name);
        if (count($segments) === 0) {
            throw new RuntimeException(
                "No operating segment revenues found for $file_name in $source_path"
            );
        }

        // extract the income statement facts of the year e.g. the total revenues
        $context_ref = 'D' . $this->year_of_file_name($file_name);
        $statement = [];
        foreach (self::CONCEPT_WORDS as $concept => $wrd_name) {
            $fact = $this->extract_fact($instance_xml, $concept, $context_ref);
            if ($fact != '') {
                $statement[$wrd_name] = $this->value_to_millions($fact);
            }
        }

        // get the reported currency and the calculation check of the gross profit
        $currency = $this->extract_fact_currency($instance_xml, array_key_first(self::CONCEPT_WORDS), $context_ref);
        $calc_arcs = [];
        $concepts = [];
        if ($cal_xml != '') {
            $calc_arcs = $this->extract_calculation_arcs($cal_xml, self::CONCEPT_CALC_PARENT);
            $concepts = $this->extract_concepts($cal_xml);
        }

        // get all facts of the instance to convert e.g. the fair value disclosures
        $facts = $this->extract_facts($instance_xml);

        $data = $this->build_data($segments, $file_name, $time, $statement, $currency, $calc_arcs, $concepts, $facts, $msg);
        $result = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        return $result;
    }

    /**
     * convert the XBRL zip into a JSON file. Replaces the CLI main() of the
     * standalone script: the same inputs (zip path, instance file name, optional
     * output path), but instead of writing to STDERR and returning an exit code it
     * throws on error and returns the path of the file actually written.
     *
     * @param string $zip_path path to the XBRL fileset zip
     * @param string $file_name the name of the instance file e.g. "abb-20131231.xml"
     * @param string $out_path output JSON path; empty => "ABB_<year>.json" in cwd
     * @return string the absolute or relative path of the written JSON file
     */
    function convert_to_file(string $zip_path, string $file_name, string $out_path = ''): string
    {
        $json = $this->convert($zip_path, $file_name);
        $target = $out_path;
        if ($target === '') {
            $target = self::ISSUER_ABB . '_' . $this->year_of_file_name($file_name) . files::JSON;
        }
        $written = file_put_contents($target, $json . "\n");
        if ($written === false) {
            throw new RuntimeException("Could not write output to $target");
        }
        return $target;
    }

    /**
     * convert an unpacked XBRL fileset folder into a JSON file saved in the same folder
     * so that the unpacked source files and the created import JSON stay together
     * e.g. .../xbrl/abb-2013-xbrl_fileset-20131231/abb-2013-xbrl_fileset-20131231.json
     *
     * @param string $folder path of the folder with the unpacked XBRL fileset
     * @param string $file_name the name of the instance file e.g. "abb-20131231.xml"
     * @param string $time   the creation timestamp or empty to use the current time
     * @param user_message|null $msg to report the inconsistencies of the instance facts
     * @return string the path of the written JSON file
     */
    function convert_folder_to_file(string $folder, string $file_name, string $time = '', ?user_message $msg = null): string
    {
        $json = $this->convert_folder($folder, $file_name, $time, $msg);
        $folder_path = rtrim($folder, DIRECTORY_SEPARATOR);
        $target = $folder_path . DIRECTORY_SEPARATOR . basename($folder_path) . files::JSON;
        $written = file_put_contents($target, $json . "\n");
        if ($written === false) {
            throw new RuntimeException("Could not write output to $target");
        }
        return $target;
    }

    /**
     * convert XBRL facts into the zukunft.com import JSON
     * each concept name is decomposed into a hierarchy of words and triples
     * and each fact of a base context is converted into a value
     * inconsistencies in the facts are reported via the user message
     * and never via an exception so that a single bad fact does not stop the conversion
     *
     * @param string $facts_xml the xml with the XBRL facts
     * @param string $time the creation timestamp or empty to use the current time
     * @param user_message $msg to report the inconsistencies of the facts
     * @return string a pretty-printed JSON string
     */
    function convert_facts(string $facts_xml, string $time, user_message $msg): string
    {
        if ($time == '') {
            $time = date('Y-m-d H:i:s');
        }
        $facts = $this->extract_facts($facts_xml);
        if (count($facts) == 0) {
            $msg->add_id(msg_id::XBRL_NO_FACTS);
        }

        // the source of the latest fact year
        $year_max = '';
        foreach ($facts as $fact) {
            if ($this->is_year_context($fact[xbrl::FACT_CONTEXT])) {
                $year = $this->context_year($fact[xbrl::FACT_CONTEXT]);
                if ($year > $year_max) {
                    $year_max = $year;
                }
            }
        }
        $sources = [];
        $source_name = '';
        if ($year_max != '') {
            $src_json = $this->source_json($year_max);
            $source_name = $src_json[json_fields::NAME];
            $sources[] = $src_json;
        }

        // the issuer word and the words, triples and values of the facts
        $words = [];
        $words[] = [json_fields::NAME => self::ISSUER_ABB];
        $triples = [];
        $values = $this->facts_json($facts, $source_name, $words, $triples, $msg);
        $words = $this->unique_words($words);
        $triples = $this->unique_triples($triples);

        $data = $this->data_envelope($time, $words, $triples, [], $sources, $values);
        $result = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $result;
    }

    /**
     * convert an XBRL facts file into an import JSON file saved in the same folder
     * e.g. "sample.xml" leads to "sample_json.json"
     * problems are reported via the user message and never via an exception
     *
     * @param string $folder path of the folder with the facts file
     * @param string $file_name the name of the facts file e.g. "sample.xml"
     * @param string $time the creation timestamp or empty to use the current time
     * @param user_message $msg to report the problems and the fact inconsistencies
     * @return string the path of the written JSON file or an empty string if it failed
     */
    function convert_facts_to_file(string $folder, string $file_name, string $time, user_message $msg): string
    {
        $result = '';
        $folder_path = rtrim($folder, DIRECTORY_SEPARATOR);
        $path = $folder_path . DIRECTORY_SEPARATOR . $file_name;
        if (!is_file($path)) {
            $msg->add(msg_id::IMPORT_READ_ERROR, [
                msg_id::VAR_FILE_TYPE => xbrl::FACTS_FILE_TYPE,
                msg_id::VAR_FILE_NAME => $path,
            ]);
        } else {
            $json = $this->convert_facts(file_get_contents($path), $time, $msg);
            $target = $folder_path . DIRECTORY_SEPARATOR
                . basename($file_name, files::XML) . xbrl::FACTS_JSON_SUFFIX . files::JSON;
            if (file_put_contents($target, $json . "\n") === false) {
                $msg->add(msg_id::FILE_WRITE_FAILED, [msg_id::VAR_FILE_NAME => $target]);
            } else {
                $result = $target;
            }
        }
        return $result;
    }

}
