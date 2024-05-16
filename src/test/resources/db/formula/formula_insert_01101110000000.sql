PREPARE formula_insert_01101110000000 (bigint, text, bigint, text, text) AS
    INSERT INTO formulas (user_id, formula_name, formula_type_id, formula_text, resolved_text)
         VALUES ($1, $2, $3, $4, $5)
      RETURNING formula_id;
