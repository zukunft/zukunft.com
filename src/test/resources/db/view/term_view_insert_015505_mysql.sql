PREPARE term_view_insert_015505 FROM
    'INSERT INTO term_views
                 (user_id, view_id, term_id, view_link_type_id)
          VALUES (?, ?, ?, ?)';