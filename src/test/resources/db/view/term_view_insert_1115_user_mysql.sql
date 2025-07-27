PREPARE term_view_insert_1115_user FROM
    'INSERT INTO user_term_views
                 (term_view_id, user_id, description, view_link_type_id)
          VALUES (?, ?, ?, ?)';