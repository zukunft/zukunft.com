PREPARE view_by_usr_cfg (bigint, bigint) AS
    SELECT view_id,
           view_name,
           description,
           view_type_id,
           view_style_id,
           excluded,
           share_type_id,
           protect_id
      FROM user_views
     WHERE view_id = $1
       AND user_id = $2;
