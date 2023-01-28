PREPARE source_type (int) AS
    SELECT source_type_id,
           type_name,
           description,
           code_id
      FROM source_types
  ORDER BY source_type_id
     LIMIT $1;