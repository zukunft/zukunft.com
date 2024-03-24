PREPARE source_norm_by_name (text) AS
    SELECT source_id,
           source_name,
           code_id,
           url,
           description,
           source_type_id,
           excluded,
           user_id
      FROM sources
     WHERE source_name = $1;