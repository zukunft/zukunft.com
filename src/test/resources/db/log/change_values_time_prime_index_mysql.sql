-- --------------------------------------------------------

--
-- indexes for table change_values_time_prime
--

ALTER TABLE change_values_time_prime
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_time_prime_change_idx (change_id),
    ADD KEY change_values_time_prime_change_time_idx (change_time),
    ADD KEY change_values_time_prime_user_idx (user_id);