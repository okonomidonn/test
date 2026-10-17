export const metadata = { title: "特定商取引法に基づく表記 | AI検索チェック" };

// 販売事業者の情報は環境変数で設定する（未設定の項目は「請求があれば遅滞なく開示します」と表示）
const DISCLOSE = "請求があった場合は遅滞なく開示します";

export default function LegalPage() {
  const env = process.env;
  const rows: [string, string][] = [
    ["販売事業者", env.LEGAL_SELLER_NAME || "（未設定）"],
    ["運営責任者", env.LEGAL_REPRESENTATIVE || "（未設定）"],
    ["所在地", env.LEGAL_ADDRESS || DISCLOSE],
    ["電話番号", env.LEGAL_PHONE || DISCLOSE],
    ["メールアドレス", env.LEGAL_EMAIL || "（未設定）"],
    ["販売価格", "料金プランのページに記載（表示価格は税込）"],
    ["商品代金以外の必要料金", "インターネット接続にかかる通信料"],
    ["支払方法", "クレジットカード（Stripe）"],
    ["支払時期", "お申し込み時に初月分、以降は毎月の更新日に自動で請求されます"],
    ["サービスの提供時期", "お支払い手続き完了後、直ちにご利用いただけます"],
    ["解約・返品について", "サービスの性質上、お支払い後の返金はいたしません。解約は契約管理画面からいつでも行え、その請求期間の終わりまでご利用いただけます"],
    ["動作環境", "最新のブラウザ（Chrome、Safari、Edge など）"],
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
