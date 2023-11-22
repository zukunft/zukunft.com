PREPARE value_p1_std_by_id (bigint) AS
    SELECT phrase_id_1,
           numeric_value,
           source_id,
           last_update,
           excluded,
           protect_id,
           user_id
    FROM values_prime
    WHERE phrase_id_1 = $1;