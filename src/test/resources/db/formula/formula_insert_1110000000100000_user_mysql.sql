PREPARE formula_insert_1110000000100000_user FROM
    'INSERT INTO user_formulas (formula_id,user_id,formula_name,last_update)
          VALUES (?,?,?,Now())';
