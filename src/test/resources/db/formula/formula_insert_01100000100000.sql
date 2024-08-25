PREPARE formula_insert_01100000100000 (bigint,text) AS
    INSERT INTO formulas (user_id,formula_name,last_update)
         VALUES ($1,$2,Now())
      RETURNING formula_id;