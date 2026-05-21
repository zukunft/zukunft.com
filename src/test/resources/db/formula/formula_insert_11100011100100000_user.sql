PREPARE formula_insert_11100011100100000_user (bigint,bigint,text,smallint,text,text) AS
    INSERT INTO user_formulas (formula_id, user_id, formula_name, formula_type_id, formula_text, resolved_text, last_update)
         VALUES ($1, $2, $3, $4, $5, $6, Now())
      RETURNING formula_id;
