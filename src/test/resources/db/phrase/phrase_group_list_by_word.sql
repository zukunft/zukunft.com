PREPARE phrase_group_list_by_word_id (int) AS
    SELECT s.phrase_group_id,
           s.phrase_group_name,
           s.phrase_group_name,
           s.auto_description,
           s.word_ids,
           s.triple_ids,
           l.word_id
    FROM phrase_groups s
             LEFT JOIN phrase_group_word_links l ON s.phrase_group_id = l.phrase_group_id
    WHERE l.word_id = $1;