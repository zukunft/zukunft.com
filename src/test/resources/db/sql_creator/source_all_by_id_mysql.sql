SELECT source_id,
       source_name,
       code_id,
       `url`,
       description,
       source_type_id
  FROM sources
 WHERE source_id = ?;