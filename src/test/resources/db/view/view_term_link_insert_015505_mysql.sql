PREPARE view_term_link_insert_015505 FROM
    'INSERT INTO view_term_links
                 (user_id, view_id, term_id, view_link_type_id)
          VALUES (?, ?, ?, ?)';