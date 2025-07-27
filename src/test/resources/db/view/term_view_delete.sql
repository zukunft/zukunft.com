PREPARE term_view_delete (bigint) AS
    DELETE FROM term_views
          WHERE term_view_id = $1;