-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u3
-- http://www.phpmyadmin.net

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `zukunft` - loading of predefined code linked database records
--

--
-- Dumping data for table `calc_and_cleanup_task_types`
--

INSERT INTO `calc_and_cleanup_task_types` (`calc_and_cleanup_task_type_id`, `calc_and_cleanup_task_type_name`, `description`, `code_id`) VALUES
(1, 'update value', 'if a value is updated all the depending formula values should be calculated again', 'value_update'),
(2, 'add value', '1. check if used in formulas and if yes, create new formula results\r\n2. calculate the new formula results', 'value_add'),
(3, 'exclude a value', 'check if used for a formula result and if yes either remove the formula result or update it', 'value_del'),
(4, 'update formula', NULL, 'formula_update'),
(5, 'add formula', NULL, 'formula_add'),
(6, 'exclude formula', NULL, 'formula_del'),
(7, 'link formula', NULL, 'formula_link'),
(8, 'unlink a formula', NULL, 'formula_unlink'),
(9, 'link a word', NULL, 'word_link'),
(10, 'unlink word', NULL, 'word_unlink');

--
-- Dumping data for table `change_actions`
--

INSERT INTO `change_actions` (`change_action_id`, `change_action_name`, `code_id`) VALUES
(1, 'add', ''),
(2, 'del', ''),
(3, 'update', '');

--
-- Dumping data for table `change_tables`
--

INSERT INTO `change_tables` (`change_table_id`, `change_table_name`, `description`, `code_id`) VALUES
(1, 'values', 'number', 'values'),
(2, 'words', 'word', 'words'),
(7, 'formulas', 'formula', 'formulas'),
(8, 'word_links', 'word pair', 'word_links'),
(9, 'value_word_links', 'value name', 'value_links'),
(10, 'sources', 'source', 'sources'),
(11, 'users', 'user', 'users'),
(12, 'user_words', 'word', 'user_words'),
(13, 'formula_word_links', 'formula assign', 'formula_links'),
(14, 'sys_log', 'system error', NULL),
(15, 'user_formulas', 'formula', 'user_formulas'),
(16, 'user_values', 'number', 'user_values'),
(17, 'link_types', 'verb', 'link_types'),
(18, 'masks', 'view', 'masks'),
(19, 'mask_entry_links', 'view part', 'mask_entry_links'),
(21, 'user_masks', 'view', 'user_masks'),
(22, 'mask_entries', 'view component', 'mask_entries'),
(23, 'user_mask_entries', 'view component', 'user_mask_entries'),
(24, 'user_formula_word_links', 'formula assign', 'user_formula_word_links'),
(25, 'user_value', 'number', 'user_values'),
(26, 'value', 'number', 'values'),
(27, 'user_mask_entry_links', 'view part', 'user_mask_entry_links'),
(28, 'user_word_links', 'word pair', 'user_word_links'),
(29, 'value_phrase_links', '', NULL),
(30, '', '', 'user_formula_links'),
(31, 'formula_links', '', NULL),
(32, '', '', 'views'),
(33, '', '', 'user_views'),
(34, '', '', 'view_entry_links'),
(35, '', '', 'user_view_entry_links'),
(36, 'views', '', NULL),
(37, 'user_views', '', NULL),
(38, 'view_entries', '', NULL),
(39, 'user_view_entries', '', NULL),
(40, 'view_entry_links', '', NULL),
(41, '', '', 'view_entries'),
(42, '', '', 'user_view_entries');

--
-- Dumping data for table `change_fields`
--

