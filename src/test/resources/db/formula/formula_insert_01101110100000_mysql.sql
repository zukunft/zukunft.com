PREPARE formula_insert_01101110100000 FROM
    'INSERT INTO formulas (user_id, formula_name, formula_type_id, formula_text, resolved_text, last_update)
          VALUES (?, ?, ?, ?, ?, Now())';
