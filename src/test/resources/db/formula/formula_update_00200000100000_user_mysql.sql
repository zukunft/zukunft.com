PREPARE formula_update_00200000100000_user FROM
    'UPDATE user_formulas
        SET formula_name = ?,
            last_update = Now()
      WHERE formula_id = ?
        AND user_id = ?';