-- --------------------------------------------------------

--
-- indexes for table results_standard_prime
--
ALTER TABLE results_standard_prime
    ADD KEY results_standard_prime_source_idx (source_id);

--
-- indexes for table results_standard
--
ALTER TABLE results_standard
    ADD KEY results_standard_source_idx (source_id);

--
-- indexes for table results
--
ALTER TABLE results
    ADD KEY results_source_idx (source_id),
    ADD KEY results_user_idx (user_id);

--
-- indexes for table user_results
--
ALTER TABLE user_results
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_user_idx (user_id),
    ADD KEY user_results_source_idx (source_id);

--
-- indexes for table results_prime
--
ALTER TABLE results_prime
    ADD KEY results_prime_source_idx (source_id),
    ADD KEY results_prime_user_idx (user_id);

--
-- indexes for table user_results_prime
--
ALTER TABLE user_results_prime
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_prime_user_idx (user_id),
    ADD KEY user_results_prime_source_idx (source_id);

--
-- indexes for table results_big
--
ALTER TABLE results_big
    ADD KEY results_big_source_idx (source_id),
    ADD KEY results_big_user_idx (user_id);

--
-- indexes for table user_results_big
--
ALTER TABLE user_results_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_big_user_idx (user_id),
    ADD KEY user_results_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table results_text_standard_prime
--
ALTER TABLE results_text_standard_prime
    ADD KEY results_text_standard_prime_source_idx (source_id);

--
-- indexes for table results_text_standard
--
ALTER TABLE results_text_standard
    ADD KEY results_text_standard_source_idx (source_id);
--
-- indexes for table results_text
--
ALTER TABLE results_text
    ADD KEY results_text_source_idx (source_id),
    ADD KEY results_text_user_idx (user_id);

--
-- indexes for table user_results_text
--
ALTER TABLE user_results_text
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_text_user_idx (user_id),
    ADD KEY user_results_text_source_idx (source_id);

--
-- indexes for table results_text_prime
--
ALTER TABLE results_text_prime
    ADD KEY results_text_prime_source_idx (source_id),
    ADD KEY results_text_prime_user_idx (user_id);

--
-- indexes for table user_results_text_prime
--
ALTER TABLE user_results_text_prime
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_text_prime_user_idx (user_id),
    ADD KEY user_results_text_prime_source_idx (source_id);

--
-- indexes for table results_text_big
--
ALTER TABLE results_text_big
    ADD KEY results_text_big_source_idx (source_id),
    ADD KEY results_text_big_user_idx (user_id);

--
-- indexes for table user_results_text_big
--
ALTER TABLE user_results_text_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_text_big_user_idx (user_id),
    ADD KEY user_results_text_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table results_time_standard_prime
--
ALTER TABLE results_time_standard_prime
    ADD KEY results_time_standard_prime_source_idx (source_id);

--
-- indexes for table results_time_standard
--
ALTER TABLE results_time_standard
    ADD KEY results_time_standard_source_idx (source_id);

--
-- indexes for table results_time
--
ALTER TABLE results_time
    ADD KEY results_time_source_idx (source_id),
    ADD KEY results_time_user_idx (user_id);

--
-- indexes for table user_results_time
--
ALTER TABLE user_results_time
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_time_user_idx (user_id),
    ADD KEY user_results_time_source_idx (source_id);

--
-- indexes for table results_time_prime
--
ALTER TABLE results_time_prime
    ADD KEY results_time_prime_source_idx (source_id),
    ADD KEY results_time_prime_user_idx (user_id);

--
-- indexes for table user_results_time_prime
--
ALTER TABLE user_results_time_prime
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_time_prime_user_idx (user_id),
    ADD KEY user_results_time_prime_source_idx (source_id);

--
-- indexes for table results_time_big
--
ALTER TABLE results_time_big
    ADD KEY results_time_big_source_idx (source_id),
    ADD KEY results_time_big_user_idx (user_id);

--
-- indexes for table user_results_time_big
--
ALTER TABLE user_results_time_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_time_big_user_idx (user_id),
    ADD KEY user_results_time_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table results_geo_standard_prime
--
ALTER TABLE results_geo_standard_prime
    ADD KEY results_geo_standard_prime_source_idx (source_id);

--
-- indexes for table results_geo_standard
--
ALTER TABLE results_geo_standard
    ADD KEY results_geo_standard_source_idx (source_id);

--
-- indexes for table results_geo
--
ALTER TABLE results_geo
    ADD KEY results_geo_source_idx (source_id),
    ADD KEY results_geo_user_idx (user_id);

--
-- indexes for table user_results_geo
--
ALTER TABLE user_results_geo
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_geo_user_idx (user_id),
    ADD KEY user_results_geo_source_idx (source_id);

--
-- indexes for table results_geo_prime
--
ALTER TABLE results_geo_prime
    ADD KEY results_geo_prime_source_idx (source_id),
    ADD KEY results_geo_prime_user_idx (user_id);

--
-- indexes for table user_results_geo_prime
--
ALTER TABLE user_results_geo_prime
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_geo_prime_user_idx (user_id),
    ADD KEY user_results_geo_prime_source_idx (source_id);

--
-- indexes for table results_geo_big
--
ALTER TABLE results_geo_big
    ADD KEY results_geo_big_source_idx (source_id),
    ADD KEY results_geo_big_user_idx (user_id);

--
-- indexes for table user_results_geo_big
--
ALTER TABLE user_results_geo_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_geo_big_user_idx (user_id),
    ADD KEY user_results_geo_big_source_idx (source_id);