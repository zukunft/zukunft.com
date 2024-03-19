PREPARE word_insert FROM
    'INSERT INTO words (word_id,user_id,word_name,description,excluded,share_type_id,protect_id,phrase_type_id,view_id,plural,`values`)
          VALUES       (?,?,?,?,?,?,?,?,?,?,?)';