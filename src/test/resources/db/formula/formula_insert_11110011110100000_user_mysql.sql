PREPARE formula_insert_11110011110100000_user FROM
    'INSERT INTO user_formulas (formula_id, user_id, formula_name, description, formula_type_id, formula_text, resolved_text, latex, last_update)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, Now())';
