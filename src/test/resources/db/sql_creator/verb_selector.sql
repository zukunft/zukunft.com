SELECT * FROM (
    SELECT verb_id AS id,
           CASE WHEN (name_reverse  <> '' IS NOT TRUE AND name_reverse <> verb_name) THEN CONCAT(verb_name, ' (', name_reverse, ')') ELSE verb_name END AS name,
           words
      FROM verbs
UNION SELECT verb_id * -1 AS id,
           CONCAT(name_reverse, ' (', verb_name, ')') AS name,
           words
      FROM verbs
     WHERE name_reverse <> ''
       AND name_reverse <> verb_name) AS links
 ORDER BY words DESC, name;