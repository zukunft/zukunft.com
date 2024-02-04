SELECT l1.group_id
FROM group_links l1,
     group_links l2,
     group_links l3
WHERE                                 l1.phrase_id = 1
  AND l2.phrase_id = l1.phrase_id AND l2.phrase_id = 2
  AND l3.phrase_id = l2.phrase_id AND l3.phrase_id = 3
GROUP BY l1.group_id;