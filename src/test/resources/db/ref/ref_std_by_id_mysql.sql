PREPARE ref_std_by_id FROM
    'SELECT ref_id,
            phrase_id,
            external_key,
            ref_type_id,
            source_id,
            `url`,
            description,
            excluded,
            user_id
       FROM refs
      WHERE ref_id = ?';