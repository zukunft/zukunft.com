PREPARE source_by_usr_cfg (int, int) AS
    SELECT source_id,
           source_name,
           description,
           source_type_id,
           excluded,
           url
      FROM user_sources
     WHERE source_id = $1
       AND user_id = $2;
