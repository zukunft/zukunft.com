PREPARE formula_insert_01100011100100000 FROM
    'INSERT INTO formulas (user_id, formula_name, formula_type_id, formula_text, resolved_text, last_update)
          VALUES (?, ?, ?, ?, ?, Now())';
