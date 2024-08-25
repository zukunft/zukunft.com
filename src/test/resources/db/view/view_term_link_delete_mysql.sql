PREPARE view_term_link_delete FROM
   'DELETE FROM view_term_links
          WHERE view_term_link_id = ?';