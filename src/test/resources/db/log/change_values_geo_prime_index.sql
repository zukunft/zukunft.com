-- --------------------------------------------------------

--
-- indexes for table change_values_geo_prime
--

CREATE INDEX change_values_geo_prime_change_idx ON change_values_geo_prime (change_id);
CREATE INDEX change_values_geo_prime_change_time_idx ON change_values_geo_prime (change_time);
CREATE INDEX change_values_geo_prime_user_idx ON change_values_geo_prime (user_id);
