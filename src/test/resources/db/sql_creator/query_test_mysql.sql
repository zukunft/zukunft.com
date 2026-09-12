PREPARE query_test FROM
    'SELECT config_id,
            config_name,
            `value`
       FROM config
      WHERE code_id = ?';