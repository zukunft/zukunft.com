PREPARE value_update_0210000 (numeric, text) AS
    UPDATE values
       SET numeric_value = $1, last_update = Now()
     WHERE group_id = $2;