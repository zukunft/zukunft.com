PREPARE change_table_by_code_id (text) AS
    SELECT change_table_id,
           change_table_name,
           description,
           code_id
      FROM change_tables
     WHERE code_id = $1
       AND code_id IS NOT NULL
  ORDER BY change_table_id;