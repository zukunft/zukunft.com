SELECT s.formula_link_id,
       u.formula_link_id AS user_formula_link_id,
       s.user_id,
       s.formula_id,
       s.phrase_id,
       CASE WHEN (u.formula_link_type_id IS NULL) THEN s.formula_link_type_id ELSE u.formula_link_type_id END AS formula_link_type_id,
       CASE WHEN (u.excluded IS NULL) THEN s.excluded ELSE u.excluded END AS excluded
  FROM formula_links s
  LEFT JOIN user_formula_links u ON s.formula_link_id = u.formula_link_id
                                AND u.user_id = 3
 WHERE s.formula_link_id = $1;