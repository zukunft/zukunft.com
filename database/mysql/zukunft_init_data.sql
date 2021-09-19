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
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_profile_id`, `code_id`, `password`, `email`, `email_verified`, `email_alternative`, `ip_address`, `mobile_number`, `mobile_verified`, `first_name`, `last_name`, `street`, `place`, `country_id`, `post_verified`, `official_id`, `user_official_id_type_id`, `official_verified`, `user_type_id`, `last_word_id`, `last_mask_id`, `is_active`, `dt`, `last_logoff`, `source_id`, `activation_key`, `activation_key_timeout`) VALUES
(1, 'zukunft.com system batch job', 4, 'system', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, '2018-02-05 08:32:36', NULL, NULL, NULL, NULL, NULL),
(2, 'zukunft.com system test', 4, 'system_test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, '2018-06-25 12:01:14', NULL, NULL, NULL, NULL, NULL);

--
-- Dumping data for table `verbs`
--

INSERT INTO `verbs` (`verb_id`, `verb_name`, `code_id`, `description`, `condition_type`, `formula_name`, `name_plural_reverse`, `name_plural`, `name_reverse`, `words`) VALUES
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
-- Setting the initial IP blocking for testing
--

INSERT INTO `user_blocked_ips` (`user_blocked_id`, `ip_from`, `ip_to`, `reason`, `is_active`) VALUES
    (1, '66.249.64.95', '66.249.64.95', 'too much damage from this IP', 1);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
