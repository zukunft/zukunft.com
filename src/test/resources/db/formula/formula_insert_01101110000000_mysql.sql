PREPARE formula_insert_01101110000000 FROM
    'INSERT INTO formulas (user_id, formula_name, formula_type_id, formula_text, resolved_text)
          VALUES (?, ?, ?, ?, ?)';