INSERT INTO `change_fields` (`change_field_id`, `change_field_name`, `table_id`, `description`, `code_id`) VALUES
(1, 'word_name', 2, 'name', ''),
(3, 'word_value', 1, 'number', 'value'),
(4, 'formula_text', 7, NULL, 'ref_text'),
(5, 'formula_name', 7, 'name', ''),
(6, 'word_id', 9, 'word', ''),
(7, 'source_name', 10, 'name', ''),
(8, 'url', 10, 'url', ''),
(9, 'source_id', 1, 'source', ''),
(10, 'plural', 2, 'plural', ''),
(11, 'word_type_id', 2, 'type', ''),
(12, 'user_name', 11, 'username', ''),
(13, 'email', 11, 'email', ''),
(14, 'first_name', 11, 'first name', ''),
(15, 'last_name', 11, 'last name', ''),
(16, 'user_id', 2, 'user', ''),
(17, 'word_name', 12, 'name', ''),
(18, 'resolved_text', 7, 'expression', ''),
(19, 'user_id', 7, 'user', ''),
(20, 'sys_log_status_id', 14, 'system error status', ''),
(21, 'formula_name', 15, 'name', ''),
(22, 'resolved_text', 15, 'expression', ''),
(23, 'formula_text', 15, NULL, 'ref_text'),
(24, 'word_value', 16, 'value', ''),
(25, 'type_name', 17, 'type', ''),
(26, 'name_plural', 17, 'plural', ''),
(27, 'name_reverse', 17, 'name', ''),
(28, 'name_plural_reverse', 17, 'plural opposite name', ''),
(29, 'description', 12, 'description', ''),
(30, 'plural', 12, 'plural', ''),
(31, 'mask_name', 18, 'name', ''),
(32, 'description', 2, 'comment', ''),
(33, 'mask_type_id', 18, 'type', ''),
(34, 'user_id', 18, 'user', ''),
(37, 'all_values_needed', 15, 'need all flag', 'all_values_needed'),
(38, 'all_values_needed', 7, 'need all flag', 'all_values_needed'),
(39, 'mask_id', 2, 'view', ''),
(43, 'word_type_id', 12, 'type', ''),
(44, 'description', 7, 'comment', ''),
(45, 'formula_type_id', 7, 'type', 'frm_type'),
(46, 'description', 15, 'comment', ''),
(47, 'formula_type_id', 15, 'type', 'frm_type'),
(48, 'comment', 18, 'comment', ''),
(49, 'comment', 21, 'comment', ''),
(50, 'mask_type_id', 21, 'type', ''),
(51, 'entry_name', 22, 'name', ''),
(52, 'mask_entry_name', 22, 'name', ''),
(53, 'comment', 22, 'comment', ''),
(54, 'mask_entry_type_id', 22, 'type', ''),
(55, 'comment', 23, 'comment', ''),
(56, 'mask_entry_type_id', 23, 'type', ''),
(57, 'sys_log', 14, NULL, ''),
(59, 'excluded', 15, NULL, ''),
(60, 'word_value', 26, 'number', ''),
(61, 'word_value', 25, 'number', ''),
(62, 'user_value', 1, 'number', ''),
(63, 'mask_name', 21, 'name', ''),
(64, 'mask_entry_name', 23, 'name', ''),
(65, 'excluded', 1, NULL, ''),
(66, 'excluded', 16, NULL, ''),
(67, 'name', 28, 'name', ''),
(68, 'name', 8, 'name', ''),
(69, 'order_nbr', 19, 'order', ''),
(70, 'position_type', 19, 'position', ''),
(71, 'source_id', 16, 'source', ''),
(72, 'word_id_row', 22, NULL, ''),
(73, 'word_id_col', 22, NULL, ''),
(74, 'word_id_col2', 22, NULL, ''),
(75, 'order_nbr', 27, NULL, ''),
(76, 'view_name', 36, NULL, ''),
(77, 'comment', 36, NULL, ''),
(78, 'view_type_id', 36, NULL, ''),
(79, 'comment', 37, NULL, ''),
(80, 'view_type_id', 37, NULL, ''),
(81, 'view_entry_name', 38, NULL, ''),
(82, 'comment', 38, NULL, ''),
(83, 'view_entry_type_id', 38, NULL, ''),
(84, 'comment', 39, NULL, ''),
(85, 'view_entry_type_id', 39, NULL, ''),
(86, 'order_nbr', 40, NULL, ''),
(87, 'position_type', 40, NULL, '');

--
-- Dumping data for table `formula_element_types`
--

INSERT INTO `formula_element_types` (`formula_element_type_id`, `formula_element_type_name`, `code_id`, `description`) VALUES
(1, 'word', 'word', 'a reference to a word'),
(2, 'verb', 'term_link', 'a reference to a term link'),
(3, 'formula', 'formula', 'a reference to a formula');

--
-- Dumping data for table `formula_link_types`
--

INSERT INTO `formula_link_types` (`formula_link_type_id`, `type_name`, `code_id`, `formula_id`, `word_type_id`, `link_type_id`, `description`) VALUES
(1, '', NULL, 0, 0, 0, 'default'),
(2, '', NULL, 0, 4, 5, 'increase needs to know for which time period the increase should be calculated');

--
-- Dumping data for table `formula_types`
--

