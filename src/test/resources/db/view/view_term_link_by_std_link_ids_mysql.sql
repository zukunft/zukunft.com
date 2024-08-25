PREPARE view_term_link_by_std_link_ids FROM
       'SELECT
                view_term_link_id,
                term_id,
                view_link_type_id,
                view_id,
                description,
                excluded,
                share_type_id,
                protect_id,
                user_id
           FROM view_term_links
          WHERE view_id = ?
            AND term_id = ?';