PREPARE formula_link_by_not_changed_not_owned (bigint, bigint) AS
    SELECT user_id
      FROM user_formula_links
     WHERE formula_link_id = $1
       AND (excluded <> 1 OR excluded is NULL)
       AND user_id <> $2;
