PREPARE source_insert_0011110000 (text,text,bigint,text) AS
    INSERT INTO sources (source_name,description,source_type_id,url)
         VALUES         ($1,$2,$3,$4)
      RETURNING source_id;