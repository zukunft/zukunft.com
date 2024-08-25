PREPARE view_term_link_delete (bigint) AS
    DELETE FROM view_term_links
          WHERE view_term_link_id = $1;