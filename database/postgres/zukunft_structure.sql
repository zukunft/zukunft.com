--
-- Database: zukunft
--

-- --------------------------------------------------------

ALTER DATABASE zukunft SET search_path TO public;

--
-- Table structure for table calc_and_cleanup_tasks
--

CREATE TABLE IF NOT EXISTS calc_and_cleanup_tasks
(
    calc_and_cleanup_task_id      BIGSERIAL PRIMARY KEY,
    user_id                       bigint    NOT NULL,
    request_time                  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    start_time                    timestamp,
    end_time                      timestamp,
    calc_and_cleanup_task_type_id bigint    NOT NULL,
    row_id                        bigint    NOT NULL,
    change_field_id               bigint             DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table calc_and_cleanup_task_types
--

CREATE TABLE IF NOT EXISTS calc_and_cleanup_task_types
(
    calc_and_cleanup_task_type_id BIGSERIAL PRIMARY KEY,
    type_name                     varchar(200) NOT NULL,
    description                   text,
    code_id                       varchar(50)  NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table changes
--

CREATE TABLE IF NOT EXISTS changes
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id bigint    NOT NULL,
    change_field_id  bigint    NOT NULL,
    row_id           bigint             DEFAULT NULL,
    old_value        varchar(300)       DEFAULT NULL,
    new_value        varchar(300)       DEFAULT NULL,
    old_id           bigint             DEFAULT NULL,
    new_id           bigint             DEFAULT NULL
);

COMMENT ON TABLE changes is 'to log all changes';
COMMENT ON COLUMN changes.change_time is 'time when the value has been changed';
COMMENT ON COLUMN changes.old_id is 'old value id';
COMMENT ON COLUMN changes.new_id is 'new value id';

-- --------------------------------------------------------

--
-- Table structure for table change_actions
--

CREATE TABLE IF NOT EXISTS change_actions
(
    change_action_id   BIGSERIAL PRIMARY KEY,
    change_action_name varchar(200) NOT NULL,
    code_id            varchar(50)  NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table change_fields
--

CREATE TABLE IF NOT EXISTS change_fields
(
    change_field_id   BIGSERIAL PRIMARY KEY,
    change_field_name varchar(255) NOT NULL,
    table_id          bigint       NOT NULL,
    description       text,
    code_id           varchar(100) DEFAULT NULL
);

COMMENT ON COLUMN change_fields.table_id is 'because every field must only be unique within a table';
COMMENT ON COLUMN change_fields.code_id is 'to display the change with some linked information';


-- --------------------------------------------------------

--
-- Table structure for table change_links
--

CREATE TABLE IF NOT EXISTS change_links
(
    change_link_id   BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id bigint    NOT NULL,
    change_table_id  bigint    NOT NULL,
    old_from_id      bigint             DEFAULT NULL,
    old_link_id      bigint             DEFAULT NULL,
    old_to_id        bigint             DEFAULT NULL,
    old_text_from    text,
    old_text_link    text,
    old_text_to      text,
    new_from_id      bigint             DEFAULT NULL,
    new_link_id      bigint             DEFAULT NULL,
    new_to_id        bigint             DEFAULT NULL,
    new_text_from    text,
    new_text_link    text,
    new_text_to      text,
    row_id           bigint             DEFAULT NULL
);

COMMENT ON COLUMN change_links.new_to_id is 'either internal row id or the ref type id of the external system e.g. 2 for wikidata';
COMMENT ON COLUMN change_links.new_text_to is 'the fixed text to display to the user or the external reference id e.g. Q1 (for universe) in case of wikidata';


-- --------------------------------------------------------

--
-- Table structure for table change_tables
--

CREATE TABLE IF NOT EXISTS change_tables
(
    change_table_id   BIGSERIAL PRIMARY KEY,
    change_table_name varchar(100) NOT NULL,
    description       varchar(1000) DEFAULT NULL,
    code_id           varchar(50)   DEFAULT NULL
);

COMMENT ON TABLE change_tables is 'to avoid log changes in case a table is renamed';
COMMENT ON COLUMN change_tables.change_table_name is 'the real name';
COMMENT ON COLUMN change_tables.description is 'the user readable name';
COMMENT ON COLUMN change_tables.code_id is 'with this field tables can be combined in case of renaming';

-- --------------------------------------------------------

--
-- Table structure for table comments
--

CREATE TABLE IF NOT EXISTS comments
(
    comment_id BIGSERIAL PRIMARY KEY,
    table_id   bigint NOT NULL,
    row_id     bigint NOT NULL,
    comment    text   NOT NULL
);

COMMENT ON TABLE comments is 'separate table because it is expected that only a few record';

-- --------------------------------------------------------

--
-- Table structure for table config
--

CREATE TABLE IF NOT EXISTS config
(
    config_id   BIGSERIAL PRIMARY KEY,
    config_name varchar(100) DEFAULT NULL,
    code_id     varchar(100) NOT NULL,
    value       varchar(100) DEFAULT NULL,
    description text
);

-- --------------------------------------------------------

--
-- Table structure for table formulas
--

CREATE TABLE IF NOT EXISTS formulas
(
    formula_id         BIGSERIAL PRIMARY KEY,
    formula_name       varchar(100) NOT NULL,
    user_id            bigint                DEFAULT NULL,
    formula_text       text         NOT NULL,
    resolved_text      text         NOT NULL,
    description        text,
    formula_type_id    bigint                DEFAULT NULL,
    all_values_needed  smallint              DEFAULT NULL,
    last_update        timestamp    NULL     DEFAULT NULL,
    excluded           smallint              DEFAULT NULL,
    protection_type_id bigint       NOT NULL DEFAULT '1'
);

COMMENT ON COLUMN formulas.formula_name is 'short name of the formula';
COMMENT ON COLUMN formulas.formula_text is 'the coded formula; e.g. \\f1 for formula with ID1';
COMMENT ON COLUMN formulas.resolved_text is 'the formula in user readable format';
COMMENT ON COLUMN formulas.description is 'additional to comments because many formulas have this';
COMMENT ON COLUMN formulas.all_values_needed is 'calculate the result only if all values used in the formula are not null';
COMMENT ON COLUMN formulas.last_update is 'time of the last calculation relevant update';

-- --------------------------------------------------------

--
-- Table structure for table formula_elements
--

CREATE TABLE IF NOT EXISTS formula_elements
(
    formula_element_id      BIGSERIAL PRIMARY KEY,
    formula_id              bigint NOT NULL,
    user_id                 bigint NOT NULL,
    order_nbr               bigint NOT NULL,
    formula_element_type_id bigint NOT NULL,
    ref_id                  bigint       DEFAULT NULL,
    resolved_text           varchar(200) DEFAULT NULL
);

COMMENT ON TABLE formula_elements is 'cache for fast update of formula resolved text';
COMMENT ON COLUMN formula_elements.ref_id is 'either a term, verb or formula id';

-- --------------------------------------------------------

--
-- Table structure for table formula_links
--

CREATE TABLE IF NOT EXISTS formula_links
(
    formula_link_id BIGSERIAL PRIMARY KEY,
    user_id         bigint   DEFAULT NULL,
    formula_id      bigint NOT NULL,
    phrase_id       bigint NOT NULL,
    link_type_id    bigint NOT NULL,
    order_nbr       bigint NOT NULL,
    excluded        smallint DEFAULT NULL
);

COMMENT ON TABLE formula_links is 'if the term pattern of a value matches this term pattern';

-- --------------------------------------------------------

--
-- Table structure for table formula_link_types
--

CREATE TABLE IF NOT EXISTS formula_link_types
(
    formula_link_type_id BIGSERIAL PRIMARY KEY,
    type_name            varchar(200) NOT NULL,
    code_id              varchar(100)          DEFAULT NULL,
    formula_id           bigint       NOT NULL DEFAULT 1,
    word_type_id         bigint       NOT NULL,
    link_type_id         bigint       NOT NULL,
    description          text
);

-- --------------------------------------------------------

--
-- Table structure for table formula_types
--

CREATE TABLE IF NOT EXISTS formula_types
(
    formula_type_id BIGSERIAL PRIMARY KEY,
    name            varchar(100) NOT NULL,
    description     text         NOT NULL,
    code_id         varchar(255) NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table formula_values
--

CREATE TABLE IF NOT EXISTS formula_values
(
    formula_value_id       BIGSERIAL PRIMARY KEY,
    formula_id             bigint           NOT NULL,
    user_id                bigint                DEFAULT NULL,
    source_phrase_group_id bigint                DEFAULT NULL,
    source_time_word_id    bigint                DEFAULT NULL,
    phrase_group_id        bigint                DEFAULT 0,
    time_word_id           bigint                DEFAULT 0,
    formula_value          double precision NOT NULL,
    last_update            timestamp        NULL DEFAULT NULL,
    dirty                  smallint              DEFAULT NULL
);

COMMENT ON TABLE formula_values is 'temp table to cache the formula results';
COMMENT ON COLUMN formula_values.phrase_group_id is 'temp field for fast data collection; no single links to terms because this is just a cache table and can be recreated by the underlying tables';
COMMENT ON COLUMN formula_values.time_word_id is 'special field just to speed up queries';
COMMENT ON COLUMN formula_values.last_update is 'time of last value update mainly used for recovery in case of inconsistencies, empty in case this value is dirty';

-- --------------------------------------------------------

--
-- Table structure for table import_source
--

CREATE TABLE IF NOT EXISTS import_source
(
    import_source_id BIGSERIAL PRIMARY KEY,
    name             varchar(100) NOT NULL,
    import_type      bigint       NOT NULL,
    word_id          bigint       NOT NULL
);

COMMENT ON TABLE import_source is 'many replace by a term';
COMMENT ON COLUMN import_source.word_id is 'the name as a term';

-- --------------------------------------------------------

--
-- Table structure for table languages
--

CREATE TABLE IF NOT EXISTS languages
(
    language_id    BIGSERIAL PRIMARY KEY,
    language_name  varchar(200) NOT NULL,
    code_id        varchar(50)  NOT NULL,
    wikimedia_code varchar(50)  NOT NULL,
    description    text
);

-- --------------------------------------------------------

--
-- Table structure for table language_forms
--

CREATE TABLE IF NOT EXISTS language_forms
(
    languages_form_id   BIGSERIAL PRIMARY KEY,
    languages_form_name varchar(200) DEFAULT NULL,
    code_id             varchar(50)  DEFAULT NULL,
    language_id         bigint NOT NULL
);

COMMENT ON COLUMN language_forms.languages_form_name is 'type of adjustment of a term in a language e.g. plural';

-- --------------------------------------------------------

--
-- Table structure for table phrase_groups
--

CREATE TABLE IF NOT EXISTS phrase_groups
(
    phrase_group_id   BIGSERIAL PRIMARY KEY,
    phrase_group_name varchar(1000) DEFAULT NULL,
    auto_description  varchar(4000) DEFAULT NULL,
    word_ids          varchar(255)  DEFAULT NULL,
    triple_ids        varchar(255)  DEFAULT NULL,
    id_order          varchar(512)  DEFAULT NULL
);

COMMENT ON TABLE phrase_groups is 'to reduce the number of value to term links';
COMMENT ON COLUMN phrase_groups.phrase_group_name is 'if this is set a manual group for fast selection';
COMMENT ON COLUMN phrase_groups.auto_description is 'the automatic created user readable description';
COMMENT ON COLUMN phrase_groups.triple_ids is 'one field link to the table term_links';
COMMENT ON COLUMN phrase_groups.id_order is 'the phrase ids in the order that the user wants to see them';

-- --------------------------------------------------------

--
-- Table structure for table phrase_group_triple_links
--

CREATE TABLE IF NOT EXISTS phrase_group_triple_links
(
    phrase_group_triple_link_id BIGSERIAL PRIMARY KEY,
    phrase_group_id             bigint NOT NULL,
    triple_id                   bigint NOT NULL
);

COMMENT ON TABLE phrase_group_triple_links is 'view for fast group selection based on a triple';

-- --------------------------------------------------------

--
-- Table structure for table phrase_group_word_links
--

CREATE TABLE IF NOT EXISTS phrase_group_word_links
(
    phrase_group_word_link_id BIGSERIAL PRIMARY KEY,
    phrase_group_id           bigint NOT NULL,
    word_id                   bigint NOT NULL
);

COMMENT ON TABLE phrase_group_word_links is 'master to link words to a term_group';

-- --------------------------------------------------------

--
-- Table structure for table protection_types
--

CREATE TABLE IF NOT EXISTS protection_types
(
    protection_type_id BIGSERIAL PRIMARY KEY,
    type_name          varchar(200) NOT NULL,
    code_id            varchar(100) NOT NULL,
    description        text         NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table refs
--

CREATE TABLE IF NOT EXISTS refs
(
    ref_id       BIGSERIAL PRIMARY KEY,
    phrase_id    bigint       NOT NULL,
    external_key varchar(250) NOT NULL,
    ref_type_id  bigint       NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table ref_types
--

CREATE TABLE IF NOT EXISTS ref_types
(
    ref_type_id BIGSERIAL PRIMARY KEY,
    type_name   varchar(200) NOT NULL,
    code_id     varchar(100) NOT NULL,
    description text         NOT NULL,
    base_url    text         NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table sessions
--

CREATE TABLE IF NOT EXISTS sessions
(
    id          bigint       NOT NULL,
    uid         bigint       NOT NULL,
    hash        varchar(40)  NOT NULL,
    expire_date timestamp    NOT NULL,
    ip          varchar(39)  NOT NULL,
    agent       varchar(200) NOT NULL,
    cookie_crc  varchar(40)  NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table share_types
--

CREATE TABLE IF NOT EXISTS share_types
(
    share_type_id BIGSERIAL PRIMARY KEY,
    type_name     varchar(200) NOT NULL,
    code_id       varchar(100) NOT NULL,
    description   text
);

COMMENT ON COLUMN share_types.type_name is 'the name of the share type as displayed for the user';
COMMENT ON COLUMN share_types.code_id is 'the code link';
COMMENT ON COLUMN share_types.description is 'to explain the code action of the share type';

-- --------------------------------------------------------

--
-- Table structure for table sources
--

CREATE TABLE IF NOT EXISTS sources
(
    source_id      BIGSERIAL PRIMARY KEY,
    user_id        bigint       DEFAULT NULL,
    source_name    varchar(200) NOT NULL,
    url            text         DEFAULT NULL,
    comment        text,
    source_type_id bigint       DEFAULT NULL,
    code_id        varchar(100) DEFAULT NULL,
    excluded       smallint     DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table source_types
--

CREATE TABLE IF NOT EXISTS source_types
(
    source_type_id   BIGSERIAL PRIMARY KEY,
    source_type_name varchar(200) NOT NULL,
    code_id          varchar(100) NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table source_values
--

CREATE TABLE IF NOT EXISTS source_values
(
    value_id     BIGSERIAL PRIMARY KEY,
    source_id    bigint           NOT NULL,
    user_id      bigint           NOT NULL,
    source_value double precision NOT NULL
);

COMMENT ON TABLE source_values is 'one user can add different value, which should be the same, but are different  ';

-- --------------------------------------------------------

--
-- Table structure for table sys_log
--

CREATE TABLE IF NOT EXISTS sys_log
(
    sys_log_id          BIGSERIAL PRIMARY KEY,
    sys_log_time        timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sys_log_type_id     bigint    NOT NULL,
    sys_log_function_id bigint    NOT NULL,
    sys_log_text        text,
    sys_log_description text,
    sys_log_trace       text,
    user_id             bigint             DEFAULT NULL,
    solver_id           bigint             DEFAULT NULL,
    sys_log_status_id   bigint             DEFAULT '1'
);

COMMENT ON COLUMN sys_log.solver_id is 'user id of the user that is trying to solve the problem';

-- --------------------------------------------------------

--
-- Table structure for table sys_log_functions
--

CREATE TABLE IF NOT EXISTS sys_log_functions
(
    sys_log_function_id   BIGSERIAL PRIMARY KEY,
    sys_log_function_name varchar(200) NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table sys_log_status
--

CREATE TABLE IF NOT EXISTS sys_log_status
(
    sys_log_status_id BIGSERIAL PRIMARY KEY,
    type_name         varchar(200) NOT NULL,
    code_id           varchar(50)  NOT NULL,
    description       text         NOT NULL,
    action            varchar(200) DEFAULT NULL
);

COMMENT ON TABLE sys_log_status is 'Status of internal errors';
COMMENT ON COLUMN sys_log_status.action is 'description of the action to get to this status';

-- --------------------------------------------------------

--
-- Table structure for table sys_log_types
--

CREATE TABLE IF NOT EXISTS sys_log_types
(
    sys_log_type_id BIGSERIAL PRIMARY KEY,
    type_name       varchar(200) NOT NULL,
    code_id         varchar(50)  NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table sys_scripts
--

CREATE TABLE IF NOT EXISTS sys_scripts
(
    sys_script_id   BIGSERIAL PRIMARY KEY,
    sys_script_name varchar(200) NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table sys_script_times
--

CREATE TABLE IF NOT EXISTS sys_script_times
(
    sys_script_time  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sys_script_start timestamp,
    sys_script_id    bigint    NOT NULL,
    url              varchar(250)       DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table users
--

CREATE TABLE IF NOT EXISTS users
(
    user_id                  BIGSERIAL PRIMARY KEY,
    user_name                varchar(100) NOT NULL,
    code_id                  varchar(50)           DEFAULT NULL,
    right_level              smallint              DEFAULT NULL,
    password                 varchar(200)          DEFAULT NULL,
    email                    varchar(200)          DEFAULT NULL,
    email_verified           smallint              DEFAULT NULL,
    email_alternative        varchar(200)          DEFAULT NULL,
    ip_address               varchar(50)           DEFAULT NULL,
    mobile_number            varchar(50)           DEFAULT NULL,
    mobile_verified          smallint              DEFAULT NULL,
    first_name               varchar(200)          DEFAULT NULL,
    last_name                varchar(200)          DEFAULT NULL,
    street                   varchar(300)          DEFAULT NULL,
    place                    varchar(200)          DEFAULT NULL,
    country_id               bigint                DEFAULT NULL,
    post_verified            smallint              DEFAULT NULL,
    official_id              varchar(200)          DEFAULT NULL,
    user_official_id_type_id bigint                DEFAULT NULL,
    official_verified        bigint                DEFAULT NULL,
    user_type_id             bigint                DEFAULT NULL,
    last_word_id             bigint                DEFAULT NULL,
    last_mask_id             bigint                DEFAULT NULL,
    is_active                smallint     NOT NULL DEFAULT '0',
    dt                       timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_logoff              timestamp    NULL     DEFAULT NULL,
    user_profile_id          bigint                DEFAULT NULL,
    source_id                bigint                DEFAULT NULL,
    activation_key           varchar(200)          DEFAULT NULL,
    activation_key_timeout   timestamp    NULL     DEFAULT NULL
);

COMMENT ON TABLE users is 'only users can add data';
COMMENT ON COLUMN users.code_id is 'to select e.g. the system batch user';
COMMENT ON COLUMN users.official_id is 'such as the passport id';
COMMENT ON COLUMN users.last_word_id is 'the last term that the user had used';
COMMENT ON COLUMN users.last_mask_id is 'the last mask that the user has used';
COMMENT ON COLUMN users.source_id is 'the last source used by this user to have a default for the next value';

-- --------------------------------------------------------

--
-- Table structure for table user_attempts
--

CREATE TABLE IF NOT EXISTS user_attempts
(
    id          bigint      NOT NULL,
    ip          varchar(39) NOT NULL,
    expire_date timestamp   NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table user_blocked_ips
--

CREATE TABLE IF NOT EXISTS user_blocked_ips
(
    user_blocked_id BIGSERIAL PRIMARY KEY,
    ip_from         varchar(45) NOT NULL,
    ip_to           varchar(45) NOT NULL,
    reason          text        NOT NULL,
    is_active       smallint DEFAULT '1'
);

-- --------------------------------------------------------

--
-- Table structure for table user_formulas
--

CREATE TABLE IF NOT EXISTS user_formulas
(
    formula_id        BIGSERIAL PRIMARY KEY,
    user_id           bigint    NOT NULL,
    formula_name      varchar(200)   DEFAULT NULL,
    formula_text      text,
    resolved_text     text,
    description       text,
    formula_type_id   bigint         DEFAULT NULL,
    all_values_needed smallint       DEFAULT NULL,
    share_type_id     bigint         DEFAULT NULL,
    last_update       timestamp NULL DEFAULT NULL,
    excluded          smallint       DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table user_formula_links
--

CREATE TABLE IF NOT EXISTS user_formula_links
(
    formula_link_id BIGSERIAL PRIMARY KEY,
    user_id         bigint NOT NULL,
    link_type_id    bigint   DEFAULT NULL,
    excluded        smallint DEFAULT NULL
);

COMMENT ON TABLE user_formula_links is 'if the term pattern of a value matches this term pattern ';

-- --------------------------------------------------------

--
-- Table structure for table user_official_types
--

CREATE TABLE IF NOT EXISTS user_official_types
(
    user_official_type_id BIGSERIAL PRIMARY KEY,
    type_name             varchar(200) NOT NULL,
    code_id               varchar(100) DEFAULT NULL,
    comment               text         DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table user_phrase_groups
--

CREATE TABLE IF NOT EXISTS user_phrase_groups
(
    phrase_group_id   BIGSERIAL PRIMARY KEY,
    user_id           bigint NOT NULL,
    phrase_group_name varchar(1000) DEFAULT NULL,
    auto_description  varchar(4000) DEFAULT NULL,
    id_order          varchar(512)  DEFAULT NULL
);

COMMENT ON TABLE user_phrase_groups is 'to reduce the number of value to term links';
COMMENT ON COLUMN user_phrase_groups.phrase_group_name is 'if this is set a manual group for fast selection';
COMMENT ON COLUMN user_phrase_groups.auto_description is 'the automatic created user readable description';
COMMENT ON COLUMN user_phrase_groups.id_order is 'the phrase ids in the order that the user wants to see them';

-- --------------------------------------------------------

--
-- Table structure for table user_phrase_group_triple_links
--

CREATE TABLE IF NOT EXISTS user_phrase_group_triple_links
(
    phrase_group_triple_link_id BIGSERIAL PRIMARY KEY,
    user_id                     bigint   DEFAULT NULL,
    excluded                    smallint DEFAULT NULL
);

COMMENT ON TABLE user_phrase_group_triple_links is 'view for fast group selection based on a triple';

-- --------------------------------------------------------

--
-- Table structure for table user_phrase_group_word_links
--

CREATE TABLE IF NOT EXISTS user_phrase_group_word_links
(
    phrase_group_word_link_id BIGSERIAL PRIMARY KEY,
    user_id                   bigint   DEFAULT NULL,
    excluded                  smallint DEFAULT NULL
);

COMMENT ON TABLE user_phrase_group_word_links is 'view for fast group selection based on a triple';

-- --------------------------------------------------------

--
-- Table structure for table user_profiles
--

CREATE TABLE IF NOT EXISTS user_profiles
(
    profile_id  BIGSERIAL PRIMARY KEY,
    type_name   varchar(200) NOT NULL,
    code_id     varchar(50)  NOT NULL,
    description text
);

-- --------------------------------------------------------

--
-- Table structure for table user_requests
--

CREATE TABLE IF NOT EXISTS user_requests
(
    id          bigint      NOT NULL,
    uid         bigint      NOT NULL,
    request_key varchar(20) NOT NULL,
    expire      timestamp   NOT NULL,
    type        varchar(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table user_sources
--

CREATE TABLE IF NOT EXISTS user_sources
(
    source_id      bigint NOT NULL,
    user_id        bigint NOT NULL,
    source_name    varchar(200) DEFAULT NULL,
    url            text         DEFAULT NULL,
    comment        text,
    source_type_id bigint       DEFAULT NULL,
    excluded       smallint     DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table user_types
--

CREATE TABLE IF NOT EXISTS user_types
(
    user_type_id BIGSERIAL PRIMARY KEY,
    user_type    varchar(200) NOT NULL,
    code_id      varchar(100) DEFAULT NULL,
    comment      varchar(200) NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table user_values
--

CREATE TABLE IF NOT EXISTS user_values
(
    value_id           bigint    NOT NULL,
    user_id            bigint    NOT NULL,
    word_value         double precision DEFAULT NULL,
    source_id          bigint           DEFAULT NULL,
    excluded           smallint         DEFAULT NULL,
    share_type_id      bigint           DEFAULT NULL,
    protection_type_id bigint           DEFAULT NULL,
    last_update        timestamp NULL   DEFAULT NULL
);

COMMENT ON TABLE user_values is 'for quick access to the user specific values';
COMMENT ON COLUMN user_values.last_update is 'for fast calculation of the updates';

-- --------------------------------------------------------

--
-- Table structure for table user_views
--

CREATE TABLE IF NOT EXISTS user_views
(
    view_id      bigint NOT NULL,
    user_id      bigint NOT NULL,
    view_name    varchar(200) DEFAULT NULL,
    comment      text,
    view_type_id bigint       DEFAULT NULL,
    excluded     smallint     DEFAULT NULL
);

COMMENT ON TABLE user_views is 'user specific mask settings';

-- --------------------------------------------------------

--
-- Table structure for table user_view_components
--

CREATE TABLE IF NOT EXISTS user_view_components
(
    view_component_id      bigint NOT NULL,
    user_id                bigint NOT NULL,
    view_component_name    varchar(200) DEFAULT NULL,
    comment                text,
    view_component_type_id bigint       DEFAULT NULL,
    word_id_row            bigint       DEFAULT NULL,
    word_id_col            bigint       DEFAULT NULL,
    word_id_col2           bigint       DEFAULT NULL,
    formula_id             bigint       DEFAULT NULL,
    excluded               bigint       DEFAULT NULL,
    link_type_id           bigint       DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table user_view_component_links
--

CREATE TABLE IF NOT EXISTS user_view_component_links
(
    view_component_link_id bigint NOT NULL,
    user_id                bigint NOT NULL,
    order_nbr              bigint   DEFAULT NULL,
    position_type          bigint   DEFAULT NULL,
    excluded               smallint DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table user_words
--

CREATE TABLE IF NOT EXISTS user_words
(
    word_id            bigint   NOT NULL,
    user_id            bigint   NOT NULL,
    language_id        bigint   NOT NULL DEFAULT 1,
    word_name          varchar(200)      DEFAULT NULL,
    plural             varchar(200)      DEFAULT NULL,
    description        text,
    word_type_id       bigint            DEFAULT NULL,
    view_id            bigint            DEFAULT NULL,
    excluded           smallint          DEFAULT NULL,
    share_type_id      smallint          DEFAULT NULL,
    protection_type_id smallint NOT NULL DEFAULT '1'
);

-- --------------------------------------------------------

--
-- Table structure for table user_word_links
--

CREATE TABLE IF NOT EXISTS user_word_links
(
    word_link_id       BIGSERIAL PRIMARY KEY,
    user_id            bigint            DEFAULT NULL,
    description        text,
    word_link_name     varchar(200)      DEFAULT NULL,
    excluded           smallint          DEFAULT NULL,
    share_type_id      smallint          DEFAULT NULL,
    protection_type_id smallint NOT NULL DEFAULT '1'
);

COMMENT ON COLUMN user_word_links.word_link_name is 'the used unique name (either user created or generic based on the underlying)';

-- --------------------------------------------------------

--
-- Table structure for table values
--

CREATE TABLE IF NOT EXISTS values
(
    value_id           BIGSERIAL PRIMARY KEY,
    user_id            bigint                    DEFAULT NULL,
    word_value         double precision NOT NULL,
    source_id          bigint                    DEFAULT NULL,
    phrase_group_id    bigint                    DEFAULT NULL,
    time_word_id       bigint                    DEFAULT NULL,
    last_update        timestamp        NULL     DEFAULT NULL,
    description        text,
    excluded           smallint                  DEFAULT NULL,
    protection_type_id bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE values is 'long list';
COMMENT ON COLUMN values.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN values.phrase_group_id is 'temp field to increase speed created by the value term links';
COMMENT ON COLUMN values.time_word_id is 'special field just to speed up queries';
COMMENT ON COLUMN values.last_update is 'for fast recalculation';
COMMENT ON COLUMN values.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN values.excluded is 'the default exclude setting for most users';

-- --------------------------------------------------------

--
-- Table structure for table value_formula_links
--

CREATE TABLE IF NOT EXISTS value_formula_links
(
    value_formula_link_id BIGSERIAL PRIMARY KEY,
    value_id              bigint DEFAULT NULL,
    formula_id            bigint DEFAULT NULL,
    user_id               bigint DEFAULT NULL,
    condition_formula_id  bigint DEFAULT NULL,
    comment               text
);

COMMENT ON TABLE value_formula_links is 'used to select if a saved value should be used or a calculated value';
COMMENT ON COLUMN value_formula_links.condition_formula_id is 'if true or 1  to formula is preferred';

-- --------------------------------------------------------

--
-- Table structure for table value_phrase_links
--

CREATE TABLE IF NOT EXISTS value_phrase_links
(
    value_phrase_link_id BIGSERIAL PRIMARY KEY,
    user_id              bigint           DEFAULT NULL,
    value_id             bigint NOT NULL,
    phrase_id            bigint NOT NULL,
    weight               double precision DEFAULT NULL,
    link_type_id         bigint           DEFAULT NULL,
    condition_formula_id bigint           DEFAULT NULL
);

COMMENT ON TABLE value_phrase_links is 'link single word or triple to a value only for fast search';
COMMENT ON COLUMN value_phrase_links.condition_formula_id is 'formula_id of a formula with a boolean result; the term is only added if formula result is true';

-- --------------------------------------------------------

--
-- Table structure for table value_relations
--

CREATE TABLE IF NOT EXISTS value_relations
(
    value_link_id BIGSERIAL PRIMARY KEY,
    from_value    bigint NOT NULL,
    to_value      bigint NOT NULL,
    link_type_id  bigint NOT NULL
);

COMMENT ON TABLE value_relations is 'to link two values directly; maybe not used';

-- --------------------------------------------------------

--
-- Table structure for table value_time_series
--

CREATE TABLE IF NOT EXISTS value_time_series
(
    value_time_series_id BIGSERIAL PRIMARY KEY,
    user_id              bigint    NOT NULL,
    source_id            bigint         DEFAULT NULL,
    phrase_group_id      bigint    NOT NULL,
    excluded             smallint       DEFAULT NULL,
    share_type_id        bigint         DEFAULT NULL,
    protection_type_id   bigint    NOT NULL,
    last_update          timestamp NULL DEFAULT NULL
);

COMMENT ON TABLE value_time_series is 'common parameters for a list of intraday values';

-- --------------------------------------------------------

--
-- Table structure for table value_ts_data
--

CREATE TABLE IF NOT EXISTS value_ts_data
(
    value_time_series_id BIGSERIAL PRIMARY KEY,
    val_time             timestamp NOT NULL,
    number               float     NOT NULL
);

COMMENT ON TABLE value_ts_data is 'for efficient saving of daily or intraday values';

-- --------------------------------------------------------

--
-- Table structure for table verbs
--

CREATE TABLE IF NOT EXISTS verbs
(
    verb_id             BIGSERIAL PRIMARY KEY,
    verb_name           varchar(100) NOT NULL,
    code_id             varchar(255) DEFAULT NULL,
    description         text,
    condition_type      bigint       DEFAULT NULL,
    formula_name        varchar(200) DEFAULT NULL,
    name_plural_reverse varchar(200) DEFAULT NULL,
    name_plural         varchar(200) DEFAULT NULL,
    name_reverse        varchar(200) DEFAULT NULL,
    words               bigint       DEFAULT NULL
);

COMMENT ON TABLE verbs is 'it is fixed coded how to behavior for each type is';
COMMENT ON COLUMN verbs.formula_name is 'naming used in formulas';
COMMENT ON COLUMN verbs.name_plural_reverse is 'english description for the reverse list, e.g. Companies are ...';
COMMENT ON COLUMN verbs.words is 'used for how many terms';

-- --------------------------------------------------------

--
-- Table structure for table verb_usages
--

CREATE TABLE IF NOT EXISTS verb_usages
(
    verb_usage_id BIGSERIAL PRIMARY KEY,
    verb_id       bigint NOT NULL,
    table_id      bigint NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table views
--

CREATE TABLE IF NOT EXISTS views
(
    view_id      BIGSERIAL PRIMARY KEY,
    user_id      bigint       DEFAULT NULL,
    view_name    varchar(100) NOT NULL,
    comment      text         DEFAULT NULL,
    view_type_id bigint       DEFAULT NULL,
    code_id      varchar(100) DEFAULT NULL,
    excluded     smallint     DEFAULT NULL
);

COMMENT ON TABLE views is 'all user interfaces should be listed here';
COMMENT ON COLUMN views.view_name is 'for easy selection';

-- --------------------------------------------------------

--
-- Table structure for table view_components
--

CREATE TABLE IF NOT EXISTS view_components
(
    view_component_id           BIGSERIAL PRIMARY KEY,
    user_id                     bigint       NOT NULL,
    view_component_name         varchar(100) NOT NULL,
    comment                     text,
    view_component_type_id      bigint   DEFAULT NULL,
    word_id_row                 bigint   DEFAULT NULL,
    formula_id                  bigint   DEFAULT NULL,
    word_id_col                 bigint   DEFAULT NULL,
    word_id_col2                bigint   DEFAULT NULL,
    excluded                    smallint DEFAULT NULL,
    linked_view_component_id    bigint   DEFAULT NULL,
    view_component_link_type_id bigint   DEFAULT NULL,
    link_type_id                bigint   DEFAULT NULL
);

COMMENT ON TABLE view_components is 'the single components of a mask';
COMMENT ON COLUMN view_components.view_component_name is 'just for easy selection';
COMMENT ON COLUMN view_components.word_id_row is 'for a tree the related value the start node';
COMMENT ON COLUMN view_components.formula_id is 'used for type 6';
COMMENT ON COLUMN view_components.word_id_col is 'to define the type for the table columns';
COMMENT ON COLUMN view_components.word_id_col2 is 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart';
COMMENT ON COLUMN view_components.linked_view_component_id is 'to link this mask entry to another mask entry';
COMMENT ON COLUMN view_components.view_component_link_type_id is 'to define how this entry links to the other entry';
COMMENT ON COLUMN view_components.link_type_id is 'e.g. for type 4 to select possible terms';

-- --------------------------------------------------------

--
-- Table structure for table view_component_links
--

CREATE TABLE IF NOT EXISTS view_component_links
(
    view_component_link_id BIGSERIAL PRIMARY KEY,
    user_id                bigint NOT NULL,
    view_id                bigint NOT NULL,
    view_component_id      bigint NOT NULL,
    order_nbr              bigint NOT NULL,
    position_type          bigint NOT NULL DEFAULT '2',
    excluded               smallint        DEFAULT NULL
);

COMMENT ON TABLE view_component_links is 'A named mask entry can be used in several masks e.g. the company name';
COMMENT ON COLUMN view_component_links.position_type is '1=side, 2 =below';

-- --------------------------------------------------------

--
-- Table structure for table view_component_link_types
--

CREATE TABLE IF NOT EXISTS view_component_link_types
(
    view_component_link_type_id BIGSERIAL PRIMARY KEY,
    type_name                   varchar(200) NOT NULL,
    code_id                     varchar(50)  NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table view_component_position_types
--

CREATE TABLE IF NOT EXISTS view_component_position_types
(
    view_component_position_type_id BIGSERIAL PRIMARY KEY,
    type_name                       varchar(100) NOT NULL,
    description                     text         NOT NULL
);

COMMENT ON TABLE view_component_position_types is 'sideways or down';

-- --------------------------------------------------------

--
-- Table structure for table view_component_types
--

CREATE TABLE IF NOT EXISTS view_component_types
(
    view_component_type_id BIGSERIAL PRIMARY KEY,
    type_name              varchar(100) NOT NULL,
    description            text DEFAULT NULL,
    code_id                varchar(100) NOT NULL
);

COMMENT ON TABLE view_component_types is 'fixed text, term or formula result';

-- --------------------------------------------------------

--
-- Table structure for table view_link_types
--

CREATE TABLE IF NOT EXISTS view_link_types
(
    view_link_type_id BIGSERIAL PRIMARY KEY,
    type_name         varchar(200) NOT NULL,
    comment           text         NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table view_type_list
--

CREATE TABLE IF NOT EXISTS view_types
(
    view_type_id BIGSERIAL PRIMARY KEY,
    type_name    varchar(200) NOT NULL,
    description  text         NOT NULL,
    code_id      varchar(100) DEFAULT NULL
);

COMMENT ON TABLE view_types is 'to group the masks a link a basic format';

-- --------------------------------------------------------

--
-- Table structure for table view_word_links
--

CREATE TABLE IF NOT EXISTS view_word_links
(
    view_term_link_id BIGSERIAL PRIMARY KEY,
    word_id           bigint NOT NULL,
    type_id           bigint NOT NULL DEFAULT '1',
    link_type_id      bigint          DEFAULT NULL,
    view_id           bigint          DEFAULT NULL
);

COMMENT ON TABLE view_word_links is 'used to define the default mask for a term or a term group';
COMMENT ON COLUMN view_word_links.type_id is '1 = from_term_id is link the terms table; 2=link to the term_links table;3=to term_groups';

-- --------------------------------------------------------

--
-- Table structure for table words
--

CREATE TABLE IF NOT EXISTS words
(
    word_id            BIGSERIAL PRIMARY KEY,
    user_id            bigint                DEFAULT NULL,
    word_name          varchar(200) NOT NULL,
    plural             varchar(200)          DEFAULT NULL,
    description        text                  DEFAULT NULL,
    word_type_id       bigint                DEFAULT NULL,
    view_id            bigint                DEFAULT NULL,
    values             bigint                DEFAULT NULL,
    excluded           smallint              DEFAULT NULL,
    share_type_id      smallint              DEFAULT NULL,
    protection_type_id smallint     NOT NULL DEFAULT '1'
);

COMMENT ON TABLE words is 'probably all text of th db';
COMMENT ON COLUMN words.user_id is 'user_id of the user that has created the term';
COMMENT ON COLUMN words.plural is 'to be replaced by a language form entry';
COMMENT ON COLUMN words.description is 'to be replaced by a language form entry';
COMMENT ON COLUMN words.view_id is 'the default mask for this term';
COMMENT ON COLUMN words.values is 'number of values linked to the term, which gives an indication of the importance';

-- --------------------------------------------------------

--
-- Table structure for table word_del_confirms
--

CREATE TABLE IF NOT EXISTS word_del_confirms
(
    word_del_request_id BIGSERIAL PRIMARY KEY,
    user_id             bigint    NOT NULL,
    confirm             timestamp NULL DEFAULT NULL,
    reject              timestamp NULL DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table word_del_requests
--

CREATE TABLE IF NOT EXISTS word_del_requests
(
    word_del_request_id BIGSERIAL PRIMARY KEY,
    word_id             bigint       NOT NULL,
    word_name           varchar(200) NOT NULL,
    started             timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    canceled            timestamp    NULL     DEFAULT NULL,
    confirmed           timestamp    NULL     DEFAULT NULL,
    finished            timestamp    NULL     DEFAULT NULL,
    user_id             bigint       NOT NULL
);

COMMENT ON COLUMN word_del_requests.user_id is 'the user who has requested the term deletion';

-- --------------------------------------------------------

--
-- Table structure for table word_links
--

CREATE TABLE IF NOT EXISTS word_links
(
    word_link_id                BIGSERIAL PRIMARY KEY,
    user_id                     bigint            DEFAULT NULL,
    from_phrase_id              bigint   NOT NULL,
    verb_id                     bigint   NOT NULL,
    to_phrase_id                bigint   NOT NULL,
    word_link_condition_id      bigint            DEFAULT NULL,
    word_link_condition_type_id bigint            DEFAULT NULL,
    description                 text,
    word_type_id                bigint            DEFAULT NULL,
    word_link_name              varchar(200)      DEFAULT NULL,
    excluded                    smallint          DEFAULT NULL,
    share_type_id               smallint          DEFAULT NULL,
    protection_type_id          smallint NOT NULL DEFAULT '1'
);

COMMENT ON COLUMN word_links.word_link_condition_id is 'formula_id of a formula with a boolean result; the term is only added if formula result is true';
COMMENT ON COLUMN word_links.word_link_condition_type_id is 'maybe not needed';
COMMENT ON COLUMN word_links.word_link_name is 'the used unique name (either user created or generic based on the underlying)';

-- --------------------------------------------------------

--
-- Table structure for table word_periods
--

CREATE TABLE IF NOT EXISTS word_periods
(
    word_id BIGSERIAL PRIMARY KEY,
    "from"  timestamp NOT NULL,
    "to"    timestamp NOT NULL
);

COMMENT ON TABLE word_periods is 'to define the time period for time terms';

-- --------------------------------------------------------

--
-- Table structure for table word_types
--

CREATE TABLE IF NOT EXISTS word_types
(
    word_type_id   BIGSERIAL PRIMARY KEY,
    type_name      varchar(200) NOT NULL,
    description    text,
    code_id        varchar(100) DEFAULT NULL,
    scaling_factor bigint       DEFAULT NULL,
    word_symbol    varchar(5)   DEFAULT NULL
);

COMMENT ON COLUMN word_types.scaling_factor is 'e.g. for percent the scaling factor is 100';
COMMENT ON COLUMN word_types.word_symbol is 'e.g. for percent the symbol is %';

-- --------------------------------------------------------

--
-- Structure for view phrase_group_phrase_links
--

CREATE OR REPLACE VIEW phrase_group_phrase_links AS
SELECT w.phrase_group_word_link_id AS phrase_group_phrase_link_id,
       w.phrase_group_id           AS phrase_group_id,
       w.word_id                   AS phrase_id
FROM phrase_group_word_links AS w
UNION
SELECT t.phrase_group_triple_link_id AS phrase_group_phrase_link_id,
       t.phrase_group_id             AS phrase_group_id,
       (t.triple_id * -(1))          AS phrase_id
FROM phrase_group_triple_links AS t;

-- --------------------------------------------------------

--
-- Structure for view user_phrase_group_phrase_links
--

CREATE OR REPLACE VIEW user_phrase_group_phrase_links AS
SELECT w.phrase_group_word_link_id AS phrase_group_phrase_link_id,
       w.user_id                   AS user_id,
       w.excluded                  AS excluded
FROM user_phrase_group_word_links AS w
UNION
SELECT t.phrase_group_triple_link_id AS phrase_group_phrase_link_id,
       t.user_id                     AS user_id,
       t.excluded                    AS excluded
FROM user_phrase_group_triple_links AS t;

--
-- Indexes for dumped tables
--

--
-- Indexes for table changes
--
CREATE INDEX change_table_idx ON changes (change_field_id, row_id);
CREATE INDEX change_action_idx ON changes (change_action_id);

--
-- Indexes for table change_fields
--
CREATE INDEX change_field_table_idx ON change_fields (table_id);

--
-- Indexes for table change_links
--
CREATE INDEX change_link_user_idx ON change_links (user_id);
CREATE INDEX change_link_table_idx ON change_links (change_table_id);
CREATE INDEX change_link_action_idx ON change_links (change_action_id);

--
-- Indexes for table config
--
CREATE UNIQUE INDEX config_idx ON config (code_id);

--
-- Indexes for table formulas
--
CREATE UNIQUE INDEX formula_name_idx ON formulas (formula_name);
CREATE INDEX formula_user_idx ON formulas (user_id);
CREATE INDEX formula_type_idx ON formulas (formula_type_id);
CREATE INDEX formula_protection_type_idx ON formulas (protection_type_id);

--
-- Indexes for table formula_elements
--
CREATE INDEX formula_element_idx ON formula_elements (formula_id);
CREATE INDEX formula_element_type_idx ON formula_elements (formula_element_type_id);

--
-- Indexes for table formula_links
--
CREATE INDEX formula_link_user_idx ON formula_links (user_id);
CREATE INDEX formula_link_idx ON formula_links (formula_id);
CREATE INDEX formula_link_type_idx ON formula_links (link_type_id);

--
-- Indexes for table formula_values
--
CREATE UNIQUE INDEX formula_value_idx ON formula_values (formula_id, user_id, phrase_group_id, time_word_id,
                                                         source_phrase_group_id, source_time_word_id);
CREATE INDEX formula_value_user_idx ON formula_values (user_id);

--
-- Indexes for table phrase_groups
--
CREATE UNIQUE INDEX phrase_group_term_idx ON phrase_groups (word_ids, triple_ids);

--
-- Indexes for table phrase_group_triple_links
--
CREATE INDEX phrase_group_triple_link_group_idx ON phrase_group_triple_links (phrase_group_id);
CREATE INDEX phrase_group_triple_link_idx ON phrase_group_triple_links (triple_id);

--
-- Indexes for table phrase_group_word_links
--
CREATE INDEX phrase_group_word_link_idx ON phrase_group_word_links (phrase_group_id);
CREATE INDEX phrase_group_word_link_word_idx ON phrase_group_word_links (word_id);

--
-- Indexes for table protection_types
--
CREATE UNIQUE INDEX protection_type_idx ON protection_types (protection_type_id);

--
-- Indexes for table refs
--
CREATE UNIQUE INDEX ref_phrase_type_idx ON refs (phrase_id, ref_type_id);
CREATE INDEX ref_type_idx ON refs (ref_type_id);

--
-- Indexes for table ref_types
--
CREATE UNIQUE INDEX ref_type_name_idx ON ref_types (type_name, code_id);

--
-- Indexes for table source_values
--
CREATE INDEX source_value_value_idx ON source_values (value_id);
CREATE INDEX source_value_source_idx ON source_values (source_id);
CREATE INDEX source_value_user_idx ON source_values (user_id);

--
-- Indexes for table sys_log
--
CREATE INDEX sys_log_time ON sys_log (sys_log_time);
CREATE INDEX sys_log_type_idx ON sys_log (sys_log_type_id);
CREATE INDEX sys_log_function_idx ON sys_log (sys_log_function_id);
CREATE INDEX sys_log_status_idx ON sys_log (sys_log_status_id);

--
-- Indexes for table sys_script_times
--
CREATE INDEX sys_script_time_idx ON sys_script_times (sys_script_id);

--
-- Indexes for table users
--
CREATE UNIQUE INDEX user_name_idx ON users (user_name);
CREATE INDEX user_type_idx ON users (user_type_id);

--
-- Indexes for table user_formulas
--
CREATE UNIQUE INDEX user_formula_unique_idx ON user_formulas (formula_id, user_id);
CREATE INDEX user_formula_idx ON user_formulas (formula_id);
CREATE INDEX user_formula_user_idx ON user_formulas (user_id);
CREATE INDEX user_formula_type_idx ON user_formulas (formula_type_id);
CREATE INDEX user_formula_share_idx ON user_formulas (share_type_id);

--
-- Indexes for table user_formula_links
--
CREATE UNIQUE INDEX user_formula_link_unique_idx ON user_formula_links (formula_link_id, user_id);
CREATE INDEX user_formula_link_idx ON user_formula_links (formula_link_id);
CREATE INDEX user_formula_link_user_idx ON user_formula_links (user_id);
CREATE INDEX user_formula_link_type_idx ON user_formula_links (link_type_id);

--
-- Indexes for table user_phrase_groups
--
CREATE UNIQUE INDEX user_phrase_group_unique_id ON user_phrase_groups (phrase_group_id, user_id);
CREATE INDEX user_phrase_group_idx ON user_phrase_groups (phrase_group_id);
CREATE INDEX user_phrase_group_user_idx ON user_phrase_groups (user_id);

--
-- Indexes for table user_phrase_group_triple_links
--
CREATE UNIQUE INDEX user_phrase_group_triple_link_unique_idx ON user_phrase_group_triple_links (phrase_group_triple_link_id, user_id);
CREATE INDEX user_phrase_group_triple_link_idx ON user_phrase_group_triple_links (phrase_group_triple_link_id);
CREATE INDEX user_phrase_group_triple_user_idx ON user_phrase_group_triple_links (user_id);

--
-- Indexes for table user_phrase_group_word_links
--
CREATE UNIQUE INDEX user_phrase_group_word_link_unique_idx ON user_phrase_group_word_links (phrase_group_word_link_id, user_id);
CREATE INDEX user_phrase_group_word_link_idx ON user_phrase_group_word_links (phrase_group_word_link_id);
CREATE INDEX user_phrase_group_word_user_idx ON user_phrase_group_word_links (user_id);

--
-- Indexes for table user_sources
--
ALTER TABLE user_sources
    ADD CONSTRAINT user_source_pkey PRIMARY KEY (source_id, user_id);
CREATE INDEX user_source_user_idx ON user_sources (user_id);
CREATE INDEX user_source_idx ON user_sources (source_id);
CREATE INDEX user_source_type_idx ON user_sources (source_type_id);

--
-- Indexes for table user_values
--
ALTER TABLE user_values
    ADD CONSTRAINT user_value_pkey PRIMARY KEY (value_id, user_id);
CREATE INDEX user_value_user_idx ON user_values (user_id);
CREATE INDEX user_value_source_idx ON user_values (source_id);
CREATE INDEX user_value_value_idx ON user_values (value_id);
CREATE INDEX user_value_share_idx ON user_values (share_type_id);
CREATE INDEX user_value_protection_idx ON user_values (protection_type_id);

--
-- Indexes for table user_views
--
ALTER TABLE user_views
    ADD CONSTRAINT user_view_pkey PRIMARY KEY (view_id, user_id);
CREATE INDEX user_view_user_idx ON user_views (user_id);
CREATE INDEX user_view_type_idx ON user_views (view_type_id);
CREATE INDEX user_view_idx ON user_views (view_id);

--
-- Indexes for table user_view_components
--
ALTER TABLE user_view_components
    ADD CONSTRAINT user_view_component_pkey PRIMARY KEY (view_component_id, user_id);
CREATE INDEX user_view_component_user_idx ON user_view_components (user_id);
CREATE INDEX user_view_component_idx ON user_view_components (view_component_id);
CREATE INDEX user_view_component_type_idx ON user_view_components (view_component_type_id);

--
-- Indexes for table user_view_component_links
--
ALTER TABLE user_view_component_links
    ADD CONSTRAINT user_view_component_link_pkey PRIMARY KEY (view_component_link_id, user_id);
CREATE INDEX user_view_component_link_user_idx ON user_view_component_links (user_id);
CREATE INDEX user_view_component_link_position_idx ON user_view_component_links (position_type);
CREATE INDEX user_view_component_link_view_idx ON user_view_component_links (view_component_link_id);

--
-- Indexes for table user_words
--
ALTER TABLE user_words
    ADD CONSTRAINT user_words_pkey PRIMARY KEY (word_id, user_id, language_id);
CREATE INDEX user_word_user_idx ON user_words (user_id);
CREATE INDEX user_word_language_idx ON user_words (language_id);
CREATE INDEX user_word_type_idx ON user_words (word_type_id);
CREATE INDEX user_word_view_idx ON user_words (view_id);

--
-- Indexes for table user_word_links
--
CREATE UNIQUE INDEX user_word_link_unique_idx ON user_word_links (word_link_id, user_id);
CREATE INDEX user_word_link_idx ON user_word_links (word_link_id);
CREATE INDEX user_word_link_user_idx ON user_word_links (user_id);

--
-- Indexes for table values
--
CREATE INDEX value_user_idx ON "values" (user_id);
CREATE INDEX value_source_idx ON "values" (source_id);
CREATE INDEX value_phrase_group_idx ON "values" (phrase_group_id);
CREATE INDEX value_time_word_idx ON "values" (time_word_id);
CREATE INDEX value_protection_idx ON "values" (protection_type_id);

--
-- Indexes for table value_phrase_links
--
CREATE UNIQUE INDEX value_phrase_link_user_idx ON value_phrase_links (user_id, value_id, phrase_id);
CREATE INDEX value_phrase_link_value_idx ON value_phrase_links (value_id);
CREATE INDEX value_phrase_link_phrase_idx ON value_phrase_links (phrase_id);


--
-- Indexes for table value_ts_data
--
CREATE INDEX value_time_series_idx ON value_ts_data (value_time_series_id, val_time);

--
-- Indexes for table views
--
CREATE INDEX view_type_idx ON views (view_type_id);

--
-- Indexes for table view_components
--
CREATE INDEX view_component_formula_idx ON view_components (formula_id);

--
-- Indexes for table view_component_links
--
CREATE INDEX view_component_link_idx ON view_component_links (view_id);
CREATE INDEX view_component_link_component_idx ON view_component_links (view_component_id);
CREATE INDEX view_component_link_position__idx ON view_component_links (position_type);


--
-- Indexes for table words
--
CREATE UNIQUE INDEX word_name_idx ON words (word_name);
CREATE INDEX word_type_idx ON words (word_type_id);
CREATE INDEX word_view_idx ON words (view_id);


--
-- Constraints for dumped tables
--

--
-- AUTO_INCREMENT for exported tables
--


--
-- Constraints for table changes
--
ALTER TABLE changes
    ADD CONSTRAINT changes_fk_1 FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id),
    ADD CONSTRAINT changes_fk_2 FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id);

--
-- Constraints for table change_fields
--
ALTER TABLE change_fields
    ADD CONSTRAINT change_fields_fk_1 FOREIGN KEY (table_id) REFERENCES change_tables (change_table_id);

--
-- Constraints for table change_links
--
ALTER TABLE change_links
    ADD CONSTRAINT change_links_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE NO ACTION,
    ADD CONSTRAINT change_links_fk_2 FOREIGN KEY (change_table_id) REFERENCES change_tables (change_table_id),
    ADD CONSTRAINT change_links_fk_3 FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id);

--
-- Constraints for table formulas
--
ALTER TABLE formulas
    ADD CONSTRAINT formulas_fk_1 FOREIGN KEY (formula_type_id) REFERENCES formula_types (formula_type_id),
    ADD CONSTRAINT formulas_fk_2 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT formulas_fk_3 FOREIGN KEY (protection_type_id) REFERENCES protection_types (protection_type_id);

--
-- Constraints for table formula_elements
--
ALTER TABLE formula_elements
    ADD CONSTRAINT formula_elements_fk_1 FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- Constraints for table formula_links
--
ALTER TABLE formula_links
    ADD CONSTRAINT formula_links_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- Constraints for table formula_values
--
ALTER TABLE formula_values
    ADD CONSTRAINT formula_values_fk_1 FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT formula_values_fk_2 FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- Constraints for table phrase_group_triple_links
--
ALTER TABLE phrase_group_triple_links
    ADD CONSTRAINT phrase_group_triple_links_fk_1 FOREIGN KEY (phrase_group_id) REFERENCES phrase_groups (phrase_group_id),
    ADD CONSTRAINT phrase_group_triple_links_fk_2 FOREIGN KEY (triple_id) REFERENCES word_links (word_link_id);

--
-- Constraints for table phrase_group_word_links
--
ALTER TABLE phrase_group_word_links
    ADD CONSTRAINT phrase_group_word_links_fk_1 FOREIGN KEY (phrase_group_id) REFERENCES phrase_groups (phrase_group_id);

--
-- Constraints for table refs
--
ALTER TABLE refs
    ADD CONSTRAINT refs_fk_1 FOREIGN KEY (ref_type_id) REFERENCES ref_types (ref_type_id);

--
-- Constraints for table source_values
--
ALTER TABLE source_values
    ADD CONSTRAINT source_values_fk_3 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT source_values_fk_1 FOREIGN KEY (value_id) REFERENCES values (value_id),
    ADD CONSTRAINT source_values_fk_2 FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- Constraints for table sys_log
--
ALTER TABLE sys_log
    ADD CONSTRAINT sys_log_fk_1 FOREIGN KEY (sys_log_status_id) REFERENCES sys_log_status (sys_log_status_id),
    ADD CONSTRAINT sys_log_fk_2 FOREIGN KEY (sys_log_function_id) REFERENCES sys_log_functions (sys_log_function_id),
    ADD CONSTRAINT sys_log_fk_3 FOREIGN KEY (sys_log_type_id) REFERENCES sys_log_types (sys_log_type_id);

--
-- Constraints for table sys_script_times
--
ALTER TABLE sys_script_times
    ADD CONSTRAINT sys_script_times_fk_1 FOREIGN KEY (sys_script_id) REFERENCES sys_scripts (sys_script_id);

--
-- Constraints for table users
--
ALTER TABLE users
    ADD CONSTRAINT users_fk_1 FOREIGN KEY (user_type_id) REFERENCES user_types (user_type_id),
    ADD CONSTRAINT users_fk_2 FOREIGN KEY (user_profile_id) REFERENCES user_profiles (profile_id);

--
-- Constraints for table user_formulas
--
ALTER TABLE user_formulas
    ADD CONSTRAINT user_formulas_fk_4 FOREIGN KEY (share_type_id) REFERENCES share_types (share_type_id),
    ADD CONSTRAINT user_formulas_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_formulas_fk_2 FOREIGN KEY (formula_type_id) REFERENCES formula_types (formula_type_id),
    ADD CONSTRAINT user_formulas_fk_3 FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- Constraints for table user_formula_links
--
ALTER TABLE user_formula_links
    ADD CONSTRAINT user_formula_links_fk_1 FOREIGN KEY (formula_link_id) REFERENCES formula_links (formula_link_id),
    ADD CONSTRAINT user_formula_links_fk_2 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_formula_links_fk_3 FOREIGN KEY (link_type_id) REFERENCES formula_link_types (formula_link_type_id);

--
-- Constraints for table user_phrase_groups
--
ALTER TABLE user_phrase_groups
    ADD CONSTRAINT user_phrase_groups_fk_1 FOREIGN KEY (phrase_group_id) REFERENCES phrase_groups (phrase_group_id),
    ADD CONSTRAINT user_phrase_groups_fk_2 FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- Constraints for table user_phrase_group_triple_links
--
ALTER TABLE user_phrase_group_triple_links
    ADD CONSTRAINT user_phrase_group_triple_links_fk_1 FOREIGN KEY (phrase_group_triple_link_id) REFERENCES phrase_group_triple_links (phrase_group_triple_link_id),
    ADD CONSTRAINT user_phrase_group_triple_links_fk_2 FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- Constraints for table user_phrase_group_word_links
--
ALTER TABLE user_phrase_group_word_links
    ADD CONSTRAINT user_phrase_group_word_links_fk_1 FOREIGN KEY (phrase_group_word_link_id) REFERENCES phrase_group_word_links (phrase_group_word_link_id),
    ADD CONSTRAINT user_phrase_group_word_links_fk_2 FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- Constraints for table user_sources
--
ALTER TABLE user_sources
    ADD CONSTRAINT user_sources_fk_1 FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT user_sources_fk_2 FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- Constraints for table user_values
--
ALTER TABLE user_values
    ADD CONSTRAINT user_values_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_fk_2 FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT user_values_fk_3 FOREIGN KEY (share_type_id) REFERENCES share_types (share_type_id);

--
-- Constraints for table user_views
--
ALTER TABLE user_views
    ADD CONSTRAINT user_views_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_views_fk_2 FOREIGN KEY (view_type_id) REFERENCES view_types (view_type_id),
    ADD CONSTRAINT user_views_fk_3 FOREIGN KEY (view_id) REFERENCES views (view_id);

--
-- Constraints for table user_view_components
--
ALTER TABLE user_view_components
    ADD CONSTRAINT user_view_components_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_view_components_fk_2 FOREIGN KEY (view_component_id) REFERENCES view_components (view_component_id),
    ADD CONSTRAINT user_view_components_fk_3 FOREIGN KEY (view_component_type_id) REFERENCES view_component_types (view_component_type_id);

--
-- Constraints for table user_view_component_links
--
ALTER TABLE user_view_component_links
    ADD CONSTRAINT user_view_component_links_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_view_component_links_fk_2 FOREIGN KEY (view_component_link_id) REFERENCES view_component_links (view_component_link_id),
    ADD CONSTRAINT user_view_component_links_fk_3 FOREIGN KEY (position_type) REFERENCES view_component_position_types (view_component_position_type_id);

--
-- Constraints for table user_words
--
ALTER TABLE user_words
    ADD CONSTRAINT user_words_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_words_fk_2 FOREIGN KEY (word_type_id) REFERENCES word_types (word_type_id),
    ADD CONSTRAINT user_words_fk_3 FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT user_words_fk_4 FOREIGN KEY (word_id) REFERENCES words (word_id);

--
-- Constraints for table user_word_links
--
ALTER TABLE user_word_links
    ADD CONSTRAINT user_word_links_fk_1 FOREIGN KEY (word_link_id) REFERENCES word_links (word_link_id),
    ADD CONSTRAINT user_word_links_fk_2 FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- Constraints for table values
--
ALTER TABLE values
    ADD CONSTRAINT values_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT values_fk_2 FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_fk_3 FOREIGN KEY (phrase_group_id) REFERENCES phrase_groups (phrase_group_id),
    ADD CONSTRAINT values_fk_4 FOREIGN KEY (protection_type_id) REFERENCES protection_types (protection_type_id);

--
-- Constraints for table view_components
--
ALTER TABLE view_components
    ADD CONSTRAINT view_components_fk_2 FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- Constraints for table view_component_links
--
ALTER TABLE view_component_links
    ADD CONSTRAINT view_component_links_fk_1 FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT view_component_links_fk_2 FOREIGN KEY (position_type) REFERENCES view_component_position_types (view_component_position_type_id),
    ADD CONSTRAINT view_component_links_fk_3 FOREIGN KEY (view_component_id) REFERENCES view_components (view_component_id);

--
-- Constraints for table words
--
ALTER TABLE words
    ADD CONSTRAINT word_name UNIQUE (word_name);
ALTER TABLE words
    ADD CONSTRAINT words_fk_1 FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT words_fk_2 FOREIGN KEY (word_type_id) REFERENCES word_types (word_type_id);

--
-- Constraints for table word_periods
--
ALTER TABLE word_periods
    ADD CONSTRAINT word_periods_fk_1 FOREIGN KEY (word_id) REFERENCES words (word_id);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
