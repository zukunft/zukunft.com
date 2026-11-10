PREPARE change_field_all (bigint, bigint) AS
    SELECT     change_field_id,
               change_field_name,
               description,
               code_id,
               table_id
          FROM change_fields
      ORDER BY change_field_id
         LIMIT $1
        OFFSET $2;