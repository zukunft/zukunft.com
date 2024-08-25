PREPARE result_big_update_02100000 (numeric, text) AS
    UPDATE results_big
       SET numeric_value = $1,
           last_update   = Now()
     WHERE group_id = $2;