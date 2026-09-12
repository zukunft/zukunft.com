PREPARE source_type_by_id (bigint) AS
    SELECT source_type_id,
           type_name
      FROM source_types
     WHERE source_type_id = $1;