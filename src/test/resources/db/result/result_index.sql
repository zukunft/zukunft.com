-- --------------------------------------------------------

--
-- indexes for table results_standard_prime
--
CREATE UNIQUE INDEX results_standard_prime_pkey ON results_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_standard_prime_phrase_id_1_idx ON results_standard_prime (phrase_id_1);
CREATE INDEX results_standard_prime_phrase_id_2_idx ON results_standard_prime (phrase_id_2);
CREATE INDEX results_standard_prime_phrase_id_3_idx ON results_standard_prime (phrase_id_3);
CREATE INDEX results_standard_prime_phrase_id_4_idx ON results_standard_prime (phrase_id_4);

--
-- indexes for table results_standard
--

--
-- indexes for table results
--
CREATE INDEX results_source_group_idx ON results (source_group_id);
CREATE INDEX results_formula_idx ON results (formula_id);
CREATE INDEX results_user_idx ON results (user_id);

--
-- indexes for table user_results
--
ALTER TABLE user_results ADD CONSTRAINT user_results_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_source_group_idx ON user_results (source_group_id);
CREATE INDEX user_results_user_idx ON user_results (user_id);
CREATE INDEX user_results_formula_idx ON user_results (formula_id);

--
-- indexes for table results_prime
--
CREATE UNIQUE INDEX results_prime_pkey ON results_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_prime_phrase_id_1_idx ON results_prime (phrase_id_1);
CREATE INDEX results_prime_phrase_id_2_idx ON results_prime (phrase_id_2);
CREATE INDEX results_prime_phrase_id_3_idx ON results_prime (phrase_id_3);
CREATE INDEX results_prime_phrase_id_4_idx ON results_prime (phrase_id_4);
CREATE INDEX results_prime_source_group_idx ON results_prime (source_group_id);
CREATE INDEX results_prime_formula_idx ON results_prime (formula_id);
CREATE INDEX results_prime_user_idx ON results_prime (user_id);

--
-- indexes for table user_results_prime
--
CREATE UNIQUE INDEX user_results_prime_pkey ON user_results_prime (phrase_id_1,phrase_id_2,phrase_id_3,phrase_id_4, user_id);
CREATE INDEX user_results_prime_phrase_id_1_idx ON user_results_prime (phrase_id_1);
CREATE INDEX user_results_prime_phrase_id_2_idx ON user_results_prime (phrase_id_2);
CREATE INDEX user_results_prime_phrase_id_3_idx ON user_results_prime (phrase_id_3);
CREATE INDEX user_results_prime_phrase_id_4_idx ON user_results_prime (phrase_id_4);
CREATE INDEX user_results_prime_source_group_idx ON user_results_prime (source_group_id);
CREATE INDEX user_results_prime_user_idx ON user_results_prime (user_id);
CREATE INDEX user_results_prime_formula_idx ON user_results_prime (formula_id);

--
-- indexes for table results_big
--
CREATE INDEX results_big_source_group_idx ON results_big (source_group_id);
CREATE INDEX results_big_formula_idx ON results_big (formula_id);
CREATE INDEX results_big_user_idx ON results_big (user_id);

