PREPARE changes_del_by_ts_id (bigint) AS
    DELETE FROM changes
          WHERE row_id = $1
            AND change_field_id IN (SELECT change_field_id
                                      FROM change_fields
                                     WHERE table_id IN (1,2));