-- --------------------------------------------------------

--
-- indexes for table change_prime_values
--

CREATE INDEX change_prime_values_change_idx ON change_prime_values (change_id);
CREATE INDEX change_prime_values_change_time_idx ON change_prime_values (change_time);
CREATE INDEX change_prime_values_user_idx ON change_prime_values (user_id);
