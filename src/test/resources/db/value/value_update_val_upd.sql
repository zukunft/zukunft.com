PREPARE value_update_val_upd (numeric, bigint) AS
    UPDATE values
       SET numeric_value = $1, last_update = Now()
     WHERE group_id = $2;