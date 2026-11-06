<?php

/*

    shared/const/files.php - resource file names used in backend and frontend
    ----------------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\const;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

class files
{

    /*
     * types and extensions
     */

    const string JSON = '.json';
    const string YAML = '.yaml';
    const string CSV = '.csv';
    const string CSS = '.css';
    const string SVG = '.svg';
    const string JS = '.js';


    /*
     * path
     */

    const string RESOURCE_PATH = paths::MAIN . 'resources' . DIRECTORY_SEPARATOR;
    const string MESSAGE_PATH = self::RESOURCE_PATH . 'messages' . DIRECTORY_SEPARATOR;
    const string REL_SOURCE_PATH = DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    const string REL_MAIN_PATH = self::REL_SOURCE_PATH . 'main' . DIRECTORY_SEPARATOR;
    const string REL_RESOURCE_PATH = self::REL_MAIN_PATH . 'resources' . DIRECTORY_SEPARATOR;
    const string STYLE_PATH = self::REL_RESOURCE_PATH . 'style' . DIRECTORY_SEPARATOR;
    const string SCRIPT_PATH = self::REL_RESOURCE_PATH . 'script' . DIRECTORY_SEPARATOR;


    /*
     * system config
     */

    // the system configuration values that an admin can change without a code update
    const string CONFIG_YAML = self::RESOURCE_PATH . 'config' . self::YAML;

    // the predicates as a zukunft.com user import json; the home of every verb name and code id
    const string VERBS = self::RESOURCE_PATH . 'verbs' . self::JSON;

    // the system views as a zukunft.com user import json
    const string SYSTEM_VIEWS_FILE = 'system_views' . self::JSON;
    const string SYSTEM_VIEWS = self::MESSAGE_PATH . self::SYSTEM_VIEWS_FILE;
    const string BASE_VIEWS_FILE = 'base_views' . self::JSON;
    const string BASE_VIEWS = self::MESSAGE_PATH . self::BASE_VIEWS_FILE;
    const string TRANSLATION_PATH = self::RESOURCE_PATH . 'translations' . DIRECTORY_SEPARATOR;
    // the translation of a document of /docs/ per language e.g. concept to konzept_de (see markdown::doc_translations)
    const string DOC_TRANSLATIONS = self::TRANSLATION_PATH . 'docs' . self::CSV;

    // for html
    const string STYLE_HTML = self::STYLE_PATH . 'style_html' . self::CSS;
    const string STYLE_FALLBACK = self::STYLE_PATH . 'style' . self::CSS;
    const string STYLE_BS = paths::EXT_LIB_BS_CSS . 'bootstrap' . self::CSS;
    const string STYLE_FONT = paths::EXT_LIB_FONT_CSS . 'all' . self::CSS;
    const string LOGO = paths::IMAGE_RES . 'ZUKUNFT_logo' . self::SVG;
    // the images on the server e.g. the logo of a report, which is a jpeg because a pdf can embed it as it is
    const string IMAGE_PATH = self::RESOURCE_PATH . 'images' . DIRECTORY_SEPARATOR;
    const string REPORT_LOGO_DEFAULT = self::IMAGE_PATH . 'logo_xs.jpg';
    // the fonts that a pdf report embeds as a subset, so that it can show any char of a page e.g. the CO₂;
    // DejaVu Sans by default and Noto Sans as an alternative (see the LICENSE files in the fonts folder)
    const string FONT_PATH = self::RESOURCE_PATH . 'fonts' . DIRECTORY_SEPARATOR;
    const string PDF_FONT_DEJAVU = self::FONT_PATH . 'DejaVuSans.ttf';
    const string PDF_FONT_DEJAVU_BOLD = self::FONT_PATH . 'DejaVuSans-Bold.ttf';
    const string PDF_FONT_NOTO = self::FONT_PATH . 'NotoSans-Regular.ttf';
    const string PDF_FONT_NOTO_BOLD = self::FONT_PATH . 'NotoSans-Bold.ttf';
    // the only scripts of the html frontend: the calculator pages repeat their calculation in the
    // browser, the calculator and the formula editor build the editor and the json calculator page the
    // page from the phrase data, if config.yaml allows javascript for the calculator (see docs/llm/frontend.md)
    const string CALCULATOR_SCRIPT = self::SCRIPT_PATH . 'calculator' . self::JS;
    const string CALCULATOR_EDITOR_SCRIPT = self::SCRIPT_PATH . 'calculator_editor' . self::JS;
    const string CALCULATOR_JSON_SCRIPT = self::SCRIPT_PATH . 'calculator_json' . self::JS;
    const string FORMULA_EDITOR_SCRIPT = self::SCRIPT_PATH . 'formula_editor' . self::JS;
    const string FORMULA_EDITOR_STYLE = self::STYLE_PATH . 'formula_editor' . self::CSS;
    const string CALCULATOR_EDITOR_STYLE = self::STYLE_PATH . 'calculator_editor' . self::CSS;
    const string TABLE_EDITOR_SCRIPT = self::SCRIPT_PATH . 'table_editor' . self::JS;
    const string TABLE_EDITOR_STYLE = self::STYLE_PATH . 'table_editor' . self::CSS;

}
