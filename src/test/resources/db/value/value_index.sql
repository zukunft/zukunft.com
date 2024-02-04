-- --------------------------------------------------------

--
-- indexes for table values_standard_prime
--
CREATE UNIQUE INDEX values_standard_prime_pkey ON values_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_standard_prime_phrase_id_1_idx ON values_standard_prime (phrase_id_1);
CREATE INDEX values_standard_prime_phrase_id_2_idx ON values_standard_prime (phrase_id_2);
CREATE INDEX values_standard_prime_phrase_id_3_idx ON values_standard_prime (phrase_id_3);
CREATE INDEX values_standard_prime_phrase_id_4_idx ON values_standard_prime (phrase_id_4);
CREATE INDEX values_standard_prime_source_idx ON values_standard_prime (source_id);

--
-- indexes for table values_standard
--
CREATE INDEX values_standard_source_idx ON values_standard (source_id);

--
-- indexes for table values
--
CREATE INDEX values_source_idx ON values (source_id);
CREATE INDEX values_user_idx ON values (user_id);

--
-- indexes for table user_values
--
ALTER TABLE user_values ADD CONSTRAINT user_values_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_user_idx ON user_values (user_id);
CREATE INDEX user_values_source_idx ON user_values (source_id);

--
-- indexes for table values_prime
--
CREATE UNIQUE INDEX values_prime_pkey ON values_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_prime_phrase_id_1_idx ON values_prime (phrase_id_1);
CREATE INDEX values_prime_phrase_id_2_idx ON values_prime (phrase_id_2);
CREATE INDEX values_prime_phrase_id_3_idx ON values_prime (phrase_id_3);
CREATE INDEX values_prime_phrase_id_4_idx ON values_prime (phrase_id_4);
CREATE INDEX values_prime_source_idx ON values_prime (source_id);
CREATE INDEX values_prime_user_idx ON values_prime (user_id);

--
-- indexes for table user_values_prime
--
CREATE UNIQUE INDEX user_values_prime_pkey ON user_values_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_values_prime_phrase_id_1_idx ON user_values_prime (phrase_id_1);
CREATE INDEX user_values_prime_phrase_id_2_idx ON user_values_prime (phrase_id_2);
CREATE INDEX user_values_prime_phrase_id_3_idx ON user_values_prime (phrase_id_3);
CREATE INDEX user_values_prime_phrase_id_4_idx ON user_values_prime (phrase_id_4);
CREATE INDEX user_values_prime_user_idx ON user_values_prime (user_id);
CREATE INDEX user_values_prime_source_idx ON user_values_prime (source_id);

--
-- indexes for table values_big
--
CREATE INDEX values_big_source_idx ON values_big (source_id);
CREATE INDEX values_big_user_idx ON values_big (user_id);

