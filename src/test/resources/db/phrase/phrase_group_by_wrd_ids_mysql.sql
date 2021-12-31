PREPARE phrase_group_by_wrd_ids FROM
   'SELECT phrase_group_id,
           phrase_group_name,
           auto_description,
           word_ids,
           triple_ids,
           id_order
      FROM phrase_groups
     WHERE word_ids = ?';
