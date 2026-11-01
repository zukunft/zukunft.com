PREPARE system_time_type_all (bigint, bigint) AS
    SELECT     system_time_type_id,
               type_name,
               description,
               code_id
          FROM system_time_types
      ORDER BY system_time_type_id
         LIMIT $1
        OFFSET $2;