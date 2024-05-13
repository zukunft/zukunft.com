INSERT INTO change_links
            (user_id, change_action_id, change_table_id, new_from_id, new_link_id, new_to_id, row_id)
     VALUES ($1, $2, $3, $4, $5, $6, $7)
  RETURNING change_link_id;