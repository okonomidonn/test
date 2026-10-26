import { timingSafeEqual } from "node:crypto";
import { NextResponse, type NextRequest } from "next/server";
import { retryPendingLeadNotifications } from "@/lib/leads/notifications";

export const maxDuration = 300;

export async function GET(req: NextRequest) {
  const secret = process.env.CRON_SECRET;
  const given = Buffer.from(req.headers.get("authorization") ?? "");
  const expected = Buffer.from(`Bearer ${secret}`);
  if (!secret || given.length !== expected.length || !timingSafeEqual(given, expected)) return NextResponse.json({ error: "unauthorized" }, { status: 401 });
  return NextResponse.json(await retryPendingLeadNotifications());
}
