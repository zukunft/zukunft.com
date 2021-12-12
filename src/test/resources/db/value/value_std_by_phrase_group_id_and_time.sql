SELECT value_id,
       phrase_group_id,
       time_word_id,
       word_value,
       source_id,
       last_update,
       excluded,
       share_type_id,
       protection_type_id,
       user_id
FROM values
WHERE value_id = $1;