PREPARE query_test (text) AS
    SELECT config_id,
           config_name,
           value
      FROM config
     WHERE code_id = $1
       AND code_id IS NOT NULL;