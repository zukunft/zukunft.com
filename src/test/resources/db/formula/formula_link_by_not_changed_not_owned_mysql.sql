PREPARE formula_link_by_not_changed_not_owned FROM
   'SELECT user_id
      FROM user_formula_links
     WHERE formula_link_id = ?
       AND (excluded <> 1 OR excluded is NULL)
       AND user_id <> ?';
