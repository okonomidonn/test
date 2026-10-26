// 公開する連絡先（特商法の表記・報告書・申し込み通知で共用）。環境変数で上書きできる
export const CONTACT = {
  seller: process.env.LEGAL_SELLER_NAME || "ススメル",
  email: process.env.LEGAL_EMAIL || "jun.torii@keio.jp",
};

// 申し込みがあったら運営者にメールで知らせる（RESEND_API_KEY が未設定なら何もしない。失敗しても申し込みは受け付ける）
export async function notifyLead(lead: { kind: string; company: string; name: string; email: string; websiteUrl: string; message?: string }) {
  const key = process.env.RESEND_API_KEY;
  if (!key) return;
  const plan = lead.kind === "TRIAL" ? "GEO診断・改善トライアル" : "無料チェック";
  const text = [`種類：${plan}`, `会社名・サービス名：${lead.company}`, `お名前：${lead.name}`, `メール：${lead.email}`, `サイト：${lead.websiteUrl}`, `ご相談内容：${lead.message || "—"}`].join("\n");
  try {
    await fetch("https://api.resend.com/emails", {
      method: "POST",
      headers: { Authorization: `Bearer ${key}`, "Content-Type": "application/json" },
      body: JSON.stringify({
        from: process.env.LEAD_NOTIFY_FROM || "ススメル <onboarding@resend.dev>",
        to: [process.env.LEAD_NOTIFY_TO || CONTACT.email],
        reply_to: lead.email,
        subject: `【ススメル】${plan}の申し込み：${lead.company}`,
        text,
      }),
      signal: AbortSignal.timeout(5000),
    });
  } catch (e) {
    console.error("lead notify failed", e);
  }
}
