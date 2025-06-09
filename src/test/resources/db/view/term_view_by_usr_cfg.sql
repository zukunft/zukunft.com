PREPARE term_view_by_usr_cfg (bigint, bigint) AS
        SELECT
                term_view_id,
                view_link_type_id,
                description,
                excluded,
                share_type_id,
                protect_id
           FROM user_term_views
          WHERE term_view_id = $1
            AND user_id = $2;