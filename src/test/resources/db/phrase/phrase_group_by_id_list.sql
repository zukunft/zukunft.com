SELECT l1.phrase_group_id
FROM phrase_group_word_links l1,
     phrase_group_word_links l2,
     phrase_group_word_links l3
WHERE                             l1.word_id = 1
  AND l2.word_id = l1.word_id AND l2.word_id = 2
  AND l3.word_id = l2.word_id AND l3.word_id = 3
GROUP BY l1.phrase_group_id;