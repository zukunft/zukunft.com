--
-- upgrade the zukunft.com PostgreSQL database to Version 0.0.3
--

-- --------------------------------------------------------

--
-- Structure for view phrases
--

CREATE OR REPLACE VIEW phrases AS
SELECT w.word_id   AS phrase_id,
       w.user_id,
       w.word_name AS phrase_name,
       w.excluded,
       w.share_type_id,
       w.protection_type_id
FROM words AS w
UNION
SELECT (l.word_link_id * -(1))                                                        AS phrase_id,
       l.user_id,
       CASE WHEN (l.description IS NULL) THEN l.word_link_name ELSE l.description END AS phrase_name,
       l.excluded,
       l.share_type_id,
       l.protection_type_id
FROM word_links AS l;