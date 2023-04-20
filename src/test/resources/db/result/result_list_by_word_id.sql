PREPARE result_list_by_word_id (int) AS
    SELECT s.result_id,
           s.formula_id,
           s.user_id,
           s.source_phrase_group_id,
           s.source_time_id,
           s.phrase_group_id,
           s.result,
           s.last_update,
           s.dirty,
           l.phrase_group_id
      FROM results s
 LEFT JOIN phrase_group_word_links l ON s.phrase_group_id = l.phrase_group_id
     WHERE l.word_id = $1;