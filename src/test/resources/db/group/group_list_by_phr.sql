PREPARE group_list_by_phr (int, int, int) AS
    SELECT
            s.group_id,
            s.group_name,
            s.description,
            l.phrase_id
       FROM groups s
  LEFT JOIN group_links l ON s.group_id = l.group_id
      WHERE l.phrase_id = $1
   ORDER BY s.group_id
      LIMIT $2
     OFFSET $3;