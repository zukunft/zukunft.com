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

include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\shared\json_fields;
use RuntimeException;
use ZipArchive;

class import_convert_xbrl
{

    const string ZIP_EXT = '.zip';

    // ABB-specific source identifiers used when building the import JSON
    const string ISSUER_ABB = 'ABB';
    const string MEASURE_SALES = 'sales';
    const string CURRENCY_CHF = 'CHF';
    const string SCALE_MILLION = 'million';
    const string SECTOR = 'sector';
    const string VERB_IS_A = 'is a';

    // keys used inside the segment array returned by extract_segment_sales()
    const string SEG_SECTOR = 'sector';
    const string SEG_VALUE = 'value';

    // non-standard formula fields (no json_fields constant yet)
    const string FORMULA_RESULT = 'result';
    const string FORMULA_COMPONENTS = 'components';


    /**
     * unzip an XBRL fileset .zip into a uniquely-named subfolder of $target_root
     * so several filesets (different periods, different issuers) can be unpacked
     * side by side without overwriting each other.
     *
     * The folder name is derived from the zip basename plus a uniqueness token;
     * pass an explicit token via $unique_suffix to make the path deterministic
     * for tests, otherwise a fresh uniqid() is used.
     *
     * @param string $zip_path the absolute path to the XBRL .zip file
     * @param string $target_root directory under which the new folder is created (created on demand)
     * @param string|null $unique_suffix optional explicit uniqueness token; null => uniqid()
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
        $folder = rtrim($target_root, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . $basename . '_' . $suffix . DIRECTORY_SEPARATOR;
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
     * The instance is the file matching abb-<year>1231.xml (not the _cal, _def,
     * _lab, _pre or .xsd companion files).
     *
     * @param string $zip_path path to the XBRL fileset zip
     * @param string $year     four digit year, e.g. "2013"
     * @return string the instance XML, or an empty string on failure
     */
    function read_instance_xml(string $zip_path, string $year): string
    {
        $result = '';
        $instance_name = 'abb-' . $year . '1231.xml';
        $zip = new ZipArchive();
        $opened = $zip->open($zip_path);
        if ($opened === true) {
            $xml = $zip->getFromName($instance_name);
            if ($xml !== false) {
                $result = $xml;
            }
            $zip->close();
        }
        return $result;
    }

    /**
     * turn a CamelCase XBRL member core into a spaced, readable sector name.
     *
     * "DiscreteAutomationAndMotion" becomes "Discrete Automation and Motion"
     * (the joining word "And" is lower-cased to match the ABB segment naming).
     *
     * @param string $member_core the member name without the trailing "Member"
     * @return string the readable sector name
     */
    private function member_to_name(string $member_core): string
    {
        $spaced = preg_replace('/(?<!^)([A-Z])/', ' $1', $member_core);
        $spaced = trim($spaced);
        $words = explode(' ', $spaced);
        $rebuilt = [];
        foreach ($words as $word) {
            $piece = $word;
            if ($word === 'And') {
                $piece = 'and';
            }
            $rebuilt[] = $piece;
        }
        $result = implode(' ', $rebuilt);
        return $result;
    }

    /**
     * extract the per-sector revenue facts for the given year from the instance.
     *
     * Each returned entry has a SEG_SECTOR (readable name) and a SEG_VALUE
     * (full number as a string, exactly as tagged in the instance).
     *
     * @param string $instance_xml the XBRL instance document
     * @param string $year         four digit year, e.g. "2013"
     * @return array list of [SEG_SECTOR => string, SEG_VALUE => string]
     */
    function extract_segment_sales(string $instance_xml, string $year): array
    {
        $result = [];
        $context_prefix = 'D' . $year . '_OperatingSegmentsMember_';
        $pattern = '/<us-gaap:Revenues[^>]*contextRef="'
            . preg_quote($context_prefix, '/')
            . '([A-Za-z0-9]+)Member"[^>]*>(\d+)<\/us-gaap:Revenues>/';
        $found = preg_match_all($pattern, $instance_xml, $matches, PREG_SET_ORDER);
        if ($found !== false && $found > 0) {
            foreach ($matches as $match) {
                $member_core = $match[1];
                $value = $match[2];
                $sector = $this->member_to_name($member_core);
                $result[] = [
                    self::SEG_SECTOR => $sector,
                    self::SEG_VALUE => $value,
                ];
            }
        }
        return $result;
    }

