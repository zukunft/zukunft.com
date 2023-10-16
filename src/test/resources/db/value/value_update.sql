PREPARE value_update (numeric, timestamp, bigint) AS
    UPDATE values
       SET numeric_value = $1, last_update = $2
     WHERE group_id = $3;