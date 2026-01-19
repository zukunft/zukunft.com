PREPARE element_update_100202 FROM
   'UPDATE elements
       SET element_id      = ?,
           element_type_id = ?,
           ref_id          = ?
     WHERE element_id = ?';
