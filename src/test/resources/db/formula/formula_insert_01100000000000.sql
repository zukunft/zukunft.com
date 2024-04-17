PREPARE formula_insert_01100000000000 (bigint,text) AS
    INSERT INTO formulas (user_id,formula_name)
         VALUES ($1,$2)
      RETURNING formula_id;