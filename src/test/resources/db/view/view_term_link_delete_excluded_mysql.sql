PREPARE view_term_link_delete_excluded FROM
   'DELETE FROM view_term_links
          WHERE view_term_link_id = ?
            AND excluded = 1';