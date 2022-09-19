PREPARE value_by_usr_cfg (int, int) AS
    SELECT value_id,
           word_value,
           source_id,
           last_update,
           excluded,
           protect_id
      FROM user_values
     WHERE value_id = $1
       AND user_id = $2;
