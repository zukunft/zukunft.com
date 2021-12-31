PREPARE source_std_by_id (int) AS
    SELECT source_id,
           source_name,
           source_name,
           code_id,
           url,
           comment,
           source_type_id,
           excluded,
           user_id
      FROM sources
     WHERE source_id = $1;