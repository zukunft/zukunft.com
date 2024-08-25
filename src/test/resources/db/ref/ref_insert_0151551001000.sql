PREPARE ref_insert_0151551001000 (bigint,bigint,text,smallint,text) AS
    INSERT INTO refs (user_id, phrase_id, external_key, ref_type_id, description)
         VALUES ($1, $2, $3, $4, $5)
      RETURNING ref_id;