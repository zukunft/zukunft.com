PREPARE formula_insert_01110011110100000 (bigint, text, text, smallint, text, text, text) AS
    INSERT INTO formulas (user_id, formula_name, description, formula_type_id, formula_text, resolved_text, latex, last_update)
         VALUES ($1, $2, $3, $4, $5, $6, $7, Now())
      RETURNING formula_id;
