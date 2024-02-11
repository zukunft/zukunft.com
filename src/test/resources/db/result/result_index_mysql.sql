-- --------------------------------------------------------

--
-- indexes for table results_standard_prime
--
ALTER TABLE results_standard_prime
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3),
    ADD KEY results_standard_prime_formula_idx (formula_id),
    ADD KEY results_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_standard_prime_phrase_id_3_idx (phrase_id_3);

--
-- indexes for table results_standard_main
--
ALTER TABLE results_standard_main
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7),
    ADD KEY results_standard_main_formula_idx (formula_id),
    ADD KEY results_standard_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_standard_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_standard_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_standard_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_standard_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_standard_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_standard_main_phrase_id_7_idx (phrase_id_7);

--
-- indexes for table results_standard
--
ALTER TABLE results_standard
    ADD PRIMARY KEY (group_id);

--
-- indexes for table results
--
ALTER TABLE results
    ADD PRIMARY KEY (group_id),
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
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY results_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_prime_source_group_idx (source_group_id),
    ADD KEY results_prime_formula_idx (formula_id),
    ADD KEY results_prime_user_idx (user_id);

--
-- indexes for table user_results_prime
--
ALTER TABLE user_results_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id),
    ADD KEY user_results_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_prime_source_group_idx (source_group_id),
    ADD KEY user_results_prime_user_idx (user_id),
    ADD KEY user_results_prime_formula_idx (formula_id);

--
-- indexes for table results_main
--
ALTER TABLE results_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8),
    ADD KEY results_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY results_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY results_main_source_group_idx (source_group_id),
    ADD KEY results_main_formula_idx (formula_id),
    ADD KEY results_main_user_idx (user_id);

--
-- indexes for table user_results_main
--
ALTER TABLE user_results_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id),
    ADD KEY user_results_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY user_results_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY user_results_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY user_results_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY user_results_main_source_group_idx (source_group_id),
    ADD KEY user_results_main_user_idx (user_id),
    ADD KEY user_results_main_formula_idx (formula_id);

--
-- indexes for table results_big
--
ALTER TABLE results_big
    ADD PRIMARY KEY (group_id),
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
ALTER TABLE results_text_standard_prime
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3),
    ADD KEY results_text_standard_prime_formula_idx (formula_id),
    ADD KEY results_text_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_text_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_text_standard_prime_phrase_id_3_idx (phrase_id_3);

--
-- indexes for table results_text_standard_main
--
ALTER TABLE results_text_standard_main
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7),
    ADD KEY results_text_standard_main_formula_idx (formula_id),
    ADD KEY results_text_standard_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_text_standard_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_text_standard_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_text_standard_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_text_standard_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_text_standard_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_text_standard_main_phrase_id_7_idx (phrase_id_7);

--
-- indexes for table results_text_standard
--
ALTER TABLE results_text_standard
    ADD PRIMARY KEY (group_id);

--
-- indexes for table results_text
--
ALTER TABLE results_text
    ADD PRIMARY KEY (group_id),
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
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY results_text_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_text_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_text_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_text_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_text_prime_source_group_idx (source_group_id),
    ADD KEY results_text_prime_formula_idx (formula_id),
    ADD KEY results_text_prime_user_idx (user_id);

--
-- indexes for table user_results_text_prime
--
ALTER TABLE user_results_text_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id),
    ADD KEY user_results_text_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_text_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_text_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_text_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_text_prime_source_group_idx (source_group_id),
    ADD KEY user_results_text_prime_user_idx (user_id),
    ADD KEY user_results_text_prime_formula_idx (formula_id);

--
-- indexes for table results_text_main
--
ALTER TABLE results_text_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8),
    ADD KEY results_text_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_text_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_text_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_text_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_text_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_text_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_text_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY results_text_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY results_text_main_source_group_idx (source_group_id),
    ADD KEY results_text_main_formula_idx (formula_id),
    ADD KEY results_text_main_user_idx (user_id);

--
-- indexes for table user_results_text_main
--
ALTER TABLE user_results_text_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id),
    ADD KEY user_results_text_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_text_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_text_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_text_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_text_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY user_results_text_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY user_results_text_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY user_results_text_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY user_results_text_main_source_group_idx (source_group_id),
    ADD KEY user_results_text_main_user_idx (user_id),
    ADD KEY user_results_text_main_formula_idx (formula_id);

--
-- indexes for table results_text_big
--
ALTER TABLE results_text_big
    ADD PRIMARY KEY (group_id),
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
ALTER TABLE results_time_standard_prime
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3),
    ADD KEY results_time_standard_prime_formula_idx (formula_id),
    ADD KEY results_time_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_time_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_time_standard_prime_phrase_id_3_idx (phrase_id_3);

