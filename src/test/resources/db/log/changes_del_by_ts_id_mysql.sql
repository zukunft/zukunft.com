PREPARE changes_del_by_ts_id FROM
    'DELETE FROM changes
           WHERE row_id = ?
             AND change_field_id IN (SELECT change_field_id
                                       FROM change_fields
                                      WHERE table_id IN (1,2))';