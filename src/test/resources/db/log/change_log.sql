SELECT
    c.change_link_id,
    c.change_time AS time,
    u.user_name,
    a.change_action_name AS type,
    c.new_text_link AS link,
    c.row_id,
    c.old_text_to AS old,
    c.new_text_to AS new
FROM change_links c,
     change_actions a,
     change_tables t,
     users u
WHERE ( c.change_table_id = 1 )
  AND c.change_table_id  = t.change_table_id
  AND c.change_action_id = a.change_action_id
  AND c.user_id = u.user_id
  AND c.user_id = 1
ORDER BY c.change_time DESC
LIMIT 20;