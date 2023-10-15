PREPARE source_type (bigint, bigint) AS
    SELECT source_type_id,
           type_name,
           description,
           code_id
      FROM source_types
  ORDER BY source_type_id
     LIMIT $1
    OFFSET $2;