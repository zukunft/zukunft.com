PREPARE view_term_link_delete_user FROM
    'DELETE FROM user_view_term_links
           WHERE view_term_link_id = ?
             AND user_id = ?';