PREPARE view_std_by_id (int) AS
    SELECT view_id,
           view_name,
           code_id,
           description,
           view_type_id,
           excluded,
           share_type_id,
           protect_id,
           user_id
      FROM views
     WHERE view_id = $1;