    /**
     * convert a full revenue value into the CHF-million figure used in the JSON.
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
     * build the complete zukunft.com style data array from the segment sales.
     *
     * Structure follows the existing ABB_<year>.json sample: a time word, the
     * company, the measure, the currency, the scale and one word per sector;
     * one "is a sector" triple per sector; one value per sector plus a total;
     * and one formula stating the total is the sum of the sector sales.
     *
     * @param array  $segments list of [SEG_SECTOR => string, SEG_VALUE => string]
     * @param string $year     four digit year, e.g. "2013"
     * @return array the data structure ready to be encoded as JSON
     */
    function build_data(array $segments, string $year): array
    {
        $source_name = 'ABB XBRL files for ' . $year;
        $source_url = 'https://new.abb.com/investorrelations/calendar-events-and-publications/'
            . 'financial-results-and-presentations/sec-filings/xbrl-' . $year;

        $words = [];
        $words[] = [json_fields::NAME => $year,                json_fields::TYPE_NAME => 'time'];
        $words[] = [json_fields::NAME => self::ISSUER_ABB,     json_fields::TYPE_NAME => 'company'];
        $words[] = [json_fields::NAME => self::MEASURE_SALES,  json_fields::TYPE_NAME => 'measure'];
        $words[] = [json_fields::NAME => self::CURRENCY_CHF,   json_fields::TYPE_NAME => 'currency'];
        $words[] = [json_fields::NAME => self::SCALE_MILLION,  json_fields::TYPE_NAME => 'scale'];

        $triples = [];
        $values = [];
        $components = [];
        $total = 0;

        foreach ($segments as $segment) {
            $sector = $segment[self::SEG_SECTOR];
            $millions = $this->value_to_millions($segment[self::SEG_VALUE]);

            $words[] = [json_fields::NAME => $sector, json_fields::TYPE_NAME => self::SECTOR];

            $triples[] = [
                json_fields::EX_FROM => $sector,
                json_fields::EX_VERB => self::VERB_IS_A,
                json_fields::EX_TO   => self::SECTOR,
            ];

            $values[] = [
                json_fields::WORDS  => [self::ISSUER_ABB, self::MEASURE_SALES, $year,
                                        self::CURRENCY_CHF, self::SCALE_MILLION, $sector, $year],
                json_fields::NUMBER => $millions,
                json_fields::SOURCE => $source_name,
            ];

            $components[] = $sector;
            $total = $total + intval($millions);
        }

        $total_text = strval($total);

        $values[] = [
            json_fields::WORDS  => [self::ISSUER_ABB, self::MEASURE_SALES, $year,
                                    self::CURRENCY_CHF, self::SCALE_MILLION, $year],
            json_fields::NUMBER => $total_text,
            json_fields::SOURCE => $source_name,
        ];

        $formulas = [];
        $formulas[] = [
            json_fields::NAME        => 'ABB total sales ' . $year,
            json_fields::EXPRESSION  => 'ABB sales ' . $year . ' = sum of ABB sales ' . $year . ' per sector',
            self::FORMULA_RESULT     => $total_text,
            self::FORMULA_COMPONENTS => $components,
        ];

        $sources = [];
        $sources[] = [
            json_fields::NAME => $source_name,
            json_fields::URL  => $source_url,
        ];

        $result = [
            json_fields::VERSION   => '0.0.1',
            json_fields::TIME      => date('Y-m-d H:i:s'),
            json_fields::USER      => 'timon',
            json_fields::SELECTION => [self::ISSUER_ABB],
            json_fields::WORDS     => $words,
            json_fields::TRIPLES   => $triples,
            json_fields::FORMULAS  => $formulas,
            json_fields::SOURCES   => $sources,
            json_fields::VALUES    => $values,
            json_fields::VIEWS     => [],
        ];
        return $result;
    }

    /**
     * end-to-end conversion: read the zip, extract the sales, build the data,
     * and return the JSON string. Throws on any failure so the caller can
     * decide how to surface it (CLI exit code, HTTP response, test assertion).
     *
     * @param string $zip_path path to the XBRL fileset zip
     * @param string $year     four digit year, e.g. "2013"
     * @return string a pretty-printed JSON string
     */
    function convert(string $zip_path, string $year): string
    {
        // guard clauses for the two failure modes the CLI script handled
        if (!is_file($zip_path)) {
            throw new RuntimeException("XBRL zip file not found: $zip_path");
        }

        $instance_xml = $this->read_instance_xml($zip_path, $year);
        if ($instance_xml === '') {
            throw new RuntimeException(
                "Could not read instance abb-{$year}1231.xml from $zip_path"
            );
        }
        $segments = $this->extract_segment_sales($instance_xml, $year);
        if (count($segments) === 0) {
            throw new RuntimeException(
                "No operating segment revenues found for $year in $zip_path"
            );
        }
        $data = $this->build_data($segments, $year);
        $result = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        return $result;
    }

    /**
     * convert the XBRL zip into a JSON file. Replaces the CLI main() of the
     * standalone script: the same inputs (zip path, year, optional output
     * path), but instead of writing to STDERR and returning an exit code it
     * throws on error and returns the path of the file actually written.
     *
     * @param string $zip_path path to the XBRL fileset zip
     * @param string $year     four digit year, e.g. "2013"
     * @param string $out_path output JSON path; empty => "ABB_<year>.json" in cwd
     * @return string the absolute or relative path of the written JSON file
     */
    function convert_to_file(string $zip_path, string $year, string $out_path = ''): string
    {
        $json = $this->convert($zip_path, $year);
        $target = $out_path;
        if ($target === '') {
            $target = self::ISSUER_ABB . '_' . $year . '.json';
        }
        $written = file_put_contents($target, $json . "\n");
        if ($written === false) {
            throw new RuntimeException("Could not write output to $target");
        }
        return $target;
    }

}
