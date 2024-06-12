PREPARE value_update_210000_user (numeric, text, bigint) AS
    UPDATE user_values
       SET numeric_value = $1,
           last_update   = Now()
     WHERE group_id = $2
       AND user_id = $3;