-- AlterTable
ALTER TABLE "Post" ADD COLUMN     "imported" BOOLEAN NOT NULL DEFAULT false;

-- CreateIndex
CREATE UNIQUE INDEX "Post_externalId_key" ON "Post"("externalId");
