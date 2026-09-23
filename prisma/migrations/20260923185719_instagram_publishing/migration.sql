-- AlterEnum
-- This migration adds more than one value to an enum.
-- With PostgreSQL versions 11 and earlier, this is not possible
-- in a single migration. This can be worked around by creating
-- multiple migrations, each migration adding only one value to
-- the enum.


ALTER TYPE "PostStatus" ADD VALUE 'PUBLISHING';
ALTER TYPE "PostStatus" ADD VALUE 'FAILED';

-- AlterTable
ALTER TABLE "Post" ADD COLUMN     "containerId" TEXT,
ADD COLUMN     "externalId" TEXT,
ADD COLUMN     "lockedAt" TIMESTAMP(3),
ADD COLUMN     "metricsSyncedAt" TIMESTAMP(3),
ADD COLUMN     "permalink" TEXT,
ADD COLUMN     "publishAttempts" INTEGER NOT NULL DEFAULT 0,
ADD COLUMN     "publishError" TEXT,
ADD COLUMN     "publishStartedAt" TIMESTAMP(3);

-- AlterTable
ALTER TABLE "SocialAccount" ADD COLUMN     "accessToken" TEXT,
ADD COLUMN     "connectedAt" TIMESTAMP(3),
ADD COLUMN     "igUserId" TEXT,
ADD COLUMN     "tokenExpiresAt" TIMESTAMP(3);
