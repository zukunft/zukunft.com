PREPARE change_by_wrd FROM
    'SELECT
                s.change_id,
                s.user_id,
                s.change_time,
                s.change_action_id,
                s.change_field_id,
                s.row_id,
                s.old_value,
                s.old_id,
                s.new_value,
                s.new_id,
                l.user_name,
                l2.table_id
           FROM changes s
      LEFT JOIN users l          ON s.user_id = l.user_id
      LEFT JOIN change_fields l2 ON s.change_field_id = l2.change_field_id
          WHERE l2.table_id = ?
            AND s.row_id = ?
       ORDER BY s.change_time
     DESC LIMIT ?
         OFFSET ?';