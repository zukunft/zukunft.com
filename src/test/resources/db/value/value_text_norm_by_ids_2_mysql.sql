PREPARE value_text_norm_by_ids_2 FROM
   'SELECT     group_id,
               text_value,
               source_id,
               last_update,
               excluded,
               protect_id,
               user_id
          FROM values_text
         WHERE group_id IN (?)';