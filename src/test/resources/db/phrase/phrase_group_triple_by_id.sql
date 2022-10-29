PREPARE phrase_group_triple_by_id (int) AS
    SELECT phrase_group_triple_id,
           phrase_group_id,
           word_id
      FROM phrase_group_triples
     WHERE phrase_group_triple_id = $1;