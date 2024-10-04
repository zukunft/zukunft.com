/* get the function names from the database*/
SELECT
    routines.routine_name
  FROM
    information_schema.routines
 WHERE
    routines.specific_schema = 'public'
ORDER BY
    routines.routine_name;