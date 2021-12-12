SELECT user_id
FROM user_formula_links
WHERE formula_link_id = 2
  AND user_id <> 3
  AND (excluded <> 1 OR excluded is NULL);