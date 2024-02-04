PREPARE change_log_by_user (bigint, bigint) AS
    SELECT c.change_id,
           c.change_time AS time,
           u.user_name,
           a.change_action_name AS type,
           t.description AS type_table,
           f.description AS type_field,
           f.code_id,
           c.row_id,
           c.old_value AS old,
           c.new_value AS new
    FROM changes c,
         change_actions a,
         change_fields f,
         change_tables t,
         users u
    WHERE
    f.change_field_id  = c.change_field_id
                 AND f.table_id  = t.change_table_id
                 AND c.change_action_id = a.change_action_id
                 AND $1
            ORDER BY c.change_time DESC
               LIMIT $2;