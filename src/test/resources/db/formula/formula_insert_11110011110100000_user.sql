PREPARE formula_insert_11110011110100000_user (bigint,bigint,text,text,smallint,text,text,text) AS
    INSERT INTO user_formulas (formula_id, user_id, formula_name, description, formula_type_id, formula_text, resolved_text, latex, last_update)
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, Now())
      RETURNING formula_id;
