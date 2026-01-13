PREPARE result_update_02100001 (numeric,smallint,text) AS
    UPDATE results
       SET numeric_value = $1,
           last_update   = Now(),
           protect_id    = $2
     WHERE group_id = $3;