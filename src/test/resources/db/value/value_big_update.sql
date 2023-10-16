PREPARE value_big_update (numeric, timestamp, bigint) AS
    UPDATE values_big
       SET numeric_value = $1, last_update = $2
     WHERE group_id = $3;