--
-- indexes for table user_results_big
--
ALTER TABLE user_results_big ADD CONSTRAINT user_results_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_big_source_group_idx ON user_results_big (source_group_id);
CREATE INDEX user_results_big_user_idx ON user_results_big (user_id);
CREATE INDEX user_results_big_formula_idx ON user_results_big (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_text_standard_prime
--
CREATE UNIQUE INDEX results_text_standard_prime_pkey ON results_text_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_text_standard_prime_phrase_id_1_idx ON results_text_standard_prime (phrase_id_1);
CREATE INDEX results_text_standard_prime_phrase_id_2_idx ON results_text_standard_prime (phrase_id_2);
CREATE INDEX results_text_standard_prime_phrase_id_3_idx ON results_text_standard_prime (phrase_id_3);
CREATE INDEX results_text_standard_prime_phrase_id_4_idx ON results_text_standard_prime (phrase_id_4);

--
-- indexes for table results_text_standard
--

--
-- indexes for table results_text
--
CREATE INDEX results_text_source_group_idx ON results_text (source_group_id);
CREATE INDEX results_text_formula_idx ON results_text (formula_id);
CREATE INDEX results_text_user_idx ON results_text (user_id);

--
-- indexes for table user_results_text
--
ALTER TABLE user_results_text ADD CONSTRAINT user_results_text_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_text_source_group_idx ON user_results_text (source_group_id);
CREATE INDEX user_results_text_user_idx ON user_results_text (user_id);
CREATE INDEX user_results_text_formula_idx ON user_results_text (formula_id);

--
-- indexes for table results_text_prime
--
CREATE UNIQUE INDEX results_text_prime_pkey ON results_text_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_text_prime_phrase_id_1_idx ON results_text_prime (phrase_id_1);
CREATE INDEX results_text_prime_phrase_id_2_idx ON results_text_prime (phrase_id_2);
CREATE INDEX results_text_prime_phrase_id_3_idx ON results_text_prime (phrase_id_3);
CREATE INDEX results_text_prime_phrase_id_4_idx ON results_text_prime (phrase_id_4);
CREATE INDEX results_text_prime_source_group_idx ON results_text_prime (source_group_id);
CREATE INDEX results_text_prime_formula_idx ON results_text_prime (formula_id);
CREATE INDEX results_text_prime_user_idx ON results_text_prime (user_id);

--
-- indexes for table user_results_text_prime
--
CREATE UNIQUE INDEX user_results_text_prime_pkey ON user_results_text_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_results_text_prime_phrase_id_1_idx ON user_results_text_prime (phrase_id_1);
CREATE INDEX user_results_text_prime_phrase_id_2_idx ON user_results_text_prime (phrase_id_2);
CREATE INDEX user_results_text_prime_phrase_id_3_idx ON user_results_text_prime (phrase_id_3);
CREATE INDEX user_results_text_prime_phrase_id_4_idx ON user_results_text_prime (phrase_id_4);
CREATE INDEX user_results_text_prime_source_group_idx ON user_results_text_prime (source_group_id);
CREATE INDEX user_results_text_prime_user_idx ON user_results_text_prime (user_id);
CREATE INDEX user_results_text_prime_formula_idx ON user_results_text_prime (formula_id);

--
-- indexes for table results_text_big
--
CREATE INDEX results_text_big_source_group_idx ON results_text_big (source_group_id);
CREATE INDEX results_text_big_formula_idx ON results_text_big (formula_id);
CREATE INDEX results_text_big_user_idx ON results_text_big (user_id);

--
-- indexes for table user_results_text_big
--
ALTER TABLE user_results_text_big ADD CONSTRAINT user_results_text_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_text_big_source_group_idx ON user_results_text_big (source_group_id);
CREATE INDEX user_results_text_big_user_idx ON user_results_text_big (user_id);
CREATE INDEX user_results_text_big_formula_idx ON user_results_text_big (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_time_standard_prime
--
CREATE UNIQUE INDEX results_time_standard_prime_pkey ON results_time_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_time_standard_prime_phrase_id_1_idx ON results_time_standard_prime (phrase_id_1);
CREATE INDEX results_time_standard_prime_phrase_id_2_idx ON results_time_standard_prime (phrase_id_2);
CREATE INDEX results_time_standard_prime_phrase_id_3_idx ON results_time_standard_prime (phrase_id_3);
CREATE INDEX results_time_standard_prime_phrase_id_4_idx ON results_time_standard_prime (phrase_id_4);

--
-- indexes for table results_time_standard
--

--
-- indexes for table results_time
--
CREATE INDEX results_time_source_group_idx ON results_time (source_group_id);
CREATE INDEX results_time_formula_idx ON results_time (formula_id);
CREATE INDEX results_time_user_idx ON results_time (user_id);

--
-- indexes for table user_results_time
--
ALTER TABLE user_results_time ADD CONSTRAINT user_results_time_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_time_source_group_idx ON user_results_time (source_group_id);
CREATE INDEX user_results_time_user_idx ON user_results_time (user_id);
CREATE INDEX user_results_time_formula_idx ON user_results_time (formula_id);

--
-- indexes for table results_time_prime
--
CREATE UNIQUE INDEX results_time_prime_pkey ON results_time_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_time_prime_phrase_id_1_idx ON results_time_prime (phrase_id_1);
CREATE INDEX results_time_prime_phrase_id_2_idx ON results_time_prime (phrase_id_2);
CREATE INDEX results_time_prime_phrase_id_3_idx ON results_time_prime (phrase_id_3);
CREATE INDEX results_time_prime_phrase_id_4_idx ON results_time_prime (phrase_id_4);
CREATE INDEX results_time_prime_source_group_idx ON results_time_prime (source_group_id);
CREATE INDEX results_time_prime_formula_idx ON results_time_prime (formula_id);
CREATE INDEX results_time_prime_user_idx ON results_time_prime (user_id);

--
-- indexes for table user_results_time_prime
--
CREATE UNIQUE INDEX user_results_time_prime_pkey ON user_results_time_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_results_time_prime_phrase_id_1_idx ON user_results_time_prime (phrase_id_1);
CREATE INDEX user_results_time_prime_phrase_id_2_idx ON user_results_time_prime (phrase_id_2);
CREATE INDEX user_results_time_prime_phrase_id_3_idx ON user_results_time_prime (phrase_id_3);
CREATE INDEX user_results_time_prime_phrase_id_4_idx ON user_results_time_prime (phrase_id_4);
CREATE INDEX user_results_time_prime_source_group_idx ON user_results_time_prime (source_group_id);
CREATE INDEX user_results_time_prime_user_idx ON user_results_time_prime (user_id);
CREATE INDEX user_results_time_prime_formula_idx ON user_results_time_prime (formula_id);

--
-- indexes for table results_time_big
--
CREATE INDEX results_time_big_source_group_idx ON results_time_big (source_group_id);
CREATE INDEX results_time_big_formula_idx ON results_time_big (formula_id);
CREATE INDEX results_time_big_user_idx ON results_time_big (user_id);

--
-- indexes for table user_results_time_big
--
ALTER TABLE user_results_time_big ADD CONSTRAINT user_results_time_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_time_big_source_group_idx ON user_results_time_big (source_group_id);
CREATE INDEX user_results_time_big_user_idx ON user_results_time_big (user_id);
CREATE INDEX user_results_time_big_formula_idx ON user_results_time_big (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_geo_standard_prime
--
CREATE UNIQUE INDEX results_geo_standard_prime_pkey ON results_geo_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_geo_standard_prime_phrase_id_1_idx ON results_geo_standard_prime (phrase_id_1);
CREATE INDEX results_geo_standard_prime_phrase_id_2_idx ON results_geo_standard_prime (phrase_id_2);
CREATE INDEX results_geo_standard_prime_phrase_id_3_idx ON results_geo_standard_prime (phrase_id_3);
CREATE INDEX results_geo_standard_prime_phrase_id_4_idx ON results_geo_standard_prime (phrase_id_4);

--
-- indexes for table results_geo_standard
--

--
-- indexes for table results_geo
--
CREATE INDEX results_geo_source_group_idx ON results_geo (source_group_id);
CREATE INDEX results_geo_formula_idx ON results_geo (formula_id);
CREATE INDEX results_geo_user_idx ON results_geo (user_id);

--
-- indexes for table user_results_geo
--
ALTER TABLE user_results_geo ADD CONSTRAINT user_results_geo_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_geo_source_group_idx ON user_results_geo (source_group_id);
CREATE INDEX user_results_geo_user_idx ON user_results_geo (user_id);
CREATE INDEX user_results_geo_formula_idx ON user_results_geo (formula_id);

--
-- indexes for table results_geo_prime
--
CREATE UNIQUE INDEX results_geo_prime_pkey ON results_geo_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_geo_prime_phrase_id_1_idx ON results_geo_prime (phrase_id_1);
CREATE INDEX results_geo_prime_phrase_id_2_idx ON results_geo_prime (phrase_id_2);
CREATE INDEX results_geo_prime_phrase_id_3_idx ON results_geo_prime (phrase_id_3);
CREATE INDEX results_geo_prime_phrase_id_4_idx ON results_geo_prime (phrase_id_4);
CREATE INDEX results_geo_prime_source_group_idx ON results_geo_prime (source_group_id);
CREATE INDEX results_geo_prime_formula_idx ON results_geo_prime (formula_id);
CREATE INDEX results_geo_prime_user_idx ON results_geo_prime (user_id);

--
-- indexes for table user_results_geo_prime
--
CREATE UNIQUE INDEX user_results_geo_prime_pkey ON user_results_geo_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_results_geo_prime_phrase_id_1_idx ON user_results_geo_prime (phrase_id_1);
CREATE INDEX user_results_geo_prime_phrase_id_2_idx ON user_results_geo_prime (phrase_id_2);
CREATE INDEX user_results_geo_prime_phrase_id_3_idx ON user_results_geo_prime (phrase_id_3);
CREATE INDEX user_results_geo_prime_phrase_id_4_idx ON user_results_geo_prime (phrase_id_4);
CREATE INDEX user_results_geo_prime_source_group_idx ON user_results_geo_prime (source_group_id);
CREATE INDEX user_results_geo_prime_user_idx ON user_results_geo_prime (user_id);
CREATE INDEX user_results_geo_prime_formula_idx ON user_results_geo_prime (formula_id);

--
-- indexes for table results_geo_big
--
CREATE INDEX results_geo_big_source_group_idx ON results_geo_big (source_group_id);
CREATE INDEX results_geo_big_formula_idx ON results_geo_big (formula_id);
CREATE INDEX results_geo_big_user_idx ON results_geo_big (user_id);

--
-- indexes for table user_results_geo_big
--
ALTER TABLE user_results_geo_big ADD CONSTRAINT user_results_geo_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_geo_big_source_group_idx ON user_results_geo_big (source_group_id);
CREATE INDEX user_results_geo_big_user_idx ON user_results_geo_big (user_id);
CREATE INDEX user_results_geo_big_formula_idx ON user_results_geo_big (formula_id);