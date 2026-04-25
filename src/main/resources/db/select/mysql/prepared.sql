/* get the prepared SQL statements from the database*/
SELECT
    STATEMENT_NAME as name,
    SQL_TEXT as statement
FROM performance_schema.prepared_statements_instances