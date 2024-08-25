PREPARE formula_insert_11100000100000_user (bigint,bigint,text) AS
    INSERT INTO user_formulas (formula_id,user_id,formula_name,last_update)
         VALUES ($1,$2,$3,Now())
      RETURNING formula_id;
