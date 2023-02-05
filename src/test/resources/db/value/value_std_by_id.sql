PREPARE value_std_by_id (int) AS
    SELECT value_id,
           phrase_group_id,
           word_value,
           source_id,
           last_update,
           excluded,
           protect_id,
           user_id
    FROM values
    WHERE value_id = $1;