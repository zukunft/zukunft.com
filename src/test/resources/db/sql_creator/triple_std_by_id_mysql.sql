SELECT triple_id,
       from_phrase_id,
       to_phrase_id,
       verb_id,
       name_given,
       description,
       phrase_type_id,
       excluded
  FROM triples
 WHERE triple_id = 1;