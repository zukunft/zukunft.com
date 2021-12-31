PREPARE phrase_group_by_id (int) AS
    SELECT phrase_group_id,
           phrase_group_name,
           auto_description,
           word_ids,
           triple_ids,
           id_order
      FROM phrase_groups
     WHERE phrase_group_id = $1;