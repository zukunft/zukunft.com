PREPARE source_by_usr_cfg FROM
   'SELECT source_id,
           source_name,
           description,
           `url`,
           source_type_id,
           `usage`,
           excluded
      FROM user_sources
     WHERE source_id = ?
       AND user_id = ?';
