PREPARE result_big_update_1100000_user FROM
   'UPDATE user_results_big
       SET numeric_value = ?,
           last_update   = Now()
     WHERE group_id = ?
       AND user_id = ?';