-- --------------------------------------------------------

--
-- indexes for table change_prime_values
--

ALTER TABLE change_prime_values
    ADD PRIMARY KEY (change_id),
    ADD KEY change_prime_values_change_idx (change_id),
    ADD KEY change_prime_values_change_time_idx (change_time),
    ADD KEY change_prime_values_user_idx (user_id);
