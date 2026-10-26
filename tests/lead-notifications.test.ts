import test from "node:test";
import assert from "node:assert/strict";
import { prisma } from "../src/lib/prisma";
import { notifyLead } from "../src/lib/leads/notifications";

const dbUrl = process.env.DATABASE_URL ? new URL(process.env.DATABASE_URL) : null;
const localOnly = dbUrl?.hostname === "127.0.0.1" && dbUrl.port === "55432" && dbUrl.pathname === "/susumeru_local";

test("通知の並行送信・失敗後の再送・未設定をローカルDBで検証", { skip: !localOnly }, async (t) => {
  const keys = ["LEAD_NOTIFICATIONS_ENABLED", "RESEND_API_KEY", "MAIL_FROM", "LEAD_NOTIFY_EMAIL", "APP_URL", "VERCEL_ENV"];
  const previous = Object.fromEntries(keys.map((key) => [key, process.env[key]]));
  Object.assign(process.env, { LEAD_NOTIFICATIONS_ENABLED: "1", RESEND_API_KEY: "local-test-key", MAIL_FROM: "notice@example.com", LEAD_NOTIFY_EMAIL: "team@example.com", APP_URL: "https://example.com", VERCEL_ENV: "production" });
  const ids: string[] = [];
  t.after(async () => {
    await prisma.lead.deleteMany({ where: { id: { in: ids } } });
    await prisma.$disconnect();
    for (const [key, value] of Object.entries(previous)) { if (value === undefined) delete process.env[key]; else process.env[key] = value; }
  });
  const make = async () => { const lead = await prisma.lead.create({ data: { kind: "CHECK", company: "通知テスト専用", name: "ローカルテスト", email: "local@example.com", websiteUrl: "https://example.com" } }); ids.push(lead.id); return lead; };

  const concurrent = await make();
  let enter!: () => void;
  let release!: () => void;
  const entered = new Promise<void>((resolve) => { enter = resolve; });
  const released = new Promise<void>((resolve) => { release = resolve; });
  let sends = 0;
  const first = notifyLead(concurrent.id, async () => { sends++; enter(); await released; });
  await entered;
  assert.equal(await notifyLead(concurrent.id, async () => { sends++; }), "busy");
  release();
  assert.equal(await first, "sent");
  assert.equal(await notifyLead(concurrent.id, async () => { sends++; }), "busy");
  assert.equal(sends, 1);

  const failed = await make();
  assert.equal(await notifyLead(failed.id, async () => { throw new Error("provider secret must not be persisted"); }), "failed");
  const record = await prisma.lead.findUniqueOrThrow({ where: { id: failed.id } });
  assert.equal(record.notifiedAt, null);
  assert.equal(record.notificationError?.includes("secret"), false);
  assert.equal(await notifyLead(failed.id, async () => {}), "sent");
  assert.equal((await prisma.lead.findUniqueOrThrow({ where: { id: failed.id } })).notificationAttempts, 2);

  process.env.LEAD_NOTIFICATIONS_ENABLED = "0";
  const disabled = await make();
  assert.equal(await notifyLead(disabled.id, async () => { throw new Error("must not send"); }), "skipped");
  assert.equal((await prisma.lead.findUniqueOrThrow({ where: { id: disabled.id } })).notificationAttempts, 0);
});
