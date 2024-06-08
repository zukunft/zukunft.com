PREPARE result_big_update_1100000_user (numeric, text, bigint) AS
    UPDATE user_results_big
       SET numeric_value = $1,
           last_update   = Now()
     WHERE group_id = $2
       AND user_id = $3;