PREPARE result_big_insert FROM
    'INSERT INTO results_big (group_id,user_id,numeric_value,last_update,formula_id,source_group_id)
          VALUES (?,?,?,Now(),?,?)';