INSERT INTO `formula_types` (`formula_type_id`, `name`, `description`, `code_id`) VALUES
(1, 'calc', 'a normal calculation formula', 'default'),
(2, 'next', 'time jump forward: replaces a time term with the next time term based on the verb follower. E.g. "2017" "next" would lead to use "2018"', 'time_next'),
(3, 'prior', 'time jump backward: replaces a time term with the previous time term based on the verb follower. E.g. "2017" "next" would lead to use "2016"', 'time_prior'),
(4, 'this', 'selects the assumed time term', 'time_this'),
(5, 'reversible', 'a formula that can ba included also in the reversed version e.g. "minute = second / 60" and "second = minute * 60"', 'reversible');

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`language_id`, `language_name`, `code_id`, `wikimedia_code`, `description`) VALUES
(1, 'English', 'en', 'en', 'the system language, so each word must be unique for all users in this language'),
(2, 'UK English', 'en_uk', '', 'the users can use this (and the other) languages to create its own namespace. So one user may use "Zurich" in UK English for "Kanton Zurich" and another user may use "Zurich" in UK English for "Zurich AG".'),
(3, 'German', 'de', 'de', 'a normal translation to German');

--
-- Dumping data for table `languages_forms`
--

INSERT INTO `languages_forms` (`languages_form_id`, `languages_form_name`, `code_id`, `language_id`) VALUES
(1, 'plural', 'plural', 1);

--
-- Dumping data for table `protection_types`
--

INSERT INTO `protection_types` (`protection_type_id`, `type_name`, `code_id`, `description`) VALUES
(1, 'no protection', 'no_protection', 'anyone can take the ownership '),
(2, 'user', 'user_protection', 'only users with a login and a confirmed email can take over the ownership'),
(3, 'admin', 'admin_protection', 'only user with admin permission can take the ownership'),
(4, 'no change', 'no_change', 'no change of the ownership is allowed');


--
-- Dumping data for table `ref_types`
--

INSERT INTO `ref_types` (`ref_type_id`, `type_name`, `code_id`, `description`, `base_url`) VALUES
(1, 'wikipedia', 'wikipedia', 'wikipedia', 'https://en.wikipedia.org/wiki/'),
(2, 'wikidata', 'wikidata', 'wikidata', 'https://www.wikidata.org/wiki/');

-- --------------------------------------------------------


--
-- Dumping data for table `share_types`
--

INSERT INTO `share_types` (`share_type_id`, `type_name`, `code_id`, `description`) VALUES
(1, 'public', 'public', 'value can be seen and used by everyone (default)'),
(2, 'personal', 'personal', 'value can be seen by everyone, but is linked to the user'),
(3, 'group', 'group', 'only a closed group of users can seen and use the values'),
(4, 'private', 'private', 'only the user itself can see und use the value');

-- --------------------------------------------------------


--
-- Dumping data for table `source_types`
--

INSERT INTO `source_types` (`source_type_id`, `source_type_name`, `code_id`) VALUES
(1, 'XML', 'xml'),
(2, 'XBRL', 'xbrl'),
(3, 'CSV', 'csv'),
(4, 'PDF', 'pdf');

--
-- Dumping data for table `sys_log_status`
--

INSERT INTO `sys_log_status` (`sys_log_status_id`, `type_name`, `code_id`, `description`, `action`) VALUES
(1, 'new', 'log_status_new', 'the error has just being logged and no one has yet looked at it ', NULL),
(2, 'assigned', 'log_status_assigned', 'A developer is looking at the error.', 'assign to'),
(3, 'resolved', 'log_status_resolved', 'the error is supposed to be corrected', 'resolve'),
(4, 'closed', 'log_status_closed', 'a second person (other than the developer) has confirmed that the problem is solved.', 'close');

--
-- Dumping data for table `sys_log_types`
--

