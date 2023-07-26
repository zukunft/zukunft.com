PREPARE triple_by_usr_cfg FROM
   'SELECT triple_id,
           triple_name,
           name_given,
           name_generated,
           description,
           phrase_type_id,
           `values`,
           excluded,
           share_type_id,
           protect_id
      FROM user_triples
     WHERE triple_id = ?
       AND user_id = ?';
