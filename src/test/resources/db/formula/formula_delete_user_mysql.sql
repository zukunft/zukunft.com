PREPARE formula_delete_user FROM
    'DELETE FROM user_formulas
           WHERE formula_id = ?
             AND user_id = ?';