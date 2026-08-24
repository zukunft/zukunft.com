<?php

/*

    test/unit/html/verb.php - testing of the html frontend functions for verb
    -----------------------
  

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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object as data_object_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class verb_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $mtr;
        $html = new html_base();
        $t_vrb = new test_verbs($t);
        $t_trp = new test_triples($t);
        $msg = new user_message();

        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => word_names::ZH_ID];

        // start the test section (ts)
        $ts = 'unit ui html verb list ';
        $t->header($ts);

        $vrb = new verb($t_vrb->verb()->api_json());
        $test_page = $html->text_h2('Verb display test');
        $test_page .= 'with tooltip: ' . $vrb->name_tip() . '<br>';
        $test_page .= 'with link: ' . $vrb->name_link() . '<br>';
        $test_page .= $t->dsp_title_named_edit($vrb, $msg);

        // the selector to pick a verb e.g. for the verb of a triple
        $form = 'verb_ui_test';
        $from_rows = 'verb selector: ' . '<br>';
        $from_rows .= $t_vrb->list_all_ui($msg)->selector($form, verbs::IS_ID) . '<br>';
        $test_page .= $html->form($form, $from_rows);

        $t->html_page_test($test_page, 'verb', 'verb', $msg, $base_url, $lan);

        $t->subheader($ts . 'show fields');

        // the verb default page shows the description, the reverse name and both plural forms as
        // read only text, so each of these fields has an own show component (see base_views.json
        // verb_default); the measure verb is used because its four values differ, so that a test
        // can tell which field is shown by which component
        $form = new system_form();
        $vrb_filled = new verb($t_vrb->verb_measure_filled()->api_json());

        $test_name = 'the verb description is shown as read only text';
        $t->assert($test_name, $form->show_description($vrb_filled), verbs::MEASURE_COM);

        // the three language forms are shown below each other, so each value carries the label of
        // the matching form field, because without it the user cannot tell which value is which
        $test_name = 'the reverse name of the verb is shown as read only text with its label';
        $t->assert($test_name, $form->show_reverse($vrb_filled),
            $this->labeled(msg_id::FORM_FIELD_REVERSE, verbs::MEASURE_REVERSE));

        $test_name = 'the plural of the verb is shown as read only text with its label';
        $t->assert($test_name, $form->show_plural($vrb_filled),
            $this->labeled(msg_id::FORM_FIELD_PLURAL, verbs::MEASURE_PLURAL));

        $test_name = 'the plural of the reverse name of the verb is shown as read only text with its label';
        $t->assert($test_name, $form->show_plural_reverse($vrb_filled),
            $this->labeled(msg_id::FORM_FIELD_PLURAL_REVERSE, verbs::MEASURE_REV_PLURAL));

        // a verb without the language forms shows an empty text and never a php warning
        $vrb_empty = new verb($t_vrb->verb()->api_json());
        $test_name = 'a verb without a reverse name shows an empty text';
        $t->assert($test_name, $form->show_reverse($vrb_empty), '');
        $test_name = 'a verb without a plural reverse name shows an empty text';
        $t->assert($test_name, $form->show_plural_reverse($vrb_empty), '');

        $t->subheader($ts . 'triples of a verb');

        // the verb default page lists the triples that use the verb with a link to each triple
        $list = new ui_list();
        $cfg = new data_object_ui();
        $cfg->trp_lst = $t_trp->triple_list_ui();
        $vrb_is = new verb($t_vrb->verb_is()->api_json());
        $trp_html = $list->triple_list($vrb_is, $msg, $cfg);
        // 'city of Zurich' is one of the test triples that use the 'is' verb
        $test_name = 'the triples that use the verb are listed';
        $t->assert_text_contains($test_name, $trp_html, triple_names::CITY_ZH_NAME);
        $test_name = 'the listed triples are links to the triple page';
        $t->assert_text_contains($test_name, $trp_html, url_var::MASK . '=' . views::TRIPLE_ID);

        // a verb that is not used in any triple gets the not-used message instead of an empty page
        $test_name = 'a verb without triples shows the not used message';
        $vrb_unused = new verb($t_vrb->verb()->api_json());
        $t->assert($test_name, $list->triple_list($vrb_unused, $msg, $cfg),
            $mtr->txt(msg_id::NOT_USED_FOR_TRIPLES));
    }

    /**
     * a read only field value as system_form::show_field_labeled creates it, so that the expected
     * text of a test does not repeat the label text and stays independent of the translation
     *
     * @param msg_id $ui_msg_code_id the message id of the field label
     * @param string $value the expected field value
     * @return string the expected text of the read only field
     */
    private function labeled(msg_id $ui_msg_code_id, string $value): string
    {
        global $mtr;
        return $mtr->txt($ui_msg_code_id) . def::FALLBACK_LABEL_SEPARATOR . $value;
    }

}