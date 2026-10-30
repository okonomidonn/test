import Link from "next/link";
import { CONTACT } from "@/lib/contact";

export const metadata = { title: "特定商取引法に基づく表記 | AIススメル" };

// 運営責任者・所在地・電話は環境変数で設定する（未設定なら請求時に開示）
const DISCLOSE = "請求があった場合は遅滞なく開示します";

export default function LegalPage() {
  const env = process.env;
  const rows: [string, string][] = [
    ["販売事業者", CONTACT.seller],
    ["運営責任者", CONTACT.representative],
    ["所在地", env.LEGAL_ADDRESS || DISCLOSE],
    ["電話番号", env.LEGAL_PHONE || DISCLOSE],
    ["メールアドレス", CONTACT.email],
    ["販売価格", "無料チェック：0円。トライアル：49,800円（税別）／54,780円（税込）、1回・30日間。継続支援：月98,000円〜（税別）／月107,800円〜（税込）"],
    ["契約期間・総額", "継続支援は最低3か月。基本料金での最低総額は294,000円（税別）／323,400円（税込）。個別の作業範囲と総額は見積書で確認します"],
    ["商品代金以外の必要料金", env.LEGAL_ADDITIONAL_FEES || "インターネット接続にかかる通信料。その他の費用が必要な場合は、正式なご依頼前に見積書でご案内します"],
    ["支払方法", env.LEGAL_PAYMENT_METHODS || "銀行振込（請求書払い）。振込手数料はお客様のご負担となります"],
    ["支払時期", env.LEGAL_PAYMENT_TIMING || "前払い。トライアルは請求書の発行から7日以内、継続支援は翌月分を毎月末までにお支払いください。ご入金を確認してから作業を始めます"],
    ["サービスの提供時期", "トライアルは正式なご依頼後、ヒアリングで開始日を合意します。無料チェックは3営業日以内に診断結果、トライアルのご相談は2営業日以内に進め方とお見積もりをご連絡します"],
    ["解約・キャンセル", env.LEGAL_CANCELLATION_TERMS || "作業を始める前のキャンセルは全額を返金します。作業を始めた後の返金はいたしません。継続支援は最低3か月で、その後は前月末までにメールでご連絡いただくと翌月末で終了します"],
    ["成果について", "AIでの紹介や問い合わせの増加を保証するものではありません。作業範囲・納品物を契約前に確認します"],
  ];
  return (
    <div className="mx-auto max-w-3xl space-y-6">
      <h1 className="text-2xl font-bold">特定商取引法に基づく表記</h1>
      <p className="text-slate-700">現在の申し込みフォームは無料診断とお見積もりの受付です。フォーム送信だけで有料契約や自動課金は発生しません。</p>
      <h2 className="text-xl font-bold">GEO診断・改善の代行サービス</h2>
      <dl className="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
        {rows.map(([k, v]) => (
          <div key={k} className="grid gap-1 px-5 py-4 sm:grid-cols-[12rem_1fr]">
            <dt className="text-sm font-semibold text-slate-700">{k}</dt>
            <dd className="text-sm text-slate-800">{v}</dd>
          </div>
        ))}
      </dl>
      <Link href="/ai/apply" className="inline-block font-semibold text-brand underline">お問い合わせフォーム</Link>
      <section className="space-y-3 border-t border-slate-200 pt-6">
        <h2 className="text-xl font-bold">既存アカウントのセルフ版契約</h2>
        <p className="text-sm text-slate-600">新規販売は終了しています。次の内容は既存のセルフ版契約にだけ適用され、上の代行サービスとは異なります。</p>
        <ul className="list-disc space-y-2 pl-5 text-sm text-slate-800">
          <li>フリー：0円、スターター：月4,980円、プロ：月14,800円（いずれも税込）。</li>
          <li>クレジットカード（Stripe）。初月分はお申し込み時、以降は毎月の更新日に自動請求します。</li>
          <li>お支払い手続き完了後、利用可能になります。</li>
          <li>解約は契約管理画面から行え、その請求期間の終わりまで利用できます。サービスの性質上、お支払い後の返金は行いません。</li>
          <li>動作環境：最新のブラウザ（Chrome、Safari、Edgeなど）。</li>
        </ul>
      </section>
    </div>
  );
}
