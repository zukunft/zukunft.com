PREPARE term_view_insert_111500000_user FROM
    'INSERT INTO user_term_views (term_view_id, user_id, description, view_link_type_id)
          VALUES                 (?, ?, ?, ?)';