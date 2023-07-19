PREPARE change_log_named_by_dsp_of_wrd (int,int) AS
    SELECT s.change_id,
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
 LEFT JOIN users l ON s.user_id = l.user_id
 LEFT JOIN change_fields l2 ON s.change_field_id = l2.change_field_id
     WHERE s.change_field_id = $1
       AND s.row_id = $2
  ORDER BY s.change_time DESC
     LIMIT 20;


PREPARE change_log_link_by_table (int,int,int) AS
    SELECT s.change_link_id,
           s.user_id,
           s.change_table_id,
           s.change_time,s.old_text_from,s.old_from_id,s.old_text_link,s.old_link_id,s.old_text_to,s.old_to_id,s.new_text_from,s.new_from_id,s.new_text_link,s.new_link_id,s.new_text_to,s.new_to_id,l.user_name,s.change_link_id,s.user_id,s.change_table_id,s.change_time,s.old_text_from,s.old_from_id,s.old_text_link,s.old_link_id,s.old_text_to,s.old_to_id,s.new_text_from,s.new_from_id,s.new_text_link,s.new_link_id,s.new_text_to,s.new_to_id,l.user_name FROM change_links s LEFT JOIN users l ON s.user_id = l.user_id LEFT JOIN users l ON s.user_id = l.user_id WHERE s.change_table_id = ? ORDER BY s.change_link_id DESC LIMIT 20';