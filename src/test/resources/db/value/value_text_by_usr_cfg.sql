PREPARE value_text_by_usr_cfg (text, bigint, bigint) AS
    SELECT     group_id,
               text_value,
               source_id,
               last_update,
               excluded,
               protect_id
          FROM user_values_text
         WHERE group_id = $1
           AND user_id = $2
           AND source_id IS NOT DISTINCT FROM $3;