INSERT INTO `sys_log_types` (`sys_log_type_id`, `type_name`, `code_id`) VALUES
(0, 'undefined', 'undefined'),
(1, 'Info', 'log_info'),
(2, 'Warning', 'log_warning'),
(3, 'Error', 'log_error'),
(4, 'FATAL ERROR', 'log_fatal');

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `code_id`, `password`, `email`, `email_verified`, `email_alternative`, `ip_address`, `mobile_number`, `mobile_verified`, `first_name`, `last_name`, `street`, `place`, `country_id`, `post_verified`, `official_id`, `user_official_id_type_id`, `official_verified`, `user_type_id`, `last_word_id`, `last_mask_id`, `is_active`, `dt`, `last_logoff`, `user_profile_id`, `source_id`, `activation_key`, `activation_key_timeout`) VALUES
(1, 'zukunft.com system batch job', 'system', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, '2018-02-05 08:32:36', NULL, NULL, NULL, NULL, NULL),
(2, 'zukunft.com system test', 'system_test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, '2018-06-25 12:01:14', NULL, NULL, NULL, NULL, NULL);

--
-- Dumping data for table `user_official_types`
--

INSERT INTO `user_official_types` (`user_official_type_id`, `type_name`, `code_id`, `comment`) VALUES
(1, 'passport number', 'passport_nbr', NULL);

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `type_name`, `code_id`, `description`) VALUES
(1, 'normal user', 'normal', NULL),
(2, 'admin', 'admin', 'Administrator that can add and change verbs and sees the code_id'),
(3, 'developer', 'dev', 'Can see all errors and change the status of the errors, but cannot access the production system');

--
-- Dumping data for table `user_types`
--

INSERT INTO `user_types` (`user_type_id`, `user_type`, `code_id`, `comment`) VALUES
(1, 'Guest', 'guest', ''),
(2, 'IP address', 'ip_address', ''),
(3, 'Verified', 'verified', 'verified by email or mobile'),
(4, 'Secured', 'secured', 'verified with a high security e.g. via passport of a trusted country');

--
-- Dumping data for table `verbs`
--

INSERT INTO `verbs` (`verb_id`, `verb_name`, `code_id`, `description`, `condition_type`, `formula_name`, `name_plural_reverse`, `name_plural`, `name_reverse`, `words`) VALUES
(0, 'not set', '', NULL, NULL, '', '', '', '', 0),
(1, 'has a', '', NULL, 0, '', 'is used for', 'has', 'is used for', 27),
(2, 'is a', 'vrb_is', NULL, 0, '', 'are', 'are', 'are', 113),
(3, 'is time jump for', '', 'is the default time jump for', 0, 'time jump', 'have the default time jump', 'are the time jump for', 'has the default time jump', 2),
(4, 'is term jump for', '', 'is the default term jump for', 1, '', '', '', '', 0),
(5, 'term type needed', '', 'the formula needs the linked term type', NULL, '', '', '', '', 0),
(6, 'is follower of', 'vrb_follow', 'is follower of', NULL, 'follower', 'is followed by', 'is follower of', 'is followed by', 17),
(7, 'is part of', 'vrb_contains', 'if several similar term create different views to the same sum; E.g. Cash Flow Paper, Balance Sheet and Income statement are Financial Statements. Or Sectors and Regions are different splittings', NULL, NULL, 'contains', 'is part of', 'contains', 51),
(8, 'uses', '', NULL, NULL, NULL, 'are used by', 'uses', 'is used by', 7),
(9, 'issue', '', NULL, NULL, NULL, 'are issued by', 'issues', 'is issued by', 3),
(10, 'is measure type for', '', 'is the default measure type for', NULL, NULL, 'have the measure type', 'are measure type for', 'has the measure type', 9),
(11, 'is an acronym for', '', 'is an acronym for', NULL, NULL, 'are an acronyms of', 'are an acronyms for', 'is an acronym of', 0),
(12, 'can be used as a differentiator for', 'vrb_can_contain', 'similar to contains, but in a table view the row will not be shown if there is no corresponding value', NULL, 'differentiator', 'can be differentiated by', 'can be used as a differentiator for', 'can be differentiated by', 2),
(13, 'influences', '', NULL, NULL, NULL, 'is influenced by', 'influences', 'is influenced by', 0),
(14, 'is alias of', '', NULL, NULL, NULL, 'is alias of', 'is alias of', 'is alias of', 5),
(15, 'can be', '', 'vrb_can_be', NULL, NULL, 'can be', 'can be', 'can be', 2);

--
-- Dumping data for table `views`
--

