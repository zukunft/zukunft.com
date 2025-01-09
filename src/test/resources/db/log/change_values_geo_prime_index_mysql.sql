-- --------------------------------------------------------

--
-- indexes for table change_values_geo_prime
--

ALTER TABLE change_values_geo_prime
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_geo_prime_change_idx (change_id),
    ADD KEY change_values_geo_prime_change_time_idx (change_time),
    ADD KEY change_values_geo_prime_user_idx (user_id);
