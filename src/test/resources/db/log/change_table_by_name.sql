PREPARE change_table_by_name (text) AS
    SELECT change_table_id,
           change_table_name,
           description,
           code_id
      FROM change_tables
     WHERE change_table_name = $1
  ORDER BY change_table_id;