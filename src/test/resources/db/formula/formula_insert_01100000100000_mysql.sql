PREPARE formula_insert_01100000100000 FROM
    'INSERT INTO formulas (user_id,formula_name,last_update)
          VALUES (?,?,Now())';