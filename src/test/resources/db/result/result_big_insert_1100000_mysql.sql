PREPARE result_big_insert_1100000 FROM
    'INSERT INTO results_big (group_id,user_id,numeric_value,last_update)
          VALUES (?,?,?,Now())';