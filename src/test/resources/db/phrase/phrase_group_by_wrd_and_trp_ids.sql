PREPARE phrase_group_by_wrd_and_trp_ids (text, text) AS
    SELECT phrase_group_id,
           phrase_group_name,
           auto_description,
           word_ids,
           triple_ids,
           id_order
      FROM phrase_groups
     WHERE triple_ids = $1
       AND word_ids = $2;
