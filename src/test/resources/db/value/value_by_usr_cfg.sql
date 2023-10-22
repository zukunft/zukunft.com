PREPARE value_by_usr_cfg (bigint, bigint) AS
    SELECT group_id,
           numeric_value,
           source_id,
           last_update,
           excluded,
           protect_id
      FROM user_values_prime
     WHERE group_id = $1
       AND user_id = $2;
