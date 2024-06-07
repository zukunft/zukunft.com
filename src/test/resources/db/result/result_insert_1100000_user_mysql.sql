PREPARE result_insert_1100000_user FROM
    'INSERT INTO user_results (group_id,user_id,numeric_value,last_update)
          VALUES (?,?,?,Now())';