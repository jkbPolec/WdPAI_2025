BEGIN;

CREATE TYPE group_status AS ENUM ('active', 'closed');

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    firstname VARCHAR,
    lastname VARCHAR,
    email VARCHAR UNIQUE,
    password VARCHAR,
    active BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE "group" (
    id SERIAL PRIMARY KEY,
    name VARCHAR,
    description VARCHAR,
    owner INTEGER NOT NULL,
    status group_status NOT NULL
);

ALTER TABLE "group"
    ADD CONSTRAINT fk_group_owner
    FOREIGN KEY (owner) REFERENCES users(id);

CREATE TABLE group_user (
    group_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    PRIMARY KEY (group_id, user_id)
);

ALTER TABLE group_user
    ADD CONSTRAINT fk_group_user_group
    FOREIGN KEY (group_id) REFERENCES "group"(id) ON DELETE CASCADE;

ALTER TABLE group_user
    ADD CONSTRAINT fk_group_user_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

CREATE TABLE group_expense (
    id SERIAL PRIMARY KEY,
    group_id INTEGER NOT NULL,
    created_by INTEGER NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT now(),
    name VARCHAR,
    amount DECIMAL(10,2) NOT NULL
);

ALTER TABLE group_expense
    ADD CONSTRAINT fk_expense_group
    FOREIGN KEY (group_id) REFERENCES "group"(id) ON DELETE CASCADE;

ALTER TABLE group_expense
    ADD CONSTRAINT fk_expense_creator
    FOREIGN KEY (created_by) REFERENCES users(id);

CREATE TABLE group_expense_user (
    expense_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    PRIMARY KEY (expense_id, user_id)
);

ALTER TABLE group_expense_user
    ADD CONSTRAINT fk_expense_user_expense
    FOREIGN KEY (expense_id) REFERENCES group_expense(id) ON DELETE CASCADE;

ALTER TABLE group_expense_user
    ADD CONSTRAINT fk_expense_user_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

CREATE TABLE group_payment (
    id SERIAL PRIMARY KEY,
    group_id INTEGER NOT NULL,
    from_user INTEGER NOT NULL,
    to_user INTEGER NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT now(),
    CHECK (from_user <> to_user)
);

ALTER TABLE group_payment
    ADD CONSTRAINT fk_payment_group
    FOREIGN KEY (group_id) REFERENCES "group"(id) ON DELETE CASCADE;

ALTER TABLE group_payment
    ADD CONSTRAINT fk_payment_from
    FOREIGN KEY (from_user) REFERENCES users(id);

ALTER TABLE group_payment
    ADD CONSTRAINT fk_payment_to
    FOREIGN KEY (to_user) REFERENCES users(id);

CREATE TABLE migrations (
    id SERIAL PRIMARY KEY,
    code VARCHAR UNIQUE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT now()
);

INSERT INTO migrations (code)
VALUES ('V110120261827__base_structure');

COMMIT;
