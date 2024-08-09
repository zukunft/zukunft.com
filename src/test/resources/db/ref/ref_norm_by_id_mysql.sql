PREPARE ref_norm_by_id FROM
    'SELECT ref_id,
            phrase_id,
            ref_type_id,
            external_key,
            `url`,
            description,
            source_id,
            excluded,
            share_type_id,
            protect_id,
            user_id
       FROM refs
      WHERE ref_id = ?';