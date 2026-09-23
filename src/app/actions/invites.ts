"use server";

import { randomBytes } from "node:crypto";
import { revalidatePath } from "next/cache";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/session";

const INVITE_TTL_MS = 7 * 24 * 60 * 60 * 1000;

export async function createInvite() {
  const user = await requireUser();
  await prisma.invite.create({
    data: {
      token: randomBytes(24).toString("base64url"),
      createdById: user.id,
      expiresAt: new Date(Date.now() + INVITE_TTL_MS),
    },
  });
  revalidatePath("/team");
}

export async function revokeInvite(inviteId: string) {
  await requireUser();
  await prisma.invite.deleteMany({ where: { id: inviteId, usedAt: null } });
  revalidatePath("/team");
}
