-- Demo data was seeded into production by earlier builds; remove it.
DELETE FROM "Client" WHERE "name" IN ('カフェ・ド・モカ', '株式会社グリーンフィット');
DELETE FROM "User" WHERE "email" = 'demo@example.com';
