<?php

/*

    cfg/ref/ref_link_conceptnet.php - read the 'is a' concepts of a name from conceptnet
    -------------------------------

    the add word button of a search asks conceptnet which groups the new word belongs to e.g.
    "Hamburg" is a "city", so that the user can confirm these triples together with the new word


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\ref;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_IMPORT . 'import_wikidata.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;

/**
 * $abbr $cpn
 */
class ref_link_conceptnet
{

    // the query api of conceptnet for the 'is a' edges of an english concept
    // e.g. https://api.conceptnet.io/query?start=/c/en/hamburg&rel=/r/IsA&limit=50
    const string QUERY_URL = 'https://api.conceptnet.io/query?start=/c/en/';
    const string QUERY_IS_A = '&rel=/r/IsA&limit=';
    // the 'is a' edges that are read at most, because a few suggestions are enough for the user to select
    const int MAX_EDGES = 50;
    // a concept key is the lower case name with an underscore for each space e.g. "new_york"
    const string NAME_SPACE = ' ';
    const string CONCEPT_SPACE = '_';
    // english only, like the english wikipedia of ref_link_wikipedia::SUMMARY_URL
    const string LANGUAGE = 'en';
    // a generic client name, because conceptnet needs no contact of the pod
    const string HTTP_HEADER = 'User-Agent: zukunft.com';
    // the fields of the answer json
    const string CN_EDGES = 'edges';
    const string CN_END = 'end';
    const string CN_LABEL = 'label';
    const string CN_LANGUAGE = 'language';

    /**
     * @param string $name the name of the new word e.g. "Hamburg"
     * @param user_message $msg to tell the user that conceptnet could not be read
     * @return array the names of the 'is a' concepts e.g. ["city", "port"], empty if conceptnet knows none
     */
    function is_a_names(string $name, user_message $msg): array
    {
        $url = $this->is_a_url($name);
        $http = ['header' => self::HTTP_HEADER, 'timeout' => import_wikidata::READ_TIMEOUT];
        $context = stream_context_create(['http' => $http]);
        // the @ suppresses the php warning of a failed request, which the user gets as message instead
        import_wikidata::request_clear();
        // never interrupted by the note timer of a long import
        $json = import::without_note_timer(fn() => @file_get_contents($url, false, $context));
        $answer = [];
        if ($json !== false) {
            $answer = json_decode($json, true);
        } else {
            // the reason for the developer e.g. a http 502, the user gets the message
            log_warning('the conceptnet request for ' . $name . ' via ' . $url . ' failed with '
                . import_wikidata::request_failure(), 'ref_link_conceptnet->is_a_names');
        }
        if (!is_array($answer) or !array_key_exists(self::CN_EDGES, $answer)) {
            $msg->add(msg_id::CONCEPTNET_READ_FAILED, [msg_id::VAR_NAME => $name]);
            $answer = [];
        }
        return $this->names_of_answer($answer, $name);
    }

    /**
     * @param string $name the name of the new word e.g. "Hamburg"
     * @return string the url of the 'is a' query e.g. https://api.conceptnet.io/query?start=/c/en/hamburg&rel=/r/IsA&limit=50
     */
    private function is_a_url(string $name): string
    {
        return self::QUERY_URL . rawurlencode($this->concept_key($name)) . self::QUERY_IS_A . self::MAX_EDGES;
    }

    /**
     * @param string $name the name of a word e.g. "New York"
     * @return string the key of the conceptnet concept e.g. "new_york", which is also the external key of the reference
     */
    function concept_key(string $name): string
    {
        return strtolower(str_replace(self::NAME_SPACE, self::CONCEPT_SPACE, $name));
    }

    /**
     * public only so that the unit tests can check it with a given answer (see ref_tests)
     *
     * @param array $answer the json of the conceptnet query
     * @param string $name the name of the new word, which is never its own parent
     * @return array the english end labels of the 'is a' edges, each once e.g. ["city", "port"]
     */
    function names_of_answer(array $answer, string $name): array
    {
        $result = [];
        foreach ($answer[self::CN_EDGES] ?? [] as $edge) {
            $end = $edge[self::CN_END] ?? [];
            $label = (string)($end[self::CN_LABEL] ?? '');
            $is_english = (($end[self::CN_LANGUAGE] ?? '') == self::LANGUAGE);
            if ($is_english and $this->is_new_parent($label, $name, $result)) {
                $result[] = $label;
            }
        }
        return $result;
    }

    /**
     * @param string $label the end label of an 'is a' edge e.g. "city"
     * @param string $name the name of the new word e.g. "Hamburg"
     * @param array $names the parents found until now
     * @return bool true if the label is a parent that is not yet suggested
     */
    private function is_new_parent(string $label, string $name, array $names): bool
    {
        $is_other = ($label != '' and strcasecmp($label, $name) != 0);
        return ($is_other and !in_array($label, $names));
    }

}
