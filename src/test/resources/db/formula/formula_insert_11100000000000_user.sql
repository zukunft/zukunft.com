PREPARE formula_insert_11100000000000_user (bigint,bigint,text) AS
    INSERT INTO user_formulas (formula_id,user_id,formula_name)
         VALUES ($1,$2,$3)
      RETURNING formula_id;
