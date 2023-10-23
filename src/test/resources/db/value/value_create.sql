
-- --------------------------------------------------------

--
-- table structure for public unprotected values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_standard_prime
(
    group_id      BIGSERIAL        PRIMARY KEY,
    numeric_value double precision NOT NULL,
    source_id     bigint           DEFAULT NULL
);

COMMENT ON TABLE values_standard_prime                IS 'for public unprotected values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_standard_prime.group_id      IS 'the 64-bit prime index to find the value';
COMMENT ON COLUMN values_standard_prime.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_standard_prime.source_id     IS 'the source of the value as given by the user';

--
-- table structure for public unprotected values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_standard
(
    group_id      char(112)        PRIMARY KEY,
    numeric_value double precision NOT NULL,
    source_id     bigint           DEFAULT NULL
);

COMMENT ON TABLE values_standard                IS 'for public unprotected values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_standard.group_id      IS 'the 512-bit prime index to find the value';
COMMENT ON COLUMN values_standard.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_standard.source_id     IS 'the source of the value as given by the user';

-- --------------------------------------------------------

--
-- table structure for values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values
(
    group_id        char(112)        PRIMARY KEY,
    numeric_value   double precision NOT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE values                IS 'for values related to up to 16 phrases';
COMMENT ON COLUMN values.group_id      IS 'the 512-bit prime index to find the value';
COMMENT ON COLUMN values.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values.protect_id    IS 'to protect against unwanted changes';

--
-- table structure for user specific changes of values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values
(
    group_id      char(112)        NOT NULL,
    user_id       bigint           NOT NULL,
    numeric_value double precision DEFAULT NULL,
    source_id     bigint           DEFAULT NULL,
    last_update   timestamp        DEFAULT NULL,
    excluded      smallint         DEFAULT NULL,
    share_type_id smallint         DEFAULT NULL,
    protect_id    smallint         DEFAULT NULL
);

COMMENT ON TABLE user_values                IS 'for user specific changes of values related to up to 16 phrases';
COMMENT ON COLUMN user_values.group_id      IS 'the 512-bit prime index to find the user values';
COMMENT ON COLUMN user_values.user_id       IS 'the changer of the value';
COMMENT ON COLUMN user_values.numeric_value IS 'the user specific numeric value change';
COMMENT ON COLUMN user_values.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the most often requested values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_prime
(
    group_id        BIGSERIAL        PRIMARY KEY,
    numeric_value   double precision NOT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE values_prime                IS 'for the most often requested values related up to four prime phrase';
COMMENT ON COLUMN values_prime.group_id      IS 'the 64-bit prime index to find the value';
COMMENT ON COLUMN values_prime.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_prime.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_prime.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_prime
(
    group_id        bigint           NOT NULL,
    user_id         bigint           NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_values_prime                IS 'to store the user specific changes for the most often requested values related up to four prime phrase';
COMMENT ON COLUMN user_values_prime.group_id      IS 'the 64-bit prime index to find the user values';
COMMENT ON COLUMN user_values_prime.user_id       IS 'the changer of the value';
COMMENT ON COLUMN user_values_prime.numeric_value IS 'the user specific numeric value change';
COMMENT ON COLUMN user_values_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_prime.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_big
(
    group_id        text             PRIMARY KEY,
    numeric_value   double precision NOT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE values_big                IS 'for values related to more than 16 phrases';
COMMENT ON COLUMN values_big.group_id      IS 'the text index to find value related to more than 16 phrases';
COMMENT ON COLUMN values_big.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_big.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_big.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_big
(
    group_id        text             NOT NULL,
    user_id         bigint           NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_values_big                IS 'to store the user specific changes of values related to more than 16 phrases';
COMMENT ON COLUMN user_values_big.group_id      IS 'the text index to find the user values related to more than 16 phrases';
COMMENT ON COLUMN user_values_big.user_id       IS 'the changer of the value';
COMMENT ON COLUMN user_values_big.numeric_value IS 'the user specific numeric value change';
COMMENT ON COLUMN user_values_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_big.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for public unprotected values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_text_standard_prime
(
    group_id   BIGSERIAL PRIMARY KEY,
    text_value text      NOT NULL,
    source_id  bigint    DEFAULT NULL
);

COMMENT ON TABLE values_text_standard_prime             IS 'for public unprotected values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_text_standard_prime.group_id   IS 'the 64-bit prime index to find the value';
COMMENT ON COLUMN values_text_standard_prime.text_value IS 'the text value given by the user';
COMMENT ON COLUMN values_text_standard_prime.source_id  IS 'the source of the value as given by the user';

--
-- table structure for public unprotected values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_text_standard
(
    group_id   char(112) PRIMARY KEY,
    text_value text      NOT NULL,
    source_id  bigint    DEFAULT NULL
);

COMMENT ON TABLE values_text_standard             IS 'for public unprotected values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_text_standard.group_id   IS 'the 512-bit prime index to find the value';
COMMENT ON COLUMN values_text_standard.text_value IS 'the text value given by the user';
COMMENT ON COLUMN values_text_standard.source_id  IS 'the source of the value as given by the user';

-- --------------------------------------------------------

--
-- table structure for values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_text
(
    group_id      char(112) PRIMARY KEY,
    text_value    text      NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_text                IS 'for values related to up to 16 phrases';
COMMENT ON COLUMN values_text.group_id      IS 'the 512-bit prime index to find the value';
COMMENT ON COLUMN values_text.text_value    IS 'the text value given by the user';
COMMENT ON COLUMN values_text.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_text.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_text.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_text.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_text.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_text.protect_id    IS 'to protect against unwanted changes';

--
-- table structure for user specific changes of values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_text
(
    group_id      char(112) NOT NULL,
    user_id       bigint    NOT NULL,
    text_value    text      DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_text                IS 'for user specific changes of values related to up to 16 phrases';
COMMENT ON COLUMN user_values_text.group_id      IS 'the 512-bit prime index to find the user values';
COMMENT ON COLUMN user_values_text.user_id       IS 'the changer of the value';
COMMENT ON COLUMN user_values_text.text_value    IS 'the user specific text value change';
COMMENT ON COLUMN user_values_text.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_text.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_text.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_text.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_text.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the most often requested values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_text_prime
(
    group_id      BIGSERIAL PRIMARY KEY,
    text_value    text      NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_text_prime                IS 'for the most often requested values related up to four prime phrase';
COMMENT ON COLUMN values_text_prime.group_id      IS 'the 64-bit prime index to find the value';
COMMENT ON COLUMN values_text_prime.text_value    IS 'the text value given by the user';
COMMENT ON COLUMN values_text_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_text_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_text_prime.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_text_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_text_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_text_prime.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_text_prime
(
    group_id      bigint    NOT NULL,
    user_id       bigint    NOT NULL,
    text_value    text      DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_text_prime                IS 'to store the user specific changes for the most often requested values related up to four prime phrase';
COMMENT ON COLUMN user_values_text_prime.group_id      IS 'the 64-bit prime index to find the user values';
COMMENT ON COLUMN user_values_text_prime.user_id       IS 'the changer of the value';
COMMENT ON COLUMN user_values_text_prime.text_value    IS 'the user specific text value change';
COMMENT ON COLUMN user_values_text_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_text_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_text_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_text_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_text_prime.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_text_big
(
    group_id      text      PRIMARY KEY,
    text_value    text      NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_text_big                IS 'for values related to more than 16 phrases';
COMMENT ON COLUMN values_text_big.group_id      IS 'the text index to find value related to more than 16 phrases';
COMMENT ON COLUMN values_text_big.text_value    IS 'the text value given by the user';
COMMENT ON COLUMN values_text_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_text_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_text_big.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_text_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_text_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_text_big.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_text_big
(
    group_id      text      NOT NULL,
    user_id       bigint    NOT NULL,
    text_value    text      DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_text_big                IS 'to store the user specific changes of values related to more than 16 phrases';
COMMENT ON COLUMN user_values_text_big.group_id      IS 'the text index to find the user values related to more than 16 phrases';
COMMENT ON COLUMN user_values_text_big.user_id       IS 'the changer of the value';
COMMENT ON COLUMN user_values_text_big.text_value    IS 'the user specific text value change';
COMMENT ON COLUMN user_values_text_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_text_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_text_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_text_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_text_big.protect_id    IS 'to protect against unwanted changes';
