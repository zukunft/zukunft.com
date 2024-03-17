-- --------------------------------------------------------

--
-- indexes for table values_standard_prime
--
ALTER TABLE values_standard_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_standard_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_standard_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_standard_prime_source_idx (source_id);

--
-- indexes for table values_standard
--
ALTER TABLE values_standard
    ADD PRIMARY KEY (group_id),
    ADD KEY values_standard_source_idx (source_id);

--
-- indexes for table values
--
ALTER TABLE `values`
    ADD PRIMARY KEY (group_id),
    ADD KEY values_source_idx (source_id),
    ADD KEY values_user_idx (user_id);

--
-- indexes for table user_values
--
ALTER TABLE user_values
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_user_idx (user_id),
    ADD KEY user_values_source_idx (source_id);

--
-- indexes for table values_prime
--
ALTER TABLE values_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_prime_source_idx (source_id),
    ADD KEY values_prime_user_idx (user_id);

--
-- indexes for table user_values_prime
--
ALTER TABLE user_values_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id),
    ADD KEY user_values_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_values_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_values_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_values_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_values_prime_user_idx (user_id),
    ADD KEY user_values_prime_source_idx (source_id);

--
-- indexes for table values_big
--
ALTER TABLE values_big
    ADD PRIMARY KEY (group_id),
    ADD KEY values_big_source_idx (source_id),
    ADD KEY values_big_user_idx (user_id);

--
-- indexes for table user_values_big
--
ALTER TABLE user_values_big
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_big_user_idx (user_id),
    ADD KEY user_values_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_text_standard_prime
--
ALTER TABLE values_text_standard_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_text_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_text_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_text_standard_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_text_standard_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_text_standard_prime_source_idx (source_id);

--
-- indexes for table values_text_standard
--
ALTER TABLE values_text_standard
    ADD PRIMARY KEY (group_id),
    ADD KEY values_text_standard_source_idx (source_id);
--
-- indexes for table values_text
--
ALTER TABLE values_text
    ADD PRIMARY KEY (group_id),
    ADD KEY values_text_source_idx (source_id),
    ADD KEY values_text_user_idx (user_id);

--
-- indexes for table user_values_text
--
ALTER TABLE user_values_text
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_text_user_idx (user_id),
    ADD KEY user_values_text_source_idx (source_id);

--
-- indexes for table values_text_prime
--
ALTER TABLE values_text_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_text_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_text_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_text_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_text_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_text_prime_source_idx (source_id),
    ADD KEY values_text_prime_user_idx (user_id);

--
-- indexes for table user_values_text_prime
--
ALTER TABLE user_values_text_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id),
    ADD KEY user_values_text_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_values_text_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_values_text_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_values_text_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_values_text_prime_user_idx (user_id),
    ADD KEY user_values_text_prime_source_idx (source_id);

--
-- indexes for table values_text_big
--
ALTER TABLE values_text_big
    ADD PRIMARY KEY (group_id),
    ADD KEY values_text_big_source_idx (source_id),
    ADD KEY values_text_big_user_idx (user_id);

--
-- indexes for table user_values_text_big
--
ALTER TABLE user_values_text_big
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_text_big_user_idx (user_id),
    ADD KEY user_values_text_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_time_standard_prime
--
ALTER TABLE values_time_standard_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_time_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_time_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_time_standard_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_time_standard_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_time_standard_prime_source_idx (source_id);

--
-- indexes for table values_time_standard
--
ALTER TABLE values_time_standard
    ADD PRIMARY KEY (group_id),
    ADD KEY values_time_standard_source_idx (source_id);

--
-- indexes for table values_time
--
ALTER TABLE values_time
    ADD PRIMARY KEY (group_id),
    ADD KEY values_time_source_idx (source_id),
    ADD KEY values_time_user_idx (user_id);

--
-- indexes for table user_values_time
--
ALTER TABLE user_values_time
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_time_user_idx (user_id),
    ADD KEY user_values_time_source_idx (source_id);

--
-- indexes for table values_time_prime
--
ALTER TABLE values_time_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_time_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_time_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_time_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_time_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_time_prime_source_idx (source_id),
    ADD KEY values_time_prime_user_idx (user_id);

