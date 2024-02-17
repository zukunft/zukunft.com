-- --------------------------------------------------------

--
-- indexes for table triples
--

CREATE UNIQUE INDEX triples_unique_idx  ON triples (from_phrase_id, verb_id, to_phrase_id);
CREATE INDEX triples_user_idx           ON triples (user_id);
CREATE INDEX triples_from_phrase_idx    ON triples (from_phrase_id);
CREATE INDEX triples_verb_idx           ON triples (verb_id);
CREATE INDEX triples_to_phrase_idx      ON triples (to_phrase_id);
CREATE INDEX triples_triple_name_idx    ON triples (triple_name);
CREATE INDEX triples_name_given_idx     ON triples (name_given);
CREATE INDEX triples_name_generated_idx ON triples (name_generated);
CREATE INDEX triples_phrase_type_idx    ON triples (phrase_type_id);
CREATE INDEX triples_view_idx           ON triples (view_id);

--
-- indexes for table user_triples
--

ALTER TABLE user_triples ADD CONSTRAINT user_triples_pkey PRIMARY KEY (triple_id, user_id, language_id);
CREATE INDEX user_triples_triple_idx         ON user_triples (triple_id);
CREATE INDEX user_triples_user_idx           ON user_triples (user_id);
CREATE INDEX user_triples_language_idx       ON user_triples (language_id);
CREATE INDEX user_triples_triple_name_idx    ON user_triples (triple_name);
CREATE INDEX user_triples_name_given_idx     ON user_triples (name_given);
CREATE INDEX user_triples_name_generated_idx ON user_triples (name_generated);
CREATE INDEX user_triples_phrase_type_idx    ON user_triples (phrase_type_id);
CREATE INDEX user_triples_view_idx           ON user_triples (view_id);
