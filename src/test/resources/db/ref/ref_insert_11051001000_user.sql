PREPARE ref_insert_11051001000_user (bigint,bigint,text) AS
    INSERT INTO user_refs (ref_id,user_id,description)
         VALUES ($1,$2,$3)
      RETURNING ref_id;