PREPARE value_p16_update_val_upd (numeric, text) AS
    UPDATE values
       SET numeric_value = $1, last_update = Now()
     WHERE group_id = $2;