--
-- Database: 'zukunft' - loading of predefined code linked database records
--


--
-- Dumping data for table 'users'
--

INSERT INTO users (user_id, user_name, user_profile_id, code_id, password, email, email_verified, email_alternative, ip_address, mobile_number, mobile_verified, first_name, last_name, street, place, country_id, post_verified, official_id, user_official_id_type_id, official_verified, user_type_id, last_word_id, last_mask_id, is_active, dt, last_logoff, source_id, activation_key, activation_key_timeout) VALUES
(1, 'zukunft.com system batch job', 4, 'system', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, '2018-02-05 08:32:36', NULL, NULL, NULL, NULL, NULL),
(2, 'zukunft.com system test', 4, 'system_test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, '2018-06-25 12:01:14', NULL, NULL, NULL, NULL, NULL);


--
-- Dumping data for table 'verbs'
--

INSERT INTO verbs (verb_id, verb_name, code_id, description, condition_type, formula_name, name_plural_reverse, name_plural, name_reverse, words) VALUES
(0, 'not set', '', NULL, NULL, '', '', '', '', 0),
(1, 'has a', '', NULL, 0, '', 'is used for', 'has', 'is used for', 27),
(2, 'is a', 'vrb_is', 'the main child to parent relation e.g. Zurich is a Canton. The reverse is valid and usually plural is used e.g. Cantons are Zurich, Bern, ...', 0, '', 'are', 'are', 'are', 113),
(3, 'is time jump for', '', 'is the default time jump for', 0, 'time jump', 'have the default time jump', 'are the time jump for', 'has the default time jump', 2),
(4, 'is term jump for', '', 'is the default term jump for', 1, '', '', '', '', 0),
(5, 'term type needed', '', 'the formula needs the linked term type', NULL, '', '', '', '', 0),
(6, 'is follower of', 'vrb_follow', 'is follower of', NULL, 'follower', 'is followed by', 'is follower of', 'is followed by', 17),
(7, 'is part of', 'is_part_of', 'if several similar term create different views to the same sum; E.g. Cash Flow Paper, Balance Sheet and Income statement are Financial Statements. Or Sectors and Regions are different splittings', NULL, NULL, 'contains', 'is part of', 'contains', 51),
(8, 'uses', '', NULL, NULL, NULL, 'are used by', 'uses', 'is used by', 7),
(9, 'issue', '', NULL, NULL, NULL, 'are issued by', 'issues', 'is issued by', 3),
(10, 'is measure type for', '', 'is the default measure type for', NULL, NULL, 'have the measure type', 'are measure type for', 'has the measure type', 9),
(11, 'is an acronym for', '', 'is an acronym for', NULL, NULL, 'are an acronyms of', 'are an acronyms for', 'is an acronym of', 0),
(12, 'can be used as a differentiator for', 'vrb_can_contain', 'similar to contains, but in a table view the row will not be shown if there is no corresponding value', NULL, 'differentiator', 'can be differentiated by', 'can be used as a differentiator for', 'can be differentiated by', 2),
(13, 'influences', '', NULL, NULL, NULL, 'is influenced by', 'influences', 'is influenced by', 0),
(14, 'is alias of', '', NULL, NULL, NULL, 'is alias of', 'is alias of', 'is alias of', 5),
(15, 'can be', '', 'vrb_can_be', NULL, NULL, 'can be', 'can be', 'can be', 2);

--
-- Dumping data for table 'views'
--

INSERT INTO views (view_id, user_id, view_name, comment, view_type_id, code_id, excluded) VALUES
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
-- Dumping data for table 'view_components'
--

INSERT INTO view_components (view_component_id, user_id, view_component_name, comment, view_component_type_id, word_id_row, formula_id, word_id_col, word_id_col2, excluded, linked_view_component_id, view_component_link_type_id, link_type_id) VALUES
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
-- Dumping data for table 'view_link_types'
--

INSERT INTO view_link_types (view_link_type_id, type_name, comment) VALUES
(1, '0', '');


--
-- Setting the initial IP blocking for testing
--

INSERT INTO user_blocked_ips (user_blocked_id, ip_from, ip_to, reason, is_active) VALUES
    (1, '66.249.64.95', '66.249.64.95', 'too much damage from this IP', 1);

