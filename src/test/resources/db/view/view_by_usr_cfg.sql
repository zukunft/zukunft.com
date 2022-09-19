PREPARE view_by_usr_cfg (int, int) AS
    SELECT view_id,
           view_name,
           comment,
           view_type_id,
           excluded,
           share_type_id,
           protect_id
      FROM user_views
     WHERE view_id = $1
       AND user_id = $2;
