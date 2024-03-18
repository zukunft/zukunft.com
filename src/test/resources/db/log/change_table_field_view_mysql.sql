--
-- structure for view change_table_fields
--

CREATE OR REPLACE  VIEW change_table_fields AS
SELECT f.change_field_id  AS change_table_field_id,
       CONCAT(t.change_table_id, f.change_field_name) AS change_table_field_name,
       f.description,
       IF(f.code_id IS NULL ,
          CONCAT(t.change_table_id, f.change_field_name) ,
          f.code_id) AS code_id
FROM change_tables AS t, change_fields AS f
WHERE t.change_table_id = f.table_id;