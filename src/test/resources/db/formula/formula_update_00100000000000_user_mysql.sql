PREPARE formula_update_00100000000000_user FROM
    'UPDATE user_formulas SET formula_name = ?
      WHERE formula_id = ?
        AND user_id = ?';