PREPARE word_link_by_usr_cfg FROM
   'SELECT word_link_id,
           name_given,
           name_generated,
           description,
           `values`,
           excluded,
           share_type_id,
           protect_id
      FROM user_word_links
     WHERE word_link_id = ?
       AND user_id = ?';