--
-- indexes for table user_values_time_prime
--
ALTER TABLE user_values_time_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id),
    ADD KEY user_values_time_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_values_time_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_values_time_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_values_time_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_values_time_prime_user_idx (user_id),
    ADD KEY user_values_time_prime_source_idx (source_id);

--
-- indexes for table values_time_big
--
ALTER TABLE values_time_big
    ADD PRIMARY KEY (group_id),
    ADD KEY values_time_big_source_idx (source_id),
    ADD KEY values_time_big_user_idx (user_id);

--
-- indexes for table user_values_time_big
--
ALTER TABLE user_values_time_big
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_time_big_user_idx (user_id),
    ADD KEY user_values_time_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_geo_standard_prime
--
ALTER TABLE values_geo_standard_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_geo_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_geo_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_geo_standard_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_geo_standard_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_geo_standard_prime_source_idx (source_id);

--
-- indexes for table values_geo_standard
--
ALTER TABLE values_geo_standard
    ADD PRIMARY KEY (group_id),
    ADD KEY values_geo_standard_source_idx (source_id);

--
-- indexes for table values_geo
--
ALTER TABLE values_geo
    ADD PRIMARY KEY (group_id),
    ADD KEY values_geo_source_idx (source_id),
    ADD KEY values_geo_user_idx (user_id);

--
-- indexes for table user_values_geo
--
ALTER TABLE user_values_geo
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_geo_user_idx (user_id),
    ADD KEY user_values_geo_source_idx (source_id);

--
-- indexes for table values_geo_prime
--
ALTER TABLE values_geo_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_geo_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_geo_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_geo_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_geo_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_geo_prime_source_idx (source_id),
    ADD KEY values_geo_prime_user_idx (user_id);

--
-- indexes for table user_values_geo_prime
--
ALTER TABLE user_values_geo_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id),
    ADD KEY user_values_geo_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_values_geo_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_values_geo_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_values_geo_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_values_geo_prime_user_idx (user_id),
    ADD KEY user_values_geo_prime_source_idx (source_id);

--
-- indexes for table values_geo_big
--
ALTER TABLE values_geo_big
    ADD PRIMARY KEY (group_id),
    ADD KEY values_geo_big_source_idx (source_id),
    ADD KEY values_geo_big_user_idx (user_id);

--
-- indexes for table user_values_geo_big
--
ALTER TABLE user_values_geo_big
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_geo_big_user_idx (user_id),
    ADD KEY user_values_geo_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_time_series
--
ALTER TABLE values_time_series
    ADD PRIMARY KEY (group_id),
    ADD KEY values_time_series_value_time_series_idx (value_time_series_id),
    ADD KEY values_time_series_source_idx (source_id),
    ADD KEY values_time_series_user_idx (user_id);

--
-- indexes for table user_values_time_series
--
ALTER TABLE user_values_time_series
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_time_series_user_idx (user_id),
    ADD KEY user_values_time_series_value_time_series_idx (value_time_series_id),
    ADD KEY user_values_time_series_source_idx (source_id);

--
-- indexes for table values_time_series_prime
--
ALTER TABLE values_time_series_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_time_series_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_time_series_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_time_series_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_time_series_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_time_series_prime_value_time_series_idx (value_time_series_id),
    ADD KEY values_time_series_prime_source_idx (source_id),
    ADD KEY values_time_series_prime_user_idx (user_id);

--
-- indexes for table user_values_time_series_prime
--
ALTER TABLE user_values_time_series_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id),
    ADD KEY user_values_time_series_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_values_time_series_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_values_time_series_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_values_time_series_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_values_time_series_prime_user_idx (user_id),
    ADD KEY user_values_time_series_prime_value_time_series_idx (value_time_series_id),
    ADD KEY user_values_time_series_prime_source_idx (source_id);

--
-- indexes for table values_time_series_big
--
ALTER TABLE values_time_series_big
    ADD PRIMARY KEY (group_id),
    ADD KEY values_time_series_big_value_time_series_idx (value_time_series_id),
    ADD KEY values_time_series_big_source_idx (source_id),
    ADD KEY values_time_series_big_user_idx (user_id);

--
-- indexes for table user_values_time_series_big
--
ALTER TABLE user_values_time_series_big
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_time_series_big_user_idx (user_id),
    ADD KEY user_values_time_series_big_value_time_series_idx (value_time_series_id),
    ADD KEY user_values_time_series_big_source_idx (source_id);
