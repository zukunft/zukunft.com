PREPARE ref_norm_by_link_type_ids (bigint,bigint,text) AS
    SELECT ref_id,
           phrase_id,
           ref_type_id,
           impact,
           last_update,
           external_key,
           url,
           description,
           source_id,
           excluded,
           share_type_id,
           protect_id,
           user_id
      FROM refs
     WHERE phrase_id = $1
       AND ref_type_id = $2
       AND external_key = $3;