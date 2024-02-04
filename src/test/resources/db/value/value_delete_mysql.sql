PREPARE value_delete FROM
   'DELETE FROM `values`
     WHERE group_id = ?';