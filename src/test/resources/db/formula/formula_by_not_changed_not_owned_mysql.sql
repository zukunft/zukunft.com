PREPARE formula_by_not_changed_not_owned FROM
    'SELECT user_id
       FROM formulas
      WHERE formula_id = ?
        AND (excluded <> 1 OR excluded is NULL)
        AND user_id <> ?';
