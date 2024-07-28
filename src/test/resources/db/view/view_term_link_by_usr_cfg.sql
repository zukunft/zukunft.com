PREPARE view_term_link_by_usr_cfg (bigint, bigint) AS
        SELECT
                view_term_link_id,
                view_link_type_id,
                description,
                excluded,
                share_type_id,
                protect_id
           FROM user_view_term_links
          WHERE view_term_link_id = $1
            AND user_id = $2;