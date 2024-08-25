PREPARE value_update_0010000 (text) AS
    UPDATE values
       SET last_update = Now()
     WHERE group_id = $1;