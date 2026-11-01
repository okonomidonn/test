import { CONTACT } from "@/lib/contact";
import { z } from "zod";
import { BOOK_HEADING } from "./plans";

export type LeadNotice = { id: string; kind: "CHECK" | "TRIAL"; company: string; message?: string | null };

// Previewには本番の申し込みが複製されるため、Previewからは絶対に送信しない。
export function leadMailConfig() {
  if (process.env.LEAD_NOTIFICATIONS_ENABLED !== "1" || process.env.VERCEL_ENV === "preview") return null;
  const from = z.email().safeParse(process.env.MAIL_FROM);
  const to = z.email().safeParse(process.env.LEAD_NOTIFY_EMAIL || CONTACT.email);
  const key = process.env.RESEND_API_KEY;
  if (!key || !from.success || !to.success) return null;
  let origin: URL;
  try {
    origin = new URL(process.env.APP_URL || "https://www.aisusumeru.com");
  } catch {
    return null;
  }
  if (origin.protocol !== "https:" && !(process.env.NODE_ENV !== "production" && origin.protocol === "http:" && ["localhost", "127.0.0.1"].includes(origin.hostname))) return null;
  return { from: from.data, to: to.data, key, origin: origin.origin };
}

// 運営向けの通知には、申し込み者のメール・氏名・自由記入欄を載せず、管理画面で確認する。
export async function sendLeadNotice(lead: LeadNotice, fetcher: typeof fetch = fetch) {
  const config = leadMailConfig();
  if (!config) throw new Error("mail_not_configured");
  const book = !!lead.message?.startsWith(BOOK_HEADING);
  const kind = book ? "資料請求" : lead.kind === "TRIAL" ? "有料プランのご相談" : "無料チェック";
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
      subject: `【AIススメル】${kind}の受付`,
      text: `${kind}を受け付けました。\n会社・サービス：${lead.company}\n\n管理画面で内容を確認してください（ログインが必要です）。\n${url}\n\n${book ? "資料はメールでお送りしました。無料チェックをご案内してください。" : lead.kind === "TRIAL" ? "2営業日以内に進め方と見積もりを連絡してください。" : "3営業日以内に診断結果を連絡してください。"}`,
    }),
    signal: AbortSignal.timeout(10_000),
  });
  if (!response.ok) throw new Error(`mail_http_${response.status}`);
  const data: unknown = await response.json();
  if (!data || typeof data !== "object" || !("id" in data) || typeof data.id !== "string" || !data.id.trim()) throw new Error("mail_invalid_response");
}

// 資料請求をした本人へ、資料のダウンロードURLをメールで送る（配信サービスに渡すのは宛先と氏名だけ）。
export async function sendBookMail(to: { email: string; name: string; company: string }, fetcher: typeof fetch = fetch) {
  const config = leadMailConfig();
  if (!config) throw new Error("mail_not_configured");
  const url = `${config.origin}/ai/jobs-ai-book.pdf`;
  const response = await fetcher("https://api.resend.com/emails", {
    method: "POST",
    headers: { Authorization: `Bearer ${config.key}`, "Content-Type": "application/json" },
    body: JSON.stringify({
      from: `AIススメル <${config.from}>`,
      to: [to.email],
      reply_to: CONTACT.email,
      subject: "【AIススメル】求人サイトのAI集客はじめてBOOKをお送りします",
      text: `${to.company}\n${to.name} 様\n\nAIススメルの資料をご請求いただき、ありがとうございます。\n下記のURLから「求人サイトのAI集客はじめてBOOK」（PDF・全7ページ）をダウンロードできます。\n\n${url}\n\n自社サイトがChatGPTでどう紹介されているかを知りたい場合は、無料チェックもご利用ください（3営業日以内に結果をお送りします）。\n${config.origin}/ai/apply\n\nご不明な点は、このメールにご返信ください。\n\n――――――――\nAIススメル（運営責任者：鳥居潤）\n${CONTACT.email}\n${config.origin}/ai\n`,
    }),
    signal: AbortSignal.timeout(10_000),
  });
  if (!response.ok) throw new Error(`mail_http_${response.status}`);
}
