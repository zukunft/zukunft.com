PREPARE value_update_0010000 FROM
    'UPDATE `values`
        SET last_update = Now()
      WHERE group_id = ?';
