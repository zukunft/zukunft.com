PREPARE result_update_01100000 (numeric,text) AS
    UPDATE results
       SET numeric_value = $1, last_update = Now()
     WHERE group_id = $2;