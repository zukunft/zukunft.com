PREPARE formula_user_delete FROM
    'DELETE FROM user_formulas
           WHERE formula_id = ?
             AND user_id = ?';