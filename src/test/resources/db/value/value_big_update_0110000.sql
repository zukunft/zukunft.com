PREPARE value_big_update_0110000 (numeric, text) AS
    UPDATE values_big
       SET numeric_value = $1, last_update = Now()
     WHERE group_id = $2;