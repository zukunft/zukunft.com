PREPARE change_link_by_table FROM
   'SELECT s.change_link_id,
           s.user_id,
           s.change_table_id,
           s.change_time,
           s.old_text_from,
           s.old_from_id,
           s.old_text_link,
           s.old_link_id,
           s.old_text_to,
           s.old_to_id,
           s.new_text_from,
           s.new_from_id,
           s.new_text_link,
           s.new_link_id,
           s.new_text_to,
           s.new_to_id,
           l.user_name
    FROM change_links s
             LEFT JOIN users l ON s.user_id = l.user_id
    WHERE s.change_table_id = ?
    ORDER BY s.change_link_id DESC';
