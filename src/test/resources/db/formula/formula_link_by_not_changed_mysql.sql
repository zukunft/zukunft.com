PREPARE formula_link_by_not_changed FROM
   'SELECT user_id
      FROM user_formula_links
     WHERE formula_link_id = ?
       AND (excluded <> 1 OR excluded is NULL)';