CREATE TYPE "LeadStatus" AS ENUM ('NEW', 'CONTACTED', 'COMPLETED');

ALTER TABLE "Lead"
  ADD COLUMN "status" "LeadStatus" NOT NULL DEFAULT 'NEW',
  ADD COLUMN "notifiedAt" TIMESTAMP(3),
  ADD COLUMN "notificationAttemptedAt" TIMESTAMP(3),
  ADD COLUMN "notificationLockedAt" TIMESTAMP(3),
  ADD COLUMN "notificationAttempts" INTEGER NOT NULL DEFAULT 0,
  ADD COLUMN "notificationError" TEXT;

CREATE INDEX "Lead_notifiedAt_notificationAttemptedAt_idx"
  ON "Lead"("notifiedAt", "notificationAttemptedAt");
