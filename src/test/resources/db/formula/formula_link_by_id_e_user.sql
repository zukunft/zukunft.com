SELECT formula_link_id,
       link_type_id,
       excluded
FROM user_formula_links
WHERE formula_link_id = 2
  AND user_id = 1;