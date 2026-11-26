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
    every base word that a value uses is declared name-only and every borrowed triple verbatim, because the
    import resolves a phrase only from its own file (see docs/llm/json_structure.md "Self-consistency")


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
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
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
    // the borrowed triples, verbatim as in portfolio_split_calculator.json
    const array BASE_TRIPLES = [
        ['name' => 'zukunft.com system', 'from' => 'zukunft.com', 'verb' => 'is a', 'to' => 'system',
            'description' => 'keyword for system internal triples such as the column definitions'],
        ['name' => 'calculator (system)', 'from' => 'calculator', 'verb' => 'can be', 'to' => 'zukunft.com system'],
        ['name' => 'calculator hint', 'from' => 'calculator (system)', 'verb' => 'has a', 'to' => 'hint'],
        ['name' => 'exchange rate', 'from' => 'rate', 'verb' => 'of', 'to' => 'exchange',
            'description' => 'An exchange rate is the rate at which one currency will be exchanged for another. It is also regarded as the value of one country\'s currency in relation to another currency.',
            'refs' => [['name' => 'Exchange_rate', 'type' => 'wikipedia'], ['name' => 'Q66100', 'type' => 'wikidata']]],
        ['name' => 'bond (finance)', 'from' => 'bond', 'verb' => 'must be one of', 'to' => 'finance',
            'description' => 'the finance reading of the ambiguous word \'bond\': a debt security under which the issuer owes the holders the face value and the interest',
            'refs' => [['name' => 'Bond_(finance)', 'type' => 'wikipedia']]],
        ['name' => 'stock price', 'from' => 'price', 'verb' => 'of', 'to' => 'stock'],
        ['name' => 'bond price', 'from' => 'price', 'verb' => 'of', 'to' => 'bond (finance)',
            'description' => 'the price of a bond in percent of its face value'],
        ['name' => 'face value', 'from' => 'value', 'verb' => 'kind of', 'to' => 'face',
            'description' => 'the nominal amount of a bond on which the interest is paid and which is repaid at maturity'],
        ['name' => 'lot size', 'from' => 'size', 'verb' => 'of', 'to' => 'lot'],
        ['name' => 'holding value', 'from' => 'value', 'verb' => 'of', 'to' => 'holding',
            'description' => 'the market value of one holding in CHF: the shares times the stock price, the face value times the bond price, or the cash amount times the exchange rate'],
        ['from' => 'stock', 'verb' => 'is a', 'to' => 'holding'],
        ['name' => 'bond holding', 'from' => 'bond (finance)', 'verb' => 'is a', 'to' => 'holding'],
        ['from' => 'cash', 'verb' => 'is a', 'to' => 'holding'],
    ];
    // the description of a currency pair at its home (system_unit_test_data/currencies.json), else CURRENCY_PAIR_DESCRIPTION
    const array CURRENCY_PAIR_DESCRIPTIONS = [
        'CHF per USD' => 'the unit of the USD/CHF exchange rate: how many Swiss francs one US dollar buys',
        'CHF per EUR' => 'the unit of the EUR/CHF exchange rate: how many Swiss francs one euro buys',
    ];
    const string CURRENCY_PAIR_DESCRIPTION = 'the unit of the exchange rate: ';
    const string CURRENCY_PAIR_UNIT = ' per unit of ';
    const string CURRENCY_PAIR_TYPE = 'measure_unit';


    /*
     * read
     */

    /**
     * @param string $csv the text of the csv of a bank statement of the positions
     * @return array per position below the header row its cells by the name of their column, without the spaces
     *               around a cell; empty if the csv has no header row that starts with "Asset Class"
     */
    function positions(string $csv): array
    {
        $result = [];
        $header = null;
        foreach (preg_split('/\r\n|\n|\r/', $csv) as $line) {
            $cells = array_map('trim', str_getcsv($line, ',', '"', '\\'));
            if ($header === null) {
                if (($cells[0] ?? '') == self::HEADER_START) {
                    $header = $cells;
                }
            } elseif (implode('', $cells) != '') {
                $pos = [];
                foreach ($header as $i => $col) {
                    // the first column of a name e.g. the exchange rate of the market value and not of the cost
                    if (!array_key_exists($col, $pos)) {
                        $pos[$col] = $cells[$i] ?? '';
                    }
                }
                $result[] = $pos;
            }
        }
        return $result;
    }

    /**
     * @param array $pos one position of the csv (see positions)
     * @return array the holding of the position by the keys of this class e.g. self::KIND; the kind is empty for an
     *               asset class without a kind
     */
    function holding(array $pos): array
    {
        $kind = self::ASSET_CLASS_KINDS[$pos[self::COL_ASSET_CLASS] ?? ''] ?? '';
        $quantity = (float)($pos[self::COL_QUANTITY] ?? 0);
        $price = (float)($pos[self::COL_PRICE] ?? 0);
        $rate = (float)($pos[self::COL_EXCHANGE_RATE] ?? 1) ?: 1.0;
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


    /*
     * convert
     */

    /**
     * @param string $csv the text of the csv of a bank statement of the positions
     * @param array $context the names of the phrases of the portfolio e.g. "example portfolio split"; each holding is
     *                       part of the first one
     * @param string $source the name of the source of every value e.g. the bank statement
     * @param user_message $msg to report a position of an asset class without a kind, which is left out
     * @param string $timestamp the time of the json, now if empty
     * @return string the zukunft.com import json of the holdings
     */
    function convert(string $csv, array $context, string $source, user_message $msg, string $timestamp = ''): string
    {
        $holdings = [];
        foreach ($this->positions($csv) as $pos) {
            $hld = $this->holding($pos);
            if ($hld[self::KIND] == '') {
                $msg->add(msg_id::IMPORT_UNKNOWN_ELEMENT, [msg_id::VAR_NAME => $pos[self::COL_ASSET_CLASS] ?? '']);
            } else {
                $holdings[] = $hld;
            }
        }
        $json = $this->header($timestamp);
        $json[json_fields::SELECTION] = $context;
        $json[json_fields::WORDS] = $this->words($holdings, $context);
        $json[json_fields::TRIPLES] = $this->triples($holdings, $context);
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
     * @return array the borrowed triples, the currency pairs and per holding its kind, its hint, the portfolio and
     *               its walk step
     */
    private function triples(array $holdings, array $context): array
    {
        $result = self::BASE_TRIPLES;
        foreach ($this->foreign_currencies($holdings) as $ref => $currencies) {
            foreach ($currencies as $currency) {
                $result[] = $this->currency_pair($ref, $currency);
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
     * @return array the triple of the unit of the exchange rate, with the description of its home
     */
    private function currency_pair(string $ref, string $currency): array
    {
        $name = $ref . self::PER . $currency;
        return [
            json_fields::EX_FROM => $ref,
            json_fields::EX_VERB => verbs::PER_NAME,
            json_fields::EX_TO => $currency,
            json_fields::TYPE_NAME => self::CURRENCY_PAIR_TYPE,
            json_fields::DESCRIPTION => self::CURRENCY_PAIR_DESCRIPTIONS[$name]
                ?? self::CURRENCY_PAIR_DESCRIPTION . $ref . self::CURRENCY_PAIR_UNIT . $currency,
        ];
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
