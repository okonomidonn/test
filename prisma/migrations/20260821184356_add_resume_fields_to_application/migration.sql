/*
  Warnings:

  - Added the required column `fullName` to the `Application` table without a default value. This is not possible if the table is not empty.
  - Added the required column `motivation` to the `Application` table without a default value. This is not possible if the table is not empty.

*/
-- AlterTable
ALTER TABLE "Application" ADD COLUMN     "birthDate" TIMESTAMP(3),
ADD COLUMN     "education" JSONB NOT NULL DEFAULT '[]',
ADD COLUMN     "fullName" TEXT NOT NULL DEFAULT '',
ADD COLUMN     "fullNameKana" TEXT,
ADD COLUMN     "motivation" TEXT NOT NULL DEFAULT '',
ADD COLUMN     "phone" TEXT,
ADD COLUMN     "workExperience" JSONB NOT NULL DEFAULT '[]';

-- Drop the temporary defaults now that existing rows are backfilled;
-- new rows must supply these values explicitly via the application.
ALTER TABLE "Application" ALTER COLUMN "fullName" DROP DEFAULT;
ALTER TABLE "Application" ALTER COLUMN "motivation" DROP DEFAULT;
