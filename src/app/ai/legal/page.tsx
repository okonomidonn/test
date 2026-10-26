import { CONTACT } from "@/lib/contact";

export const metadata = { title: "特定商取引法に基づく表記 | ススメル" };

// 運営責任者・所在地・電話は環境変数で設定する（未設定なら「請求があれば遅滞なく開示します」と表示）
const DISCLOSE = "請求があった場合は遅滞なく開示します";

export default function LegalPage() {
  const env = process.env;
  const rows: [string, string][] = [
    ["販売事業者", CONTACT.seller],
    ["運営責任者", env.LEGAL_REPRESENTATIVE || DISCLOSE],
    ["所在地", env.LEGAL_ADDRESS || DISCLOSE],
    ["電話番号", env.LEGAL_PHONE || DISCLOSE],
    ["メールアドレス", CONTACT.email],
    ["販売価格", "料金のページに記載（表示価格は税別）"],
    ["商品代金以外の必要料金", "銀行振込の手数料、インターネット接続にかかる通信料"],
    ["支払方法", "銀行振込（請求書払い）"],
    ["支払時期", "GEO診断・改善トライアル：請求書の発行から7日以内（前払い）。継続支援：翌月分を毎月末までに（前払い）"],
    ["サービスの提供時期", "ご入金を確認してから作業を始めます"],
    ["キャンセル・返金", "作業を始める前のキャンセルは全額を返金します。作業を始めた後の返金はいたしません"],
    ["解約", "継続支援は最低3か月。その後は前月末までにメールでご連絡いただくと、翌月末で終了します"],
  ];
  return (
    <div className="mx-auto max-w-3xl space-y-6">
      <h1 className="text-2xl font-bold">特定商取引法に基づく表記</h1>
      <dl className="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
        {rows.map(([k, v]) => (
          <div key={k} className="grid gap-1 px-5 py-4 sm:grid-cols-[12rem_1fr]">
            <dt className="text-sm font-semibold text-slate-700">{k}</dt>
            <dd className="text-sm text-slate-800">{v}</dd>
          </div>
        ))}
      </dl>
    </div>
  );
}
