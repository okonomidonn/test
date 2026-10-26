import Link from "next/link";
import { btn } from "@/components/ai/ds";

export const metadata = { title: "料金 | ススメル" };

// 代行（おまかせ）。法人向け・税別。申し込みは相談から（Stripeの自動課金ではない）
const COLUMNS = [
  { key: "check", title: "無料チェック", price: "0円", unit: "費用はかかりません", term: "—", cta: { label: "無料で診断する", href: "/ai/apply" } },
  { key: "trial", title: "GEO診断・改善トライアル", price: "49,800円", unit: "税別・1回・30日間", term: "1回", cta: { label: "トライアルを申し込む", href: "/ai/apply?plan=trial" }, recommended: "はじめて依頼する方へ" },
  { key: "retainer", title: "継続支援", price: "月98,000円〜", unit: "税別・トライアル後にお見積もり", term: "最低3か月、以降は1か月ごと", cta: { label: "トライアルから始める", href: "/ai/apply?plan=trial" } },
] as const;

type Key = (typeof COLUMNS)[number]["key"];
const ROWS: { label: string; cells: Record<Key, string> }[] = [
  { label: "こんな方に", cells: { check: "まず現状を知りたい", trial: "効果を確かめてから、依頼するか決めたい", retainer: "トライアルのあと、毎月改善を続けたい" } },
  { label: "AIでの計測", cells: { check: "ChatGPTでの計測（10問）", trial: "ChatGPT・Geminiで各10問を計測", retainer: "毎月の計測とレポート" } },
  { label: "競合との比較", cells: { check: "—", trial: "競合2社との比較（紹介された回数・引用されたページ）", retainer: "—" } },
  { label: "サイトの診断", cells: { check: "公式サイトの自動診断", trial: "直す点を3つ特定", retainer: "構造化データの保守" } },
  { label: "改善の作業", cells: { check: "—", trial: "既存ページ1ページを改善", retainer: "ページの作成・書き換え（月4ページ〜）" } },
  { label: "報告", cells: { check: "結果の共有", trial: "30日後に再計測して報告", retainer: "月1回の打ち合わせ" } },
  { label: "契約期間", cells: { check: "—", trial: "1回", retainer: "最低3か月、以降は1か月ごと" } },
  { label: "総額の目安", cells: { check: "0円", trial: "49,800円（税別）", retainer: "最低3か月で294,000円〜（税別）" } },
];

export default function PricingPage() {
  return (
    <div className="space-y-14 pt-4">
      <header className="max-w-3xl">
        <h1 className="text-[2rem] font-bold text-slate-900 sm:text-[2.5rem]">料金</h1>
        <p className="mt-3 text-lg text-slate-600">まず無料チェック、次に有料のトライアルで効果を確かめ、続けるかを決められます。</p>
      </header>

      <section id="managed" className="scroll-mt-24 space-y-5">
        <div>
          <h2 className="text-2xl font-bold text-slate-900">計測から改善まで</h2>
          <p className="mt-1 text-slate-600">法人向け・税別です。</p>
        </div>

        {/* PC：比較表 */}
        <div className="hidden overflow-hidden rounded-xl border border-slate-200 md:block">
          <table className="w-full table-fixed text-left">
            <thead>
              <tr className="border-b border-slate-200">
                <th className="w-40 bg-slate-50 p-4 align-bottom text-sm font-semibold text-slate-600">
                  <span className="sr-only">項目</span>
                </th>
                {COLUMNS.map((c) => (
                  <th key={c.key} className={`p-4 align-top ${"recommended" in c ? "bg-brand-soft" : ""}`}>
                    <span className={`block text-sm font-semibold ${"recommended" in c ? "text-brand" : "text-transparent"}`}>{"recommended" in c ? c.recommended : "—"}</span>
                    <span className="mt-1 block text-base font-bold text-slate-900">{c.title}</span>
                    <span className="mt-2 block font-num text-2xl font-semibold text-slate-900">{c.price}</span>
                    <span className="block text-sm font-normal text-slate-600">{c.unit || " "}</span>
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-200">
              {ROWS.map((r) => (
                <tr key={r.label}>
                  <th scope="row" className="bg-slate-50 p-4 text-sm font-semibold text-slate-700">
                    {r.label}
                  </th>
                  {COLUMNS.map((c) => (
                    <td key={c.key} className={`p-4 text-sm ${r.cells[c.key] === "—" ? "text-slate-400" : "text-slate-900"} ${"recommended" in c ? "bg-brand-soft/60" : ""}`}>
                      {r.cells[c.key]}
                    </td>
                  ))}
                </tr>
              ))}
              <tr>
                <td className="bg-slate-50 p-4" />
                {COLUMNS.map((c) => (
                  <td key={c.key} className={`p-4 ${"recommended" in c ? "bg-brand-soft/60" : ""}`}>
                    <Link href={c.cta.href} className={`${btn("recommended" in c ? "primary" : "secondary")} w-full`}>
                      {c.cta.label}
                    </Link>
                  </td>
                ))}
              </tr>
            </tbody>
          </table>
        </div>

        {/* スマホ：プランごとに縦に */}
        <div className="space-y-4 md:hidden">
          {COLUMNS.map((c) => (
            <section key={c.key} className={`rounded-xl border p-4 ${"recommended" in c ? "border-brand" : "border-slate-200"}`}>
              {"recommended" in c && <p className="text-sm font-semibold text-brand">{c.recommended}</p>}
              <h3 className="text-base font-bold text-slate-900">{c.title}</h3>
              <p className="mt-1 font-num text-2xl font-semibold text-slate-900">
                {c.price}
                {c.unit && <span className="ml-1 text-sm font-normal text-slate-600">{c.unit}</span>}
              </p>
              <dl className="mt-3 divide-y divide-slate-100 border-t border-slate-200 text-sm">
                {ROWS.filter((r) => r.cells[c.key] !== "—").map((r) => (
                  <div key={r.label} className="grid grid-cols-[6.5rem_1fr] gap-3 py-2">
                    <dt className="font-semibold text-slate-600">{r.label}</dt>
                    <dd className="text-slate-900">{r.cells[c.key]}</dd>
                  </div>
                ))}
              </dl>
              <Link href={c.cta.href} className={`${btn("recommended" in c ? "primary" : "secondary")} mt-3 w-full`}>
                {c.cta.label}
              </Link>
            </section>
          ))}
        </div>
        <p className="text-sm text-slate-600">トライアルに含まないもの：新しいページの制作、外部媒体への掲載、大規模な実装。</p>
      </section>

      <ul className="max-w-3xl list-disc space-y-1 border-t border-slate-200 pt-6 pl-5 text-sm text-slate-600">
        <li>AIの回答は毎回変わります。計測結果は目安で、紹介や問い合わせの増加をお約束するものではありません。</li>
        <li>
          <Link href="/ai/legal" className="underline">
            特定商取引法に基づく表記
          </Link>
        </li>
      </ul>
    </div>
  );
}
