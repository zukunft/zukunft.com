PREPARE term_view_by_std_link_ids FROM
       'SELECT
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
          WHERE view_id = ?
            AND term_id = ?';