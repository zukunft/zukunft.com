-- --------------------------------------------------------

--
-- indexes for table results_standard_prime
--

--
-- indexes for table results_standard
--

--
-- indexes for table results
--
ALTER TABLE results
    ADD KEY results_source_group_idx (source_group_id),
    ADD KEY results_formula_idx (formula_id),
    ADD KEY results_user_idx (user_id);

--
-- indexes for table user_results
--
ALTER TABLE user_results
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_source_group_idx (source_group_id),
    ADD KEY user_results_user_idx (user_id),
    ADD KEY user_results_formula_idx (formula_id);

--
-- indexes for table results_prime
--
ALTER TABLE results_prime
    ADD KEY results_prime_source_group_idx (source_group_id),
    ADD KEY results_prime_formula_idx (formula_id),
    ADD KEY results_prime_user_idx (user_id);

--
-- indexes for table user_results_prime
--
ALTER TABLE user_results_prime
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_prime_source_group_idx (source_group_id),
    ADD KEY user_results_prime_user_idx (user_id),
    ADD KEY user_results_prime_formula_idx (formula_id);

--
-- indexes for table results_big
--
ALTER TABLE results_big
    ADD KEY results_big_source_group_idx (source_group_id),
    ADD KEY results_big_formula_idx (formula_id),
    ADD KEY results_big_user_idx (user_id);

--
-- indexes for table user_results_big
--
ALTER TABLE user_results_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_big_source_group_idx (source_group_id),
    ADD KEY user_results_big_user_idx (user_id),
    ADD KEY user_results_big_formula_idx (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_text_standard_prime
--

--
-- indexes for table results_text_standard
--

--
-- indexes for table results_text
--
ALTER TABLE results_text
    ADD KEY results_text_source_group_idx (source_group_id),
    ADD KEY results_text_formula_idx (formula_id),
    ADD KEY results_text_user_idx (user_id);

--
-- indexes for table user_results_text
--
ALTER TABLE user_results_text
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_text_source_group_idx (source_group_id),
    ADD KEY user_results_text_user_idx (user_id),
    ADD KEY user_results_text_formula_idx (formula_id);

--
-- indexes for table results_text_prime
--
ALTER TABLE results_text_prime
    ADD KEY results_text_prime_source_group_idx (source_group_id),
    ADD KEY results_text_prime_formula_idx (formula_id),
    ADD KEY results_text_prime_user_idx (user_id);

--
-- indexes for table user_results_text_prime
--
ALTER TABLE user_results_text_prime
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_text_prime_source_group_idx (source_group_id),
    ADD KEY user_results_text_prime_user_idx (user_id),
    ADD KEY user_results_text_prime_formula_idx (formula_id);

--
-- indexes for table results_text_big
--
ALTER TABLE results_text_big
    ADD KEY results_text_big_source_group_idx (source_group_id),
    ADD KEY results_text_big_formula_idx (formula_id),
    ADD KEY results_text_big_user_idx (user_id);

--
-- indexes for table user_results_text_big
--
ALTER TABLE user_results_text_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_text_big_source_group_idx (source_group_id),
    ADD KEY user_results_text_big_user_idx (user_id),
    ADD KEY user_results_text_big_formula_idx (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_time_standard_prime
--

--
-- indexes for table results_time_standard
--

--
-- indexes for table results_time
--
ALTER TABLE results_time
    ADD KEY results_time_source_group_idx (source_group_id),
    ADD KEY results_time_formula_idx (formula_id),
    ADD KEY results_time_user_idx (user_id);

--
-- indexes for table user_results_time
--
ALTER TABLE user_results_time
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_time_source_group_idx (source_group_id),
    ADD KEY user_results_time_user_idx (user_id),
    ADD KEY user_results_time_formula_idx (formula_id);

--
-- indexes for table results_time_prime
--
ALTER TABLE results_time_prime
    ADD KEY results_time_prime_source_group_idx (source_group_id),
    ADD KEY results_time_prime_formula_idx (formula_id),
    ADD KEY results_time_prime_user_idx (user_id);

--
-- indexes for table user_results_time_prime
--
ALTER TABLE user_results_time_prime
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_time_prime_source_group_idx (source_group_id),
    ADD KEY user_results_time_prime_user_idx (user_id),
    ADD KEY user_results_time_prime_formula_idx (formula_id);

--
-- indexes for table results_time_big
--
ALTER TABLE results_time_big
    ADD KEY results_time_big_source_group_idx (source_group_id),
    ADD KEY results_time_big_formula_idx (formula_id),
    ADD KEY results_time_big_user_idx (user_id);

--
-- indexes for table user_results_time_big
--
ALTER TABLE user_results_time_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_time_big_source_group_idx (source_group_id),
    ADD KEY user_results_time_big_user_idx (user_id),
    ADD KEY user_results_time_big_formula_idx (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_geo_standard_prime
--

--
-- indexes for table results_geo_standard
--

--
-- indexes for table results_geo
--
ALTER TABLE results_geo
    ADD KEY results_geo_source_group_idx (source_group_id),
    ADD KEY results_geo_formula_idx (formula_id),
    ADD KEY results_geo_user_idx (user_id);

--
-- indexes for table user_results_geo
--
ALTER TABLE user_results_geo
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_geo_source_group_idx (source_group_id),
    ADD KEY user_results_geo_user_idx (user_id),
    ADD KEY user_results_geo_formula_idx (formula_id);

--
-- indexes for table results_geo_prime
--
ALTER TABLE results_geo_prime
    ADD KEY results_geo_prime_source_group_idx (source_group_id),
    ADD KEY results_geo_prime_formula_idx (formula_id),
    ADD KEY results_geo_prime_user_idx (user_id);

--
-- indexes for table user_results_geo_prime
--
ALTER TABLE user_results_geo_prime
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_geo_prime_source_group_idx (source_group_id),
    ADD KEY user_results_geo_prime_user_idx (user_id),
    ADD KEY user_results_geo_prime_formula_idx (formula_id);

--
-- indexes for table results_geo_big
--
ALTER TABLE results_geo_big
    ADD KEY results_geo_big_source_group_idx (source_group_id),
    ADD KEY results_geo_big_formula_idx (formula_id),
    ADD KEY results_geo_big_user_idx (user_id);

--
-- indexes for table user_results_geo_big
--
ALTER TABLE user_results_geo_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_geo_big_source_group_idx (source_group_id),
    ADD KEY user_results_geo_big_user_idx (user_id),
    ADD KEY user_results_geo_big_formula_idx (formula_id);