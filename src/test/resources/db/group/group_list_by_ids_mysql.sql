PREPARE group_list_by_ids FROM
   'SELECT
            group_id,
            group_name,
            description
       FROM `groups`
      WHERE group_id IN (?)
   ORDER BY group_id
      LIMIT ?
     OFFSET ?';