PREPARE formula_update_002000000100000 FROM
    'UPDATE formulas
        SET formula_name = ?,
            last_update = Now()
      WHERE formula_id = ?';