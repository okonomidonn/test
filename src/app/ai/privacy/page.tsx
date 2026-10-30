import { CONTACT } from "@/lib/contact";

export const metadata = { title: "プライバシーポリシー | AIススメル" };

const ITEMS: [string, string][] = [
  ["取得する情報", "お申し込み時の会社名・サービス名、お名前、メールアドレス、サイトのURL、ご相談内容。ログイン利用時のアカウント情報と計測の記録。"],
  ["利用目的", "診断・改善の実施、結果のご連絡、お見積もり・契約に関するご連絡、サービスの改善のために使います。"],
  ["第三者への提供", "法令に基づく場合を除き、ご本人の同意なく第三者に提供しません。診断結果を公開したり、事例として紹介したりする場合は、事前に同意をいただきます。"],
  ["業務の委託", "データの保管（データベース）、サイトの公開、メールの送信、AIへの質問を、外部のサービスに委託して行います。委託先には必要な範囲の情報だけを渡します。"],
  ["サイトの読み取り", "お預かりしたURLは、公開されているページの読み取りにだけ使います。ログインが必要なページは読み取りません。"],
  ["開示・訂正・削除", "ご自身の情報の開示・訂正・削除をご希望の場合は、下記のメールアドレスまでご連絡ください。"],
  ["お問い合わせ窓口", `${CONTACT.seller}（担当：${CONTACT.representative}） ${CONTACT.email}`],
];

export default function PrivacyPage() {
  return (
    <div className="mx-auto max-w-3xl space-y-6">
      <h1 className="text-2xl font-bold text-slate-900">プライバシーポリシー</h1>
      <p className="text-slate-700">{CONTACT.seller}は、お預かりする個人情報を次のとおり取り扱います。</p>
      <dl className="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
        {ITEMS.map(([k, v]) => (
          <div key={k} className="grid gap-1 px-5 py-4 sm:grid-cols-[10rem_1fr]">
            <dt className="text-sm font-semibold text-slate-700">{k}</dt>
            <dd className="text-sm leading-relaxed text-slate-800">{v}</dd>
          </div>
        ))}
      </dl>
      <p className="text-sm text-slate-600">制定：2026年10月30日</p>
    </div>
  );
}
