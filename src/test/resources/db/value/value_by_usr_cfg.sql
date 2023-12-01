PREPARE value_by_usr_cfg (bigint, bigint) AS
    SELECT phrase_id_1,
           numeric_value,
           source_id,
           last_update,
           excluded,
           protect_id
      FROM user_values_prime
     WHERE phrase_id_1 = $1
       AND user_id = $2;