--
-- indexes for table user_values_big
--
ALTER TABLE user_values_big ADD CONSTRAINT user_values_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_big_user_idx ON user_values_big (user_id);
CREATE INDEX user_values_big_source_idx ON user_values_big (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_text_standard_prime
--
CREATE UNIQUE INDEX values_text_standard_prime_pkey ON values_text_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_text_standard_prime_phrase_id_1_idx ON values_text_standard_prime (phrase_id_1);
CREATE INDEX values_text_standard_prime_phrase_id_2_idx ON values_text_standard_prime (phrase_id_2);
CREATE INDEX values_text_standard_prime_phrase_id_3_idx ON values_text_standard_prime (phrase_id_3);
CREATE INDEX values_text_standard_prime_phrase_id_4_idx ON values_text_standard_prime (phrase_id_4);
CREATE INDEX values_text_standard_prime_source_idx ON values_text_standard_prime (source_id);

--
-- indexes for table values_text_standard
--
CREATE INDEX values_text_standard_source_idx ON values_text_standard (source_id);

--
-- indexes for table values_text
--
CREATE INDEX values_text_source_idx ON values_text (source_id);
CREATE INDEX values_text_user_idx ON values_text (user_id);

--
-- indexes for table user_values_text
--
ALTER TABLE user_values_text ADD CONSTRAINT user_values_text_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_text_user_idx ON user_values_text (user_id);
CREATE INDEX user_values_text_source_idx ON user_values_text (source_id);

--
-- indexes for table values_text_prime
--
CREATE UNIQUE INDEX values_text_prime_pkey ON values_text_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_text_prime_phrase_id_1_idx ON values_text_prime (phrase_id_1);
CREATE INDEX values_text_prime_phrase_id_2_idx ON values_text_prime (phrase_id_2);
CREATE INDEX values_text_prime_phrase_id_3_idx ON values_text_prime (phrase_id_3);
CREATE INDEX values_text_prime_phrase_id_4_idx ON values_text_prime (phrase_id_4);
CREATE INDEX values_text_prime_source_idx ON values_text_prime (source_id);
CREATE INDEX values_text_prime_user_idx ON values_text_prime (user_id);

--
-- indexes for table user_values_text_prime
--
CREATE UNIQUE INDEX user_values_text_prime_pkey ON user_values_text_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_values_text_prime_phrase_id_1_idx ON user_values_text_prime (phrase_id_1);
CREATE INDEX user_values_text_prime_phrase_id_2_idx ON user_values_text_prime (phrase_id_2);
CREATE INDEX user_values_text_prime_phrase_id_3_idx ON user_values_text_prime (phrase_id_3);
CREATE INDEX user_values_text_prime_phrase_id_4_idx ON user_values_text_prime (phrase_id_4);
CREATE INDEX user_values_text_prime_user_idx ON user_values_text_prime (user_id);
CREATE INDEX user_values_text_prime_source_idx ON user_values_text_prime (source_id);

--
-- indexes for table values_text_big
--
CREATE INDEX values_text_big_source_idx ON values_text_big (source_id);
CREATE INDEX values_text_big_user_idx ON values_text_big (user_id);

--
-- indexes for table user_values_text_big
--
ALTER TABLE user_values_text_big ADD CONSTRAINT user_values_text_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_text_big_user_idx ON user_values_text_big (user_id);
CREATE INDEX user_values_text_big_source_idx ON user_values_text_big (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_time_standard_prime
--
CREATE UNIQUE INDEX values_time_standard_prime_pkey ON values_time_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_time_standard_prime_phrase_id_1_idx ON values_time_standard_prime (phrase_id_1);
CREATE INDEX values_time_standard_prime_phrase_id_2_idx ON values_time_standard_prime (phrase_id_2);
CREATE INDEX values_time_standard_prime_phrase_id_3_idx ON values_time_standard_prime (phrase_id_3);
CREATE INDEX values_time_standard_prime_phrase_id_4_idx ON values_time_standard_prime (phrase_id_4);
CREATE INDEX values_time_standard_prime_source_idx ON values_time_standard_prime (source_id);

--
-- indexes for table values_time_standard
--
CREATE INDEX values_time_standard_source_idx ON values_time_standard (source_id);

--
-- indexes for table values_time
--
CREATE INDEX values_time_source_idx ON values_time (source_id);
CREATE INDEX values_time_user_idx ON values_time (user_id);

--
-- indexes for table user_values_time
--
ALTER TABLE user_values_time ADD CONSTRAINT user_values_time_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_time_user_idx ON user_values_time (user_id);
CREATE INDEX user_values_time_source_idx ON user_values_time (source_id);

--
-- indexes for table values_time_prime
--
CREATE UNIQUE INDEX values_time_prime_pkey ON values_time_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_time_prime_phrase_id_1_idx ON values_time_prime (phrase_id_1);
CREATE INDEX values_time_prime_phrase_id_2_idx ON values_time_prime (phrase_id_2);
CREATE INDEX values_time_prime_phrase_id_3_idx ON values_time_prime (phrase_id_3);
CREATE INDEX values_time_prime_phrase_id_4_idx ON values_time_prime (phrase_id_4);
CREATE INDEX values_time_prime_source_idx ON values_time_prime (source_id);
CREATE INDEX values_time_prime_user_idx ON values_time_prime (user_id);

--
-- indexes for table user_values_time_prime
--
CREATE UNIQUE INDEX user_values_time_prime_pkey ON user_values_time_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_values_time_prime_phrase_id_1_idx ON user_values_time_prime (phrase_id_1);
CREATE INDEX user_values_time_prime_phrase_id_2_idx ON user_values_time_prime (phrase_id_2);
CREATE INDEX user_values_time_prime_phrase_id_3_idx ON user_values_time_prime (phrase_id_3);
CREATE INDEX user_values_time_prime_phrase_id_4_idx ON user_values_time_prime (phrase_id_4);
CREATE INDEX user_values_time_prime_user_idx ON user_values_time_prime (user_id);
CREATE INDEX user_values_time_prime_source_idx ON user_values_time_prime (source_id);

--
-- indexes for table values_time_big
--
CREATE INDEX values_time_big_source_idx ON values_time_big (source_id);
CREATE INDEX values_time_big_user_idx ON values_time_big (user_id);

--
-- indexes for table user_values_time_big
--
ALTER TABLE user_values_time_big ADD CONSTRAINT user_values_time_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_time_big_user_idx ON user_values_time_big (user_id);
CREATE INDEX user_values_time_big_source_idx ON user_values_time_big (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_geo_standard_prime
--
CREATE UNIQUE INDEX values_geo_standard_prime_pkey ON values_geo_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_geo_standard_prime_phrase_id_1_idx ON values_geo_standard_prime (phrase_id_1);
CREATE INDEX values_geo_standard_prime_phrase_id_2_idx ON values_geo_standard_prime (phrase_id_2);
CREATE INDEX values_geo_standard_prime_phrase_id_3_idx ON values_geo_standard_prime (phrase_id_3);
CREATE INDEX values_geo_standard_prime_phrase_id_4_idx ON values_geo_standard_prime (phrase_id_4);
CREATE INDEX values_geo_standard_prime_source_idx ON values_geo_standard_prime (source_id);

--
-- indexes for table values_geo_standard
--
CREATE INDEX values_geo_standard_source_idx ON values_geo_standard (source_id);

--
-- indexes for table values_geo
--
CREATE INDEX values_geo_source_idx ON values_geo (source_id);
CREATE INDEX values_geo_user_idx ON values_geo (user_id);

--
-- indexes for table user_values_geo
--
ALTER TABLE user_values_geo ADD CONSTRAINT user_values_geo_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_geo_user_idx ON user_values_geo (user_id);
CREATE INDEX user_values_geo_source_idx ON user_values_geo (source_id);

--
-- indexes for table values_geo_prime
--
CREATE UNIQUE INDEX values_geo_prime_pkey ON values_geo_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_geo_prime_phrase_id_1_idx ON values_geo_prime (phrase_id_1);
CREATE INDEX values_geo_prime_phrase_id_2_idx ON values_geo_prime (phrase_id_2);
CREATE INDEX values_geo_prime_phrase_id_3_idx ON values_geo_prime (phrase_id_3);
CREATE INDEX values_geo_prime_phrase_id_4_idx ON values_geo_prime (phrase_id_4);
CREATE INDEX values_geo_prime_source_idx ON values_geo_prime (source_id);
CREATE INDEX values_geo_prime_user_idx ON values_geo_prime (user_id);

--
-- indexes for table user_values_geo_prime
--
CREATE UNIQUE INDEX user_values_geo_prime_pkey ON user_values_geo_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_values_geo_prime_phrase_id_1_idx ON user_values_geo_prime (phrase_id_1);
CREATE INDEX user_values_geo_prime_phrase_id_2_idx ON user_values_geo_prime (phrase_id_2);
CREATE INDEX user_values_geo_prime_phrase_id_3_idx ON user_values_geo_prime (phrase_id_3);
CREATE INDEX user_values_geo_prime_phrase_id_4_idx ON user_values_geo_prime (phrase_id_4);
CREATE INDEX user_values_geo_prime_user_idx ON user_values_geo_prime (user_id);
CREATE INDEX user_values_geo_prime_source_idx ON user_values_geo_prime (source_id);

--
-- indexes for table values_geo_big
--
CREATE INDEX values_geo_big_source_idx ON values_geo_big (source_id);
CREATE INDEX values_geo_big_user_idx ON values_geo_big (user_id);

--
-- indexes for table user_values_geo_big
--
ALTER TABLE user_values_geo_big ADD CONSTRAINT user_values_geo_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_geo_big_user_idx ON user_values_geo_big (user_id);
CREATE INDEX user_values_geo_big_source_idx ON user_values_geo_big (source_id);
