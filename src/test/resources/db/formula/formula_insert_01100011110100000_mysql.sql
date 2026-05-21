PREPARE formula_insert_01100011110100000 FROM
    'INSERT INTO formulas (user_id, formula_name, formula_type_id, formula_text, resolved_text, latex, last_update)
          VALUES (?, ?, ?, ?, ?, ?, Now())';
