PREPARE group_list_by_phr FROM
   'SELECT
            s.group_id,
            s.group_name,
            s.description,
            l.phrase_id
       FROM `groups` s
  LEFT JOIN group_links l ON s.group_id = l.group_id
      WHERE l.phrase_id = ?
   ORDER BY s.group_id
      LIMIT ?
     OFFSET ?';