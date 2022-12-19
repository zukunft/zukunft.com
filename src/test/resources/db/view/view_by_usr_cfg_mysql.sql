PREPARE view_by_usr_cfg FROM
   'SELECT view_id,
           view_name,
           description,
           view_type_id,
           excluded,
           share_type_id,
           protect_id
      FROM user_views
     WHERE view_id = ?
       AND user_id = ?';
