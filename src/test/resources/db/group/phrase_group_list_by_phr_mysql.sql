PREPARE phrase_group_list_by_phr FROM
   'SELECT
            s.phrase_group_id,
            s.phrase_group_name,
            s.auto_description,
            s.word_ids,
            s.triple_ids,
            s.id_order,
            l.word_id
       FROM phrase_groups s
  LEFT JOIN phrase_group_word_links l ON s.phrase_group_id = l.phrase_group_id
      WHERE l.word_id = ?
   ORDER BY s.phrase_group_id
      LIMIT 20';