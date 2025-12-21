PREPARE formula_insert_1110001110100000_user FROM
    'INSERT INTO user_formulas (formula_id, user_id, formula_name, formula_type_id, formula_text, resolved_text, last_update)
          VALUES (?, ?, ?, ?, ?, ?, Now())';
