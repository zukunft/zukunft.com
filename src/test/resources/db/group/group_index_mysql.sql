-- --------------------------------------------------------

--
-- indexes for table groups
--
ALTER TABLE `groups`
    ADD KEY groups_user_idx (user_id);

--
-- indexes for table user_groups
--
ALTER TABLE user_groups
    ADD KEY user_groups_user_idx (user_id);

--
-- indexes for table groups_prime
--
ALTER TABLE groups_prime
    ADD KEY groups_prime_user_idx (user_id);

--
-- indexes for table user_groups_prime
--
ALTER TABLE user_groups_prime
    ADD KEY user_groups_prime_user_idx (user_id);

--
-- indexes for table groups_big
--
ALTER TABLE groups_big
    ADD KEY groups_big_user_idx (user_id);

--
-- indexes for table user_groups_big
--
ALTER TABLE user_groups_big
    ADD KEY user_groups_big_user_idx (user_id);
