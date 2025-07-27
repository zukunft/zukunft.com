PREPARE term_view_delete_excluded (bigint) AS
    DELETE FROM term_views
          WHERE term_view_id = $1
            AND excluded = 1;