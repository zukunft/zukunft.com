SELECT s.triple_id,
       u.triple_id AS user_triple_id,
       s.user_id,
       s.from_phrase_id,
       s.to_phrase_id,
       s.verb_id,
       s.phrase_type_id,
       IF(u.name_given  IS NULL, s.name_given,  u.name_given)  AS name_given,
       IF(u.description IS NULL, s.description, u.description) AS description,
       IF(u.excluded    IS NULL, s.excluded,    u.excluded)    AS excluded
  FROM triples s
  LEFT JOIN user_triples u ON s.triple_id = u.triple_id
                          AND u.user_id = 3
 WHERE triple_id = 1;