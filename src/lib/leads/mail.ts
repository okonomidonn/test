import { CONTACT } from "@/lib/contact";
import { z } from "zod";

export type LeadNotice = { id: string; kind: "CHECK" | "TRIAL"; company: string };

// Previewには本番の申し込みが複製されるため、Previewからは絶対に送信しない。
export function leadMailConfig() {
  if (process.env.LEAD_NOTIFICATIONS_ENABLED !== "1" || process.env.VERCEL_ENV === "preview") return null;
  const from = z.email().safeParse(process.env.MAIL_FROM);
  const to = z.email().safeParse(process.env.LEAD_NOTIFY_EMAIL || CONTACT.email);
  const key = process.env.RESEND_API_KEY;
  if (!key || !from.success || !to.success) return null;
  let origin: URL;
  try {
    origin = new URL(process.env.APP_URL || "https://test-one-xi-90.vercel.app");
  } catch {
    return null;
  }
  if (origin.protocol !== "https:" && !(process.env.NODE_ENV !== "production" && origin.protocol === "http:" && ["localhost", "127.0.0.1"].includes(origin.hostname))) return null;
  return { from: from.data, to: to.data, key, origin: origin.origin };
}

// 申し込み者のメール・氏名・自由記入欄を配信サービスに送らず、管理画面で確認する。
export async function sendLeadNotice(lead: LeadNotice, fetcher: typeof fetch = fetch) {
  const config = leadMailConfig();
  if (!config) throw new Error("mail_not_configured");
  const kind = lead.kind === "TRIAL" ? "トライアル" : "無料チェック";
  const url = `${config.origin}/ai/app/leads#lead-${encodeURIComponent(lead.id)}`;
  const response = await fetcher("https://api.resend.com/emails", {
    method: "POST",
    headers: {
      Authorization: `Bearer ${config.key}`,
      "Content-Type": "application/json",
      "Idempotency-Key": `susumeru-lead-${lead.id}`,
    },
    body: JSON.stringify({
      from: config.from,
      to: [config.to],
      subject: `【AIススメル】${kind}の申し込み`,
      text: `${kind}の申し込みを受け付けました。\n会社・サービス：${lead.company}\n\n管理画面で内容を確認してください（ログインが必要です）。\n${url}\n\n${lead.kind === "TRIAL" ? "2営業日以内に進め方と見積もりを連絡してください。" : "3営業日以内に診断結果を連絡してください。"}`,
    }),
    signal: AbortSignal.timeout(10_000),
  });
  if (!response.ok) throw new Error(`mail_http_${response.status}`);
  const data: unknown = await response.json();
  if (!data || typeof data !== "object" || !("id" in data) || typeof data.id !== "string" || !data.id.trim()) throw new Error("mail_invalid_response");
}