INSERT INTO `views` (`view_id`, `user_id`, `view_name`, `comment`, `view_type_id`, `code_id`, `excluded`) VALUES
(1, 1, 'Start view', 'A dynamic start mask that shows a interesting fact', 1, 'start', NULL),
(2, 14, 'Company sheet', 'The income statement, balance sheet or cash flow sheet of a big company', 3, NULL, NULL),
(3, 14, 'complete', 'Show a word, all related words to edit the word tree and the linked formulas with some results', 4, NULL, NULL),
(4, 1, 'Change Number', '', NULL, 'value_edit', NULL),
(5, 1, 'Add New Formula', '', NULL, 'formula_add', NULL),
(6, 1, 'Add New Word', '', NULL, 'word_add', NULL),
(7, 1, 'Change Word', '', NULL, 'word_edit', NULL),
(8, 1, 'Add New Number', '', NULL, 'value_add', NULL),
(9, 1, 'Change Formula', '', NULL, 'formula_edit', NULL),
(10, 1, 'Add New View', '', NULL, 'view_add', NULL),
(11, 1, 'Change View', '', NULL, 'view_edit', NULL),
(12, 1, 'Add New Verb', '', NULL, 'verb_add', NULL),
(13, 1, 'Search', 'The search page for all words and formulas', NULL, 'word_find', NULL),
(14, 1, 'Delete Number', '', NULL, 'value_del', NULL),
(15, 14, 'Company list with main ratios', 'List the most interesting companies with the main ratios', 3, NULL, NULL),
(16, 1, '', 'to change the user settings', NULL, 'user', NULL),
(17, 1, 'Add New Source', '', NULL, 'source_add', NULL),
(18, 1, 'Verb List', 'List all available ways how to link two words', NULL, 'verbs', NULL),
(19, 1, 'System Error List', 'List the system errors and allow the user to change the status', NULL, 'error_update', NULL),
(20, 1, 'Delete Formula', 'To confirm the exclusion or deleting of a formula', NULL, 'formula_del', NULL),
(21, 1, 'Error Log', 'simple confirm page for a new error that has been logged in the database', NULL, 'error_log', NULL),
(22, 1, 'Explain Formula Result', 'Explain the formula result', NULL, 'formula_explain', NULL),
(23, 1, 'Delete Word', 'Exclude or delete a word', NULL, 'word_del', NULL),
(24, 1, 'Change Source', '', NULL, 'source_edit', NULL),
(25, 1, 'Delete Source', 'Delete an external data source', NULL, 'source_del', NULL),
(28, 1, 'Change Verb', 'Rename a tern link type', NULL, 'verb_edit', NULL),
(30, 14, 'Country ratios', '', 2, NULL, NULL),
(31, 1, 'Word', 'the default view for words', 0, 'word', NULL),
(32, 1, 'Formula test', 'To debug the formula', NULL, 'formula_test', NULL),
(33, 1, 'Delete verb', 'Exclude or delete a verb', NULL, 'verb_del', NULL),
(34, 1, 'Delete view', '', NULL, 'view_del', NULL),
(35, 1, 'Add New Triple', '', NULL, 'triple_add', NULL),
(36, 1, 'Change Triple', 'Rename a triple', NULL, 'triple_edit', NULL),
(37, 1, 'Delete triple', 'Exclude or delete a triple', NULL, 'triple_del', NULL);

--
-- Dumping data for table `view_components`
--

