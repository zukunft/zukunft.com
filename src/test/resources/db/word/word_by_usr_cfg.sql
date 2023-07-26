PREPARE word_by_usr_cfg (int,int) AS
    SELECT
            word_id,
            word_name,
            values,
            plural,
            description,
            phrase_type_id,
            view_id,
            excluded,
            share_type_id,
            protect_id
       FROM user_words
      WHERE word_id = $1
        AND user_id = $2;