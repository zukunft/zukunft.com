SELECT s.formula_link_id,
       u.formula_link_id                                          AS user_formula_link_id,
       s.user_id,
       s.formula_id,
       s.phrase_id,
       IF(u.link_type_id IS NULL, s.link_type_id, u.link_type_id) AS link_type_id,
       IF(u.excluded IS NULL, s.excluded, u.excluded)             AS excluded
FROM formula_links s
         LEFT JOIN user_formula_links u ON s.formula_link_id = u.formula_link_id
    AND u.user_id = 1
WHERE s.formula_link_id = 2;