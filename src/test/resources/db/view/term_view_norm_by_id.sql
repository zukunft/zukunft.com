PREPARE term_view_norm_by_id
    (bigint) AS
        SELECT
                term_view_id,
                term_id,
                view_link_type_id,
                view_id,
                description,
                excluded,
                share_type_id,
                protect_id,
                user_id
           FROM term_views
          WHERE term_view_id = $1;