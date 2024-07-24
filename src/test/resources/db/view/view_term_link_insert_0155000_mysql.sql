PREPARE view_term_link_insert_0155000 FROM
    'INSERT INTO view_term_links
                 (user_id, view_id, term_id)
          VALUES (?, ?, ?)';