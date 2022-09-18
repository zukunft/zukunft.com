PREPARE source_by_usr_cfg FROM
   'SELECT source_id,
           source_name,
           `url`,
           comment,
           source_type_id,
           excluded
      FROM user_sources
     WHERE source_id = ?
       AND user_id = ?';
