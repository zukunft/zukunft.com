<?php

/*

    test/utils/test_api.php - quick internal check of the open api definition versus the code
    -----------------------

    to activate the yaml support on debian use
    sudo apt-get update
    sudo apt-get install php-yaml

    and if needed for the api test
    service apache2 restart


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

use api\formula_api;
use api\phrase_api;
use api\source_api;
use api\triple_api;
use api\verb_api;
use api\view_api;
use api\view_cmp_api;
use api\word_api;
use cfg\formula_type;
use cfg\phrase_type;
use html\html_base;

class test_api extends test_base
{
    // path
    const TEST_ROOT_PATH = '/home/timon/git/zukunft.com/';
    const OPEN_API_PATH = 'src/main/resources/openapi/zukunft_com_api.yaml';

    const API_PATH = 'api/';
    const PHP_DEFAULT_FILENAME = 'index.php';

    public function run(testing $t): void
    {

        // init
        $t->name = 'api->';

        $t->header('Test the open API definition versus the code');

        $test_name = 'check if a controller for each api tag exists';
        $result = '';
        $api_def = yaml_parse_file(self::TEST_ROOT_PATH . self::OPEN_API_PATH);
        if ($api_def == null) {
            $result = false;
        } else {
            $tags = $api_def['tags'];
            foreach ($tags as $tag) {
                $paths = $this->get_paths_of_tag($tag['name'], $api_def);
                foreach ($paths as $path) {
                    // check if at least some controller code exists for each tag
                    $filename = self::TEST_ROOT_PATH . self::API_PATH . $path . '/' . self::PHP_DEFAULT_FILENAME;
                    $ctrl_code = file_get_contents($filename);
                    if ($ctrl_code == null or $ctrl_code == '') {
                        if ($result != '') {
                            $result .= ', ';
                        }
                        $result .= 'api for ' . $path . ' missing';
                    }
                }
            }
        }
        $target = '';
        // TODO add the missing APIs
        $target = 'api for batch missing, api for error missing, api for phraseType missing, api for wordForm missing';
        $t->assert($test_name, $result, $target);

        $test_name = 'check if an api tag for each controller exists';

        // the openapi internal consistency is checked via the online swagger test
    }

    private function get_paths_of_tag(string $tag, array $api_def): array
    {
        $lib = new library();
        $paths = [];
        $api_paths = $api_def['paths'];
        foreach ($api_paths as $path_key => $path) {
            $path_name = $lib->str_right_of($path_key, '/');
            if (str_contains($path_name, '/')) {
                $path_name = $lib->str_left_of($path_name, '/');
            }
            if (array_key_exists('post', $path)) {
                $path_posts = $path['post'];
                if (array_key_exists('tags', $path_posts)) {
                    $path_tags = $path_posts['tags'];
                    foreach ($path_tags as $path_tag) {
                        if ($path_tag == $tag) {
                            if (!in_array($path_name, $paths)) {
                                $paths[] = $path_name;
                            }
                        }
                    }
                }
            }
        }

        return $paths;
    }
}