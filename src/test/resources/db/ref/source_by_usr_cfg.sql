PREPARE source_by_usr_cfg (bigint, bigint) AS
    SELECT source_id,
           source_name,
           description,
           url,
           source_type_id,
           usage,
           excluded
      FROM user_sources
     WHERE source_id = $1
       AND user_id = $2;
