PREPARE change_field_all FROM
   'SELECT     change_field_id,
               change_field_name,
               description,
               code_id,
               table_id
          FROM change_fields
      ORDER BY change_field_id
         LIMIT ?
        OFFSET ?';