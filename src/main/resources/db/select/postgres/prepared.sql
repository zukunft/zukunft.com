/* get the prepared SQL statements from the database*/
SELECT name,
       statement,
       parameter_types
  FROM pg_prepared_statements
ORDER BY
    name;