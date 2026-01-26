BEGIN;

TRUNCATE TABLE
    group_payment,
    group_expense_user,
    group_expense,
    group_user,
    "group",
    users
RESTART IDENTITY CASCADE;

INSERT INTO users (id, firstname, lastname, email, password, active)
VALUES
    (1, 'Anna', 'Nowak', 'anna.nowak@example.com', '$2y$10$7qTB92taQJOpp3MW7wpCz.QI6sgtm4IohMPkjf7.VB61syjg/4kgy', TRUE),
    (2, 'Piotr', 'Zielinski', 'piotr.zielinski@example.com', '$2y$10$7qTB92taQJOpp3MW7wpCz.QI6sgtm4IohMPkjf7.VB61syjg/4kgy', TRUE),
    (3, 'Maria', 'Wisniewska', 'maria.wisniewska@example.com', '$2y$10$7qTB92taQJOpp3MW7wpCz.QI6sgtm4IohMPkjf7.VB61syjg/4kgy', TRUE);

INSERT INTO "group" (id, name, description, owner, status)
VALUES
    (1, 'Mieszkanie', 'Rozliczenia wspolnego mieszkania', 1, 'active'),
    (2, 'Wyjazd', 'Koszty wspolnego wyjazdu', 2, 'active');

INSERT INTO group_user (group_id, user_id)
VALUES
    (1, 1),
    (1, 2),
    (1, 3),
    (2, 1),
    (2, 2),
    (2, 3);

INSERT INTO group_expense (id, group_id, created_by, name, amount)
VALUES
    (1, 1, 1, 'Zakupy spozywcze', 150.00),
    (2, 1, 2, 'Srodki czystosci', 60.00),
    (3, 1, 3, 'Rachunek za internet', 90.00),
    (4, 2, 1, 'Noclegi', 300.00),
    (5, 2, 2, 'Transport', 120.00);

INSERT INTO group_expense_user (expense_id, user_id)
VALUES
    (1, 1),
    (1, 2),
    (1, 3),
    (2, 2),
    (2, 3),
    (3, 1),
    (3, 3),
    (4, 1),
    (4, 2),
    (4, 3),
    (5, 2),
    (5, 3);

INSERT INTO group_payment (id, group_id, from_user, to_user, amount)
VALUES
    (1, 1, 2, 1, 30.00),
    (2, 1, 3, 1, 50.00),
    (3, 1, 1, 3, 20.00),
    (4, 2, 3, 2, 40.00),
    (5, 2, 1, 2, 60.00);

COMMIT;
