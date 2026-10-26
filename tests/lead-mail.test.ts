import test from "node:test";
import assert from "node:assert/strict";
import { leadMailConfig, sendLeadNotice } from "../src/lib/leads/mail";

function configure(t: test.TestContext, overrides: Record<string, string> = {}) {
  const values = { LEAD_NOTIFICATIONS_ENABLED: "1", RESEND_API_KEY: "local-test-key", MAIL_FROM: "notice@example.com", LEAD_NOTIFY_EMAIL: "team@example.com", APP_URL: "https://example.com", VERCEL_ENV: "production", ...overrides };
  const previous = Object.fromEntries(Object.keys(values).map((key) => [key, process.env[key]]));
  Object.assign(process.env, values);
  t.after(() => { for (const [key, value] of Object.entries(previous)) { if (value === undefined) delete process.env[key]; else process.env[key] = value; } });
}

test("Previewでは設定済みでも外部へ送らない", async (t) => {
  configure(t, { VERCEL_ENV: "preview" });
  assert.equal(leadMailConfig(), null);
  let called = false;
  await assert.rejects(sendLeadNotice({ id: "test", kind: "CHECK", company: "サンプル" }, async () => { called = true; return new Response(); }));
  assert.equal(called, false);
});

test("送信は明示的な有効化と正しい宛先が必要", (t) => {
  configure(t, { LEAD_NOTIFICATIONS_ENABLED: "0" });
  assert.equal(leadMailConfig(), null);
  process.env.LEAD_NOTIFICATIONS_ENABLED = "1";
  process.env.LEAD_NOTIFY_EMAIL = "broken\r\nBcc:someone@example.com";
  assert.equal(leadMailConfig(), null);
});

test("本文は運営向けの最小限の情報だけで、同じ申し込みに同じ冪等キーを使う", async (t) => {
  configure(t);
  const source = { id: "local-123", kind: "TRIAL" as const, company: "テスト会社", name: "非公開の氏名", email: "private@example.com", message: "非公開の相談内容" };
  const requests: { headers: Headers; body: Record<string, unknown> }[] = [];
  const fetcher: typeof fetch = async (url, options) => {
    assert.equal(url, "https://api.resend.com/emails");
    requests.push({ headers: new Headers(options?.headers), body: JSON.parse(String(options?.body)) });
    return Response.json({ id: "accepted-local" });
  };
  await sendLeadNotice(source, fetcher);
  await sendLeadNotice(source, fetcher);
  assert.deepEqual(requests[0].body.to, ["team@example.com"]);
  assert.equal(requests[0].headers.get("Idempotency-Key"), requests[1].headers.get("Idempotency-Key"));
  const payload = JSON.stringify(requests[0].body);
  for (const value of [source.name, source.email, source.message]) assert.equal(payload.includes(value), false);
  assert.match(String(requests[0].body.text), /2営業日/);
});

test("配信エラー・不正な成功レスポンスは送信済みにしない", async (t) => {
  configure(t);
  const lead = { id: "local-456", kind: "CHECK" as const, company: "テスト" };
  await assert.rejects(sendLeadNotice(lead, async () => new Response("provider error", { status: 500 })), /mail_http_500/);
  await assert.rejects(sendLeadNotice(lead, async () => Response.json({})), /mail_invalid_response/);
});
