PREPARE formula_insert_011001110100000 (bigint, text, bigint, text, text) AS
    INSERT INTO formulas (user_id, formula_name, formula_type_id, formula_text, resolved_text, last_update)
         VALUES ($1, $2, $3, $4, $5, Now())
      RETURNING formula_id;
