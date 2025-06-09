PREPARE term_view_by_usr_cfg FROM
   'SELECT
            term_view_id,
            view_link_type_id,
            description,
            excluded,
            share_type_id,
            protect_id
       FROM user_term_views
      WHERE term_view_id = ?
        AND user_id = ?';