INSERT INTO `view_components` (`view_component_id`, `user_id`, `view_component_name`, `comment`, `view_component_type_id`, `word_id_row`, `formula_id`, `word_id_col`, `word_id_col2`, `excluded`, `linked_view_component_id`, `view_component_link_type_id`, `link_type_id`) VALUES
(1, 14, 'Name', NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 14, 'Cash Flow Statement', NULL, 11, 4, NULL, 141, NULL, NULL, NULL, NULL, NULL),
(4, 14, 'Words related', NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 4, 'formulas', NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 4, 'calculated results', NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 4, 'XML Export', NULL, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 4, 'Display Name', NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 997, 'Company with ratios', NULL, 13, 1, NULL, 175, NULL, NULL, NULL, NULL, NULL),
(10, 1572, 'JSON Export', NULL, 16, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Dumping data for table `view_component_position_types`
--

INSERT INTO `view_component_position_types` (`view_component_position_type_id`, `type_name`, `description`) VALUES
(1, 'below', ''),
(2, 'side', 'is right of the previous entry (or left for right to left languages)');

--
-- Dumping data for table `view_component_types`
--

INSERT INTO `view_component_types` (`view_component_type_id`, `type_name`, `description`, `code_id`) VALUES
(1, 'word selector', '', 'word_select'),
(2, 'view selector', 'to select an existing mask e.g. to set the default view', 'view_select'),
(3, 'text', 'simply to display a variable text', 'text'),
(4, 'fixed word', 'just display a word as a text', 'fixed'),
(5, 'linked word', 'display a word and offer a link', 'link'),
(6, 'formula results', 'show the results of a formula', 'formula_results'),
(7, 'word list up', 'Show a list of words related with a specified word link type; e.g. For company list all ABB and others, because they are linked with is a', 'word_list_up'),
(8, 'word name', 'show the word name and give the user the possibility to change the word name', 'word_name'),
(9, 'word list down', 'Show a list of words related with a specified word link type; e.g. For company list all ABB and others, because they are linked with is a', 'word_list_down'),
(10, 'all relations', 'show the complete list of all relations', 'values_all'),
(11, 'values related to', 'display all values that are related to a defined term', 'values_related'),
(12, 'formulas', 'show all related formulas', 'formula_list'),
(13, 'word value list', 'A list of words with some key values e.g. a company list with the PE ratio', 'word_value_list'),
(14, 'XML export', 'offer to configure and start the XML export of a phrase and all related words, numbers and formulas', 'xml_export'),
(15, 'CSV export', 'to define the main phrase, the column and row for the CSV export', 'csv_export'),
(16, 'JSON Export', 'To start or configure the JSON export', 'json_export');

--
-- Dumping data for table `view_link_types`
--

INSERT INTO `view_link_types` (`view_link_type_id`, `type_name`, `comment`) VALUES
(1, '0', '');

--
-- Dumping data for table `view_typelist`
--

INSERT INTO `view_types` (`view_type_id`, `type_name`, `description`, `code_id`) VALUES
(1, 'standard', 'the base display mask without additional functionalities', 'default'),
(2, 'entry view', 'these masks are used for the zukunft.com entry page. If a totally new user opens zukunft.com the first time, he will see a random mask of this type.', 'entry'),
(3, 'edit', 'a edit mask that is used to change data', 'mask_default'),
(4, 'presentation', 'with auto forward', 'presentation'),
(5, 'word default', 'a default mask for new words', 'word_default');

--
-- Dumping data for table `word_types`
--

INSERT INTO `word_types` (`word_type_id`, `type_name`, `description`, `code_id`, `scaling_factor`, `word_symbol`) VALUES
(1, 'standard', 'for words that have need no special behaviour', 'default', NULL, ''),
(2, 'time', 'A time word defines the time period for which a value is valid and values with a time can be used to display time series.', 'time', NULL, ''),
(3, 'measure type', 'a measure word such as meter, kilogram, ...', 'measure', NULL, ''),
(4, 'time jump', 'these terms describes a change of a timestamp term', 'time_jump', NULL, ''),
(5, 'calc', 'a calculated word in R; e.g. this year returns always a different term', NULL, NULL, ''),
(6, 'format percent', 'terms that forces the result to be formatted in percent', 'percent', NULL, ''),
(7, 'scaling', 'a scaling word such as millions, one, ...', 'scaling', NULL, ''),
(8, 'hidden scaling', '"one" is needed as a scaling word for correct calculations, but it is not useful to display it', 'scaling_hidden', NULL, ''),
(9, 'view / layer', 'word to separate the number layers and to change the view.\r\nE.g. -as reported- are exactly the numbers as they come from the external source\r\n-detailed- are the most granular splitting of the -as reported- numbers\r\n-projection- show the changes and includes the estimates to make a forecast\r\n', 'view', NULL, ''),
(10, 'formula link', 'A term with the same name as a formula, because some values can be calculated and can nevertheless be overwritten', 'formula_link', NULL, ''),
(11, 'differentiator filler', 'these terms are used to fill up a differentiator list, so most likely the erm linked will be named "other"', 'type_other', NULL, ''),
(12, 'this', 'not sure, why this is needed', 'this', NULL, ''),
(13, 'next', 'not sure, why this is needed', 'next', NULL, ''),
(14, 'prior', 'not sure, why this is needed', 'previous', NULL, ''),
(15, 'scaling word percent', 'all words that represent percent', 'scaling_percent', 100, '%'),
(16, 'scaled measure', 'a combination of scaling and measure e.g. 100ml', 'scaled_measure', 0, ''),
(17, 'math constant', 'A mathematical constant is a key number whose value is fixed by an unambiguous definition', 'constant', 0, '');

--
-- Setting the initial IP blocking for testing
--

INSERT INTO `user_blocked_ips` (`user_blocked_id`, `ip_from`, `ip_to`, `reason`, `is_active`) VALUES
    (1, '66.249.64.95', '66.249.64.95', 'too much damage from this IP', 1);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
