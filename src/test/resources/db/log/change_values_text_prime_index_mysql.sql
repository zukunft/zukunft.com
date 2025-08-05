-- --------------------------------------------------------

--
-- indexes for table change_values_text_prime
--

ALTER TABLE change_values_text_prime
    ADD KEY change_values_text_prime_change_idx (change_id),
    ADD KEY change_values_text_prime_change_time_idx (change_time),
    ADD KEY change_values_text_prime_user_idx (user_id);
