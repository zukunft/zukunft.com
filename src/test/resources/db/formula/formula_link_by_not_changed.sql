PREPARE formula_link_by_not_changed (bigint) AS
    SELECT user_id
      FROM user_formula_links
     WHERE formula_link_id = $1
       AND (excluded <> 1 OR excluded is NULL);