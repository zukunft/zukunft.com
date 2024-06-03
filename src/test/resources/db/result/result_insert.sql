PREPARE result_insert (text,bigint,numeric,bigint,bigint) AS
    INSERT INTO results (group_id,user_id,numeric_value,last_update,formula_id,source_group_id)
         VALUES ($1,$2,$3,Now(),$4,$5)
      RETURNING group_id;