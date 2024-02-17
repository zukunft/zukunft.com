-- --------------------------------------------------------

--
-- indexes for table triples
--

ALTER TABLE triples
    ADD PRIMARY KEY (triple_id),
    ADD UNIQUE KEY triples_unique_idx  (from_phrase_id, verb_id, to_phrase_id),
    ADD KEY triples_user_idx           (user_id),
    ADD KEY triples_from_phrase_idx    (from_phrase_id),
    ADD KEY triples_verb_idx           (verb_id),
    ADD KEY triples_to_phrase_idx      (to_phrase_id),
    ADD KEY triples_triple_name_idx    (triple_name),
    ADD KEY triples_name_given_idx     (name_given),
    ADD KEY triples_name_generated_idx (name_generated),
    ADD KEY triples_phrase_type_idx    (phrase_type_id),
    ADD KEY triples_view_idx           (view_id);

--
-- indexes for table user_triples
--

ALTER TABLE user_triples ADD PRIMARY KEY (triple_id, user_id, language_id),
    ADD KEY user_triples_triple_idx         (triple_id),
    ADD KEY user_triples_user_idx           (user_id),
    ADD KEY user_triples_language_idx       (language_id),
    ADD KEY user_triples_triple_name_idx    (triple_name),
    ADD KEY user_triples_name_given_idx     (name_given),
    ADD KEY user_triples_name_generated_idx (name_generated),
    ADD KEY user_triples_phrase_type_idx    (phrase_type_id),
    ADD KEY user_triples_view_idx           (view_id);
