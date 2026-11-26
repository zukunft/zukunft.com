<?php

/*

    model/import/convert_portfolio_csv.php - convert the csv of a bank statement of the positions to a zukunft.com import
    --------------------------------------

    TODO Prio 1 should be based on phrases without specific code

    a bank statement of the positions has some lines about the portfolio, a header row that starts with
    "Asset Class" and one row per position below it; each position becomes a holding of the portfolio split
    page with its quantity, its price, its currency, the exchange rate and the market value of the bank,
    written like the holdings of use_cases/portfolio_split_calculator.json:
    - the word of a holding is the instrument number (the ISIN or the account number of cash) and the
      instrument name its description and its calculator hint, because a name can repeat
    - the asset class gives the kind: a stock, a bond or cash; a bond whose market value is not its face
      value times its price in percent, e.g. a bond fund in units, is a stock
    - the csv has no lot size, so each holding gets the assumed lot size of its kind
    - the holdings follow each other from the largest lot value to the smallest, like the walk of the page
    every base word that a value uses is declared name-only and every borrowed triple is copied verbatim from its
    home file, because the import resolves a phrase only from its own file (see docs/llm/json_structure.md
    "Self-consistency")


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
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use DateTime;
use DateTimeInterface;

class convert_portfolio_csv
{

    // the columns of the csv of the bank that the conversion reads; a name that is used twice e.g. "Exchange Rate"
    // is read from its first column, which belongs to the market value
    const string COL_ASSET_CLASS = 'Asset Class';
    const string COL_INSTRUMENT_NAME = 'Instrument Name';
    const string COL_INSTRUMENT = 'Instrument';
    const string COL_QUANTITY = 'Quantity';
    const string COL_CURRENCY = 'Currency';
    const string COL_PRICE = 'Price';
    const string COL_PRICE_CURRENCY = 'Price Curr';
    const string COL_EXCHANGE_RATE = 'Exchange Rate';
    const string COL_MARKET_VALUE = 'Market Value';
    const string COL_REF_CURRENCY = 'Ref. Curr';
    // the first cell of the header row of the positions
    const string HEADER_START = self::COL_ASSET_CLASS;
    // the cells that a holding needs, so a position with one of them empty is left out and reported; an empty price
    // currency is the currency of the position and an empty instrument name only leaves the description empty
    const array COLS_REQUIRED = [self::COL_INSTRUMENT, self::COL_QUANTITY, self::COL_CURRENCY, self::COL_PRICE,
        self::COL_EXCHANGE_RATE, self::COL_MARKET_VALUE, self::COL_REF_CURRENCY];
    // the cells that must be a number
    const array COLS_NUMBER = [self::COL_QUANTITY, self::COL_PRICE, self::COL_EXCHANGE_RATE, self::COL_MARKET_VALUE];

    // the keys of a holding (see holding)
    const string KIND = 'kind';
    const string NAME = 'name';
    const string DESCRIPTION = 'description';
    const string QUANTITY = 'quantity';
    const string PRICE = 'price';
    const string CURRENCY = 'currency';
    const string PRICE_CURRENCY = 'price currency';
    const string EXCHANGE_RATE = 'exchange rate';
    const string MARKET_VALUE = 'market value';
    const string REF_CURRENCY = 'reference currency';

    // the kinds of a holding, which are the phrases of portfolio_split_calculator.json
    const string KIND_STOCK = 'stock';
    const string KIND_BOND = 'bond (finance)';
    const string KIND_CASH = 'cash';
    // the kind of each asset class of the bank; a fund of an alternative investment has a price per unit like a stock
    const array ASSET_CLASS_KINDS = [
        'Equities and similar positions' => self::KIND_STOCK,
        'Alternative investments' => self::KIND_STOCK,
        'Bonds and similar positions' => self::KIND_BOND,
        'Cash and short-term investments' => self::KIND_CASH,
    ];
    // the assumed lot size of each kind, because the csv has none: one share, 1000 of the face value and one cent
    const array LOT_SIZES = [
        self::KIND_STOCK => 1.0,
        self::KIND_BOND => 1000.0,
        self::KIND_CASH => 0.01,
    ];
    // a bond price is in percent of the face value
    const float PERCENT = 100.0;
    // the decimals of a number of the json, more than the bank writes, so that a number is never rounded
    const int DECIMALS = 10;

    // the phrases of the values of a holding (see portfolio_split_calculator.json)
    const string SHARES = 'shares';
    const string ORIGINAL = 'original';
    const string STOCK_PRICE = 'stock price';
    const string BOND_PRICE = 'bond price';
    const string FACE_VALUE = 'face value';
    const string LOT_SIZE = 'lot size';
    const string HOLDING_VALUE = 'holding value';
    const string EXCHANGE_RATE_PHRASE = 'exchange rate';
    const string PCT = 'percent';
    const string ASSUMED = 'assumed';
    const string EXPECTED = 'expected';
    const string HINT = 'calculator hint';
    const string HINT_SUFFIX = ' hint';
    const string PER = ' per ';
    // the base words that the values and triples use, declared name-only (see docs/llm/json_structure.md)
    const array BASE_WORDS = [
        'original', 'value', 'price', 'size', 'lot', 'face', 'holding', 'stock', 'shares', 'bond', 'finance', 'cash',
        'rate', 'exchange', 'expected', 'assumed', 'percent', 'zukunft.com', 'system', 'calculator', 'hint',
    ];
    // the from, verb and to of the borrowed triples, which are copied verbatim from their home file
    // (files::PORTFOLIO_SPLIT_CALCULATOR_FILE), so that the import of the json never changes them
    const array BASE_TRIPLES = [
        [words::THIS_SYSTEM, verbs::IS_NAME, words::SYSTEM],
        ['calculator', verbs::CAN_BE_NAME, 'zukunft.com system'],
        [triples::SYSTEM_CALCULATOR, verbs::HAS_NAME, 'hint'],
        ['rate', verbs::OF_NAME, 'exchange'],
        ['bond', verbs::MUST_BE_ONE_OF_NAME, 'finance'],
        ['price', verbs::OF_NAME, self::KIND_STOCK],
        ['price', verbs::OF_NAME, self::KIND_BOND],
        [words::VALUE, verbs::KIND_OF_NAME, 'face'],
        [words::SIZE, verbs::OF_NAME, 'lot'],
        [words::VALUE, verbs::OF_NAME, 'holding'],
        [self::KIND_STOCK, verbs::IS_NAME, 'holding'],
        [self::KIND_BOND, verbs::IS_NAME, 'holding'],
        [self::KIND_CASH, verbs::IS_NAME, 'holding'],
    ];
    // the description of a currency pair that its home file does not have
    const string CURRENCY_PAIR_DESCRIPTION = 'the unit of the exchange rate: ';
    const string CURRENCY_PAIR_UNIT = ' per unit of ';


    /*
     * read
     */

    /**
     * @param string $csv the text of the csv of a bank statement of the positions
     * @param string $source the name of the csv e.g. the bank statement, which a message names as the place
     * @param user_message $msg to report a csv without the header row and a line that is left out, because it has
     *                          another number of cells than the header row e.g. by a comma in a name without quotes
     * @return array per line number of the csv (from 1) of a position below the header row its cells by the name of
     *               their column, without the spaces around a cell; empty if the csv has no header row that starts
     *               with "Asset Class"
     */
    function positions(string $csv, string $source, user_message $msg): array
    {
        $result = [];
        $header = null;
        foreach (preg_split('/\r\n|\n|\r/', $csv) as $i => $line) {
            $cells = array_map('trim', str_getcsv($line, ',', '"', '\\'));
            if ($header === null) {
                if (($cells[0] ?? '') == self::HEADER_START) {
                    $header = $cells;
                }
            } elseif (implode('', $cells) != '') {
                $line_nbr = $i + 1;
                if (count($cells) != count($header)) {
                    $msg->add(msg_id::IMPORT_CSV_LINE_CELLS, [
                        msg_id::VAR_ID => (string)$line_nbr,
                        msg_id::VAR_SOURCE_NAME => $source,
                        msg_id::VAR_VALUE_COUNT => (string)count($cells),
                        msg_id::VAR_VALUE_COUNT_CHK => (string)count($header),
                    ]);
                } else {
                    $result[$line_nbr] = $this->cells_by_column($header, $cells);
                }
            }
        }
        if ($header === null) {
            $msg->add(msg_id::IMPORT_CSV_NO_HEADER, [
                msg_id::VAR_SOURCE_NAME => $source,
                msg_id::VAR_FIELD_NAME => self::HEADER_START,
            ]);
        }
        return $result;
    }

    /**
     * @param array $pos one position of the csv that position_is_valid has accepted (see positions)
     * @return array the holding of the position by the keys of this class e.g. self::KIND; the kind is empty for an
     *               asset class without a kind
     */
    function holding(array $pos): array
    {
        $kind = self::ASSET_CLASS_KINDS[$pos[self::COL_ASSET_CLASS] ?? ''] ?? '';
        $quantity = (float)($pos[self::COL_QUANTITY] ?? 0);
        $price = (float)($pos[self::COL_PRICE] ?? 0);
        $rate = (float)($pos[self::COL_EXCHANGE_RATE] ?? 1);
        $market = (float)($pos[self::COL_MARKET_VALUE] ?? 0);
        // a bond priced per unit e.g. a bond fund has the value of the units and not of a percent of a face value
        if ($kind == self::KIND_BOND and $market > 0
            and abs($quantity * $price * $rate - $market) < abs($quantity * $price / self::PERCENT * $rate - $market)) {
            $kind = self::KIND_STOCK;
        }
        return [
            self::KIND => $kind,
            self::NAME => $pos[self::COL_INSTRUMENT] ?? '',
            self::DESCRIPTION => $pos[self::COL_INSTRUMENT_NAME] ?? '',
            self::QUANTITY => $quantity,
            self::PRICE => $price,
            self::CURRENCY => $pos[self::COL_CURRENCY] ?? '',
            self::PRICE_CURRENCY => ($pos[self::COL_PRICE_CURRENCY] ?? '') ?: ($pos[self::COL_CURRENCY] ?? ''),
            self::EXCHANGE_RATE => $rate,
            self::MARKET_VALUE => $market,
            self::REF_CURRENCY => $pos[self::COL_REF_CURRENCY] ?? '',
        ];
    }

    /**
     * @param array $header the cells of the header row
     * @param array $cells the cells of a line with as many cells as the header row
     * @return array the cells by the name of their column; of a name that is used twice e.g. "Exchange Rate" the
     *               first one, which belongs to the market value and not to the cost
     */
    private function cells_by_column(array $header, array $cells): array
    {
        $result = [];
        foreach ($header as $i => $col) {
            if (!array_key_exists($col, $result)) {
                $result[$col] = $cells[$i];
            }
        }
        return $result;
    }

    /**
     * @param array $pos one position of the csv (see positions)
     * @param int $line_nbr the line of the position in the csv, which a message names
     * @param string $source the name of the csv, which a message names
     * @param user_message $msg to report each reason why the position is left out
     * @return bool true if the asset class has a kind, every cell that a holding needs is filled, each number cell is
     *              a number and the exchange rate is above zero
     */
    private function position_is_valid(array $pos, int $line_nbr, string $source, user_message $msg): bool
    {
        $vars = [
            msg_id::VAR_NAME => $pos[self::COL_INSTRUMENT] ?? '',
            msg_id::VAR_ID => (string)$line_nbr,
            msg_id::VAR_SOURCE_NAME => $source,
        ];
        $asset_class = $pos[self::COL_ASSET_CLASS] ?? '';
        $result = array_key_exists($asset_class, self::ASSET_CLASS_KINDS);
        if (!$result) {
            $msg->add(msg_id::IMPORT_CSV_UNKNOWN_KIND, $vars + [msg_id::VAR_TYPE => $asset_class]);
        }
        foreach (self::COLS_REQUIRED as $col) {
            $cell = $pos[$col] ?? '';
            $cell_vars = $vars + [msg_id::VAR_FIELD_NAME => $col, msg_id::VAR_VALUE => $cell];
            if ($cell == '') {
                $msg->add(msg_id::IMPORT_CSV_CELL_EMPTY, $cell_vars);
                $result = false;
            } elseif (in_array($col, self::COLS_NUMBER) and !is_numeric($cell)) {
                $msg->add(msg_id::IMPORT_CSV_CELL_NOT_NUMBER, $cell_vars);
                $result = false;
            } elseif ($col == self::COL_EXCHANGE_RATE and (float)$cell <= 0) {
                $msg->add(msg_id::IMPORT_CSV_RATE_NOT_POSITIVE, $cell_vars);
                $result = false;
            }
        }
        return $result;
    }


    /*
     * convert
     */

    /**
     * @param string $csv the text of the csv of a bank statement of the positions
     * @param array $context the names of the phrases of the portfolio e.g. "example portfolio split"; each holding is
     *                       part of the first one
     * @param string $source the name of the source of every value e.g. the bank statement, which a message names as
     *                       the csv
     * @param user_message $msg to report a csv without the header row and each position that is left out with its
     *                          line and the reason
     * @param string $timestamp the time of the json, now if empty
     * @return string the zukunft.com import json of the holdings
     */
    function convert(string $csv, array $context, string $source, user_message $msg, string $timestamp = ''): string
    {
        $holdings = [];
        foreach ($this->positions($csv, $source, $msg) as $line_nbr => $pos) {
            if ($this->position_is_valid($pos, $line_nbr, $source, $msg)) {
                $holdings[] = $this->holding($pos);
            }
        }
        $json = $this->header($timestamp);
        $json[json_fields::SELECTION] = $context;
        $json[json_fields::WORDS] = $this->words($holdings, $context);
        $json[json_fields::TRIPLES] = $this->triples($holdings, $context, $msg);
        $json[json_fields::SOURCES] = [[json_fields::NAME => $source]];
        $json[json_fields::VALUES] = $this->values($holdings, $source);
        return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array $holdings the holdings of the csv (see holding)
     * @param array $context the names of the phrases of the portfolio
     * @return array the words of the file: the context, the base words name-only, the currencies and the holdings
     */
    private function words(array $holdings, array $context): array
    {
        $names = array_merge($context, self::BASE_WORDS, $this->currencies($holdings));
        $result = [];
        foreach (array_unique($names) as $name) {
            $result[] = [json_fields::NAME => $name];
        }
        foreach ($holdings as $hld) {
            $result[] = [json_fields::NAME => $hld[self::NAME], json_fields::DESCRIPTION => $hld[self::DESCRIPTION]];
        }
        return $result;
    }

    /**
     * @param array $holdings the holdings of the csv (see holding)
     * @param array $context the names of the phrases of the portfolio
     * @param user_message $msg to report a home file or a borrowed triple that cannot be read
     * @return array the borrowed triples, the currency pairs and per holding its kind, its hint, the portfolio and
     *               its walk step
     */
    private function triples(array $holdings, array $context, user_message $msg): array
    {
        $result = [];
        $home = $this->home_triples($msg);
        // without the home file, which home_triples has reported, every borrowed triple would be reported as missing
        if ($home !== null) {
            $result = $this->borrowed_triples($home, $msg);
        }
        // the currencies are the keys of the exchange rates
        foreach ($this->foreign_currencies($holdings) as $ref => $rates) {
            foreach (array_keys($rates) as $currency) {
                $result[] = $this->currency_pair($ref, $currency, $home ?? []);
            }
        }
        $portfolio = $context[0] ?? '';
        foreach ($holdings as $hld) {
            $name = $hld[self::NAME];
            $result[] = [json_fields::EX_FROM => $name, json_fields::EX_VERB => verbs::IS_NAME, json_fields::EX_TO => $hld[self::KIND]];
            $result[] = [json_fields::NAME => $name . self::HINT_SUFFIX, json_fields::DESCRIPTION => $hld[self::DESCRIPTION],
                json_fields::EX_FROM => $name, json_fields::EX_VERB => verbs::HAS_NAME, json_fields::EX_TO => self::HINT];
            if ($portfolio != '') {
                $result[] = [json_fields::EX_FROM => $name, json_fields::EX_VERB => verbs::PART_NAME, json_fields::EX_TO => $portfolio];
            }
        }
        // the walk visits the holdings from the largest lot value to the smallest, on a tie in the order of the bank
        $walk = $holdings;
        usort($walk, fn(array $a, array $b) => $this->lot_value($b) <=> $this->lot_value($a));
        for ($i = 1; $i < count($walk); $i++) {
            $result[] = [json_fields::EX_FROM => $walk[$i][self::NAME], json_fields::EX_VERB => verbs::FOLLOW_NAME,
                json_fields::EX_TO => $walk[$i - 1][self::NAME]];
        }
        return $result;
    }

    /**
     * @param array $holdings the holdings of the csv (see holding)
     * @param string $source the name of the source of every value
     * @return array the values of the holdings and one exchange rate per foreign currency
     */
    private function values(array $holdings, string $source): array
    {
        $result = [];
        foreach ($this->foreign_currencies($holdings) as $ref => $currencies) {
            foreach ($currencies as $currency => $rate) {
                $result[] = $this->value([self::EXCHANGE_RATE_PHRASE, $ref . self::PER . $currency], $rate, $source);
            }
        }
        foreach ($holdings as $hld) {
            $name = $hld[self::NAME];
            $kind = $hld[self::KIND];
            if ($kind == self::KIND_STOCK) {
                $result[] = $this->value([$name, self::SHARES, self::ORIGINAL], $hld[self::QUANTITY], $source);
                $result[] = $this->value([$name, self::STOCK_PRICE, $hld[self::PRICE_CURRENCY]], $hld[self::PRICE], $source);
                $lot_unit = self::SHARES;
            } elseif ($kind == self::KIND_BOND) {
                $result[] = $this->value([$name, self::FACE_VALUE, $hld[self::CURRENCY], self::ORIGINAL], $hld[self::QUANTITY], $source);
                $result[] = $this->value([$name, self::BOND_PRICE, self::PCT], $hld[self::PRICE] / self::PERCENT, $source);
                $lot_unit = $hld[self::CURRENCY];
            } else {
                $result[] = $this->value([$name, $hld[self::CURRENCY], self::ORIGINAL], $hld[self::QUANTITY], $source);
                $lot_unit = $hld[self::CURRENCY];
            }
            // the csv has no lot size, so it is an assumption without a source
            $result[] = $this->value([$name, self::LOT_SIZE, $lot_unit, self::ASSUMED], self::LOT_SIZES[$kind], '');
            // the market value of the bank checks the holding value that the page calculates
            if ($hld[self::MARKET_VALUE] > 0) {
                $result[] = $this->value([$name, self::HOLDING_VALUE, $hld[self::REF_CURRENCY], self::EXPECTED],
                    $hld[self::MARKET_VALUE], $source);
            }
        }
        return $result;
    }

    /**
     * @param array $words the phrase names of the value
     * @param float $number the number of the value
     * @param string $source the name of the source or empty for an assumption
     * @return array the value of the import json
     */
    private function value(array $words, float $number, string $source): array
    {
        $result = [json_fields::WORDS => $words, json_fields::NUMBER => (string)round($number, self::DECIMALS)];
        if ($source != '') {
            $result[json_fields::SOURCE_NAME] = $source;
        }
        return $result;
    }

    /**
     * @param array $hld a holding (see holding)
     * @return float the value of one lot in the reference currency: the lot size times the value of one unit
     */
    private function lot_value(array $hld): float
    {
        $unit = match ($hld[self::KIND]) {
            self::KIND_STOCK => $hld[self::PRICE],
            self::KIND_BOND => $hld[self::PRICE] / self::PERCENT,
            default => 1.0,
        };
        return $unit * $hld[self::EXCHANGE_RATE] * self::LOT_SIZES[$hld[self::KIND]];
    }

    /**
     * @param array $holdings the holdings of the csv (see holding)
     * @return array the names of the currencies of the holdings and of their reference currencies, each once
     */
    private function currencies(array $holdings): array
    {
        $result = [];
        foreach ($holdings as $hld) {
            foreach ([$hld[self::CURRENCY], $hld[self::PRICE_CURRENCY], $hld[self::REF_CURRENCY]] as $currency) {
                if ($currency != '' and !in_array($currency, $result)) {
                    $result[] = $currency;
                }
            }
        }
        return $result;
    }

    /**
     * @param array $holdings the holdings of the csv (see holding)
     * @return array per reference currency the exchange rate of the bank of each other currency, the first one of a
     *               currency, e.g. ['CHF' => ['EUR' => 0.9323]]
     */
    private function foreign_currencies(array $holdings): array
    {
        $result = [];
        foreach ($holdings as $hld) {
            $ref = $hld[self::REF_CURRENCY];
            $currency = $hld[self::PRICE_CURRENCY];
            if ($ref != '' and $currency != $ref and !isset($result[$ref][$currency])) {
                $result[$ref][$currency] = $hld[self::EXCHANGE_RATE];
            }
        }
        return $result;
    }

    /**
     * @param string $ref the reference currency e.g. CHF
     * @param string $currency the other currency e.g. GBP
     * @param array $home the triples of the home file (see home_triples)
     * @return array the triple of the unit of the exchange rate: verbatim from the home file, or with the general
     *               description if the home file does not have this currency pair
     */
    private function currency_pair(string $ref, string $currency, array $home): array
    {
        $result = $this->home_triple($home, [$ref, verbs::PER_NAME, $currency]);
        if ($result === null) {
            $result = [
                json_fields::EX_FROM => $ref,
                json_fields::EX_VERB => verbs::PER_NAME,
                json_fields::EX_TO => $currency,
                json_fields::TYPE_NAME => phrase_types::MEASURE,
                json_fields::DESCRIPTION => self::CURRENCY_PAIR_DESCRIPTION . $ref . self::CURRENCY_PAIR_UNIT . $currency,
            ];
        }
        return $result;
    }

    /**
     * @param user_message $msg to report a home file that cannot be read, which is a problem of the installation
     * @return array|null the triples of the home file of the borrowed triples, null if the file cannot be read
     */
    private function home_triples(user_message $msg): ?array
    {
        $result = null;
        $home = null;
        if (is_readable(files::PORTFOLIO_SPLIT_CALCULATOR_FILE)) {
            $home = json_decode(file_get_contents(files::PORTFOLIO_SPLIT_CALCULATOR_FILE), true);
        }
        if (is_array($home) and is_array($home[json_fields::TRIPLES] ?? null)) {
            $result = $home[json_fields::TRIPLES];
        } else {
            log_err_msg('the borrowed triples of the bank csv cannot be read from the triples of '
                . files::PORTFOLIO_SPLIT_CALCULATOR_FILE, $msg);
        }
        return $result;
    }

    /**
     * @param array $home the triples of the home file (see home_triples)
     * @param user_message $msg to report a borrowed triple that the home file does not have
     * @return array the borrowed triples verbatim from the home file in the order of self::BASE_TRIPLES
     */
    private function borrowed_triples(array $home, user_message $msg): array
    {
        $result = [];
        foreach (self::BASE_TRIPLES as $key) {
            $trp = $this->home_triple($home, $key);
            if ($trp === null) {
                log_err_msg('the borrowed triple "' . implode(' ', $key) . '" of the bank csv is missing in '
                    . files::PORTFOLIO_SPLIT_CALCULATOR_FILE, $msg);
            } else {
                $result[] = $trp;
            }
        }
        return $result;
    }

    /**
     * @param array $home the triples of the home file (see home_triples)
     * @param array $key the from, verb and to of a triple
     * @return array|null the first triple of the home file with this from, verb and to, null if it has none
     */
    private function home_triple(array $home, array $key): ?array
    {
        $result = null;
        foreach ($home as $trp) {
            $trp_key = [$trp[json_fields::EX_FROM] ?? '', $trp[json_fields::EX_VERB] ?? '', $trp[json_fields::EX_TO] ?? ''];
            if ($result === null and $trp_key == $key) {
                $result = $trp;
            }
        }
        return $result;
    }

    /**
     * @param string $timestamp the time of the json, now if empty
     * @return array the head of the import json like convert_wikipedia_table::header
     */
    private function header(string $timestamp): array
    {
        return [
            json_fields::POD => POD_NAME,
            json_fields::VERSION => def::PRG_VERSION,
            json_fields::DATA_VERSION => def::DATA_VERSION_INIT,
            json_fields::TIME => $timestamp == '' ? new DateTime()->format(DateTimeInterface::ATOM) : $timestamp,
        ];
    }

}
