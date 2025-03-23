PREPARE value_big_by_usr_cfg (text, bigint, bigint) AS
    SELECT group_id,
           numeric_value,
           source_id,
           last_update,
           excluded,protect_id
      FROM user_values_big
     WHERE group_id = $1
       AND user_id = $2
       AND source_id = $3;