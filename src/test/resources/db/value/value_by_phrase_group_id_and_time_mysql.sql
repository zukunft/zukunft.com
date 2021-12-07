SELECT value_id
FROM `values`
WHERE phrase_group_id IN (SELECT l1.phrase_group_id
    FROM phrase_group_word_links l1
    WHERE l1.word_id = 1)
  AND time_word_id = 4 ;