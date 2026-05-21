PREPARE formula_insert_01100011110100000 (bigint, text, smallint, text, text, text) AS
    INSERT INTO formulas (user_id, formula_name, formula_type_id, formula_text, resolved_text, latex, last_update)
         VALUES ($1, $2, $3, $4, $5, $6, Now())
      RETURNING formula_id;
