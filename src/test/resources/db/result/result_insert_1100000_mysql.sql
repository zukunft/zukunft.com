PREPARE result_insert_1100000 FROM
    'INSERT INTO results (group_id,user_id,numeric_value,last_update)
          VALUES (?,?,?,Now())';