BEGIN;

ALTER TABLE group_expense
    ADD COLUMN category VARCHAR DEFAULT 'Inne';

COMMIT;
