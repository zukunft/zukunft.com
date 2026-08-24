PREPARE source_norm_by_ids FROM
   'SELECT     source_id,
               source_name,
               code_id,
               `usage`,
               `url`,
               doi,
               description,
               source_type_id,
               excluded,
               share_type_id,
               protect_id,
               user_id
          FROM sources
         WHERE source_id IN (?)';