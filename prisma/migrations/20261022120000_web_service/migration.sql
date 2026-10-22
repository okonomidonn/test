-- CreateEnum
CREATE TYPE "StoreKind" AS ENUM ('RESTAURANT', 'WEB');

-- AlterTable
ALTER TABLE "Store" ADD COLUMN     "audience" TEXT,
ADD COLUMN     "features" TEXT[] DEFAULT ARRAY[]::TEXT[],
ADD COLUMN     "kind" "StoreKind" NOT NULL DEFAULT 'RESTAURANT',
ADD COLUMN     "pitch" TEXT;