--
-- indexes for table results_time_standard_main
--
ALTER TABLE results_time_standard_main
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7),
    ADD KEY results_time_standard_main_formula_idx (formula_id),
    ADD KEY results_time_standard_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_time_standard_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_time_standard_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_time_standard_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_time_standard_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_time_standard_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_time_standard_main_phrase_id_7_idx (phrase_id_7);

--
-- indexes for table results_time_standard
--
ALTER TABLE results_time_standard
    ADD PRIMARY KEY (group_id);

--
-- indexes for table results_time
--
ALTER TABLE results_time
    ADD PRIMARY KEY (group_id),
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
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY results_time_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_time_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_time_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_time_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_time_prime_source_group_idx (source_group_id),
    ADD KEY results_time_prime_formula_idx (formula_id),
    ADD KEY results_time_prime_user_idx (user_id);

--
-- indexes for table user_results_time_prime
--
ALTER TABLE user_results_time_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id),
    ADD KEY user_results_time_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_time_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_time_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_time_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_time_prime_source_group_idx (source_group_id),
    ADD KEY user_results_time_prime_user_idx (user_id),
    ADD KEY user_results_time_prime_formula_idx (formula_id);

--
-- indexes for table results_time_main
--
ALTER TABLE results_time_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8),
    ADD KEY results_time_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_time_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_time_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_time_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_time_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_time_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_time_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY results_time_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY results_time_main_source_group_idx (source_group_id),
    ADD KEY results_time_main_formula_idx (formula_id),
    ADD KEY results_time_main_user_idx (user_id);

--
-- indexes for table user_results_time_main
--
ALTER TABLE user_results_time_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id),
    ADD KEY user_results_time_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_time_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_time_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_time_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_time_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY user_results_time_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY user_results_time_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY user_results_time_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY user_results_time_main_source_group_idx (source_group_id),
    ADD KEY user_results_time_main_user_idx (user_id),
    ADD KEY user_results_time_main_formula_idx (formula_id);

--
-- indexes for table results_time_big
--
ALTER TABLE results_time_big
    ADD PRIMARY KEY (group_id),
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
ALTER TABLE results_geo_standard_prime
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3),
    ADD KEY results_geo_standard_prime_formula_idx (formula_id),
    ADD KEY results_geo_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_geo_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_geo_standard_prime_phrase_id_3_idx (phrase_id_3);

--
-- indexes for table results_geo_standard_main
--
ALTER TABLE results_geo_standard_main
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7),
    ADD KEY results_geo_standard_main_formula_idx (formula_id),
    ADD KEY results_geo_standard_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_geo_standard_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_geo_standard_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_geo_standard_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_geo_standard_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_geo_standard_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_geo_standard_main_phrase_id_7_idx (phrase_id_7);

--
-- indexes for table results_geo_standard
--
ALTER TABLE results_geo_standard
    ADD PRIMARY KEY (group_id);

--
-- indexes for table results_geo
--
ALTER TABLE results_geo
    ADD PRIMARY KEY (group_id),
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
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY results_geo_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_geo_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_geo_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_geo_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_geo_prime_source_group_idx (source_group_id),
    ADD KEY results_geo_prime_formula_idx (formula_id),
    ADD KEY results_geo_prime_user_idx (user_id);

--
-- indexes for table user_results_geo_prime
--
ALTER TABLE user_results_geo_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id),
    ADD KEY user_results_geo_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_geo_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_geo_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_geo_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_geo_prime_source_group_idx (source_group_id),
    ADD KEY user_results_geo_prime_user_idx (user_id),
    ADD KEY user_results_geo_prime_formula_idx (formula_id);

--
-- indexes for table results_geo_main
--
ALTER TABLE results_geo_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8),
    ADD KEY results_geo_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_geo_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_geo_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_geo_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_geo_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_geo_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_geo_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY results_geo_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY results_geo_main_source_group_idx (source_group_id),
    ADD KEY results_geo_main_formula_idx (formula_id),
    ADD KEY results_geo_main_user_idx (user_id);

--
-- indexes for table user_results_geo_main
--
ALTER TABLE user_results_geo_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id),
    ADD KEY user_results_geo_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_geo_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_geo_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_geo_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_geo_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY user_results_geo_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY user_results_geo_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY user_results_geo_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY user_results_geo_main_source_group_idx (source_group_id),
    ADD KEY user_results_geo_main_user_idx (user_id),
    ADD KEY user_results_geo_main_formula_idx (formula_id);

--
-- indexes for table results_geo_big
--
ALTER TABLE results_geo_big
    ADD PRIMARY KEY (group_id),
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