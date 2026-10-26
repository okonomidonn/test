import Link from "next/link";
import { sampleReport } from "@/lib/ai-check/sample";
import { AuditItem, PlanList, QuestionsTable, RankingTable, ReportSummaryHero } from "@/components/ai/report-dashboard";
import { MarketCompare, MethodSteps } from "@/components/ai/marketing";
import { SectionHead, btn } from "@/components/ai/ds";

const STEPS = [
  { title: "無料チェック", price: "0円", term: "3営業日以内にメールで", body: "主な質問でAIに紹介されているかを測り、直すところの上位3つをお送りします。" },
  { title: "GEO診断・改善トライアル", price: "49,800円", term: "税別・1回・30日間", body: "2つのAIで計測し、直す点を3つ決めて1ページを実際に直し、30日後に測り直します。" },
  { title: "継続支援", price: "月98,000円〜", term: "税別・最低3か月", body: "毎月計測し、紹介されなかった質問に答えるページを作ります。" },
];

export default function AiLandingPage() {
  const d = sampleReport();
  const pitch = d.checks.find((c) => c.key === "pitch");
  const plan = d.plans.find((p) => p.type === "条件に答えるページ");

  return (
    <div className="pb-8">
      {/* ファーストビュー：左にコピー、右に結果の見本（大きく、要点だけ） */}
      <section className="grid items-center gap-10 pt-6 sm:pt-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-14">
        <div className="min-w-0">
          <p className="text-sm font-semibold text-brand">ChatGPT・Geminiでの競合比較とサイト改善</p>
          <h1 className="mt-4 text-[2.125rem] font-bold leading-[1.25] tracking-tight text-slate-900 sm:text-[2.75rem] sm:leading-[1.2] lg:text-[2.25rem]">
            あなたのサービスは、
            <br />
            AIの比較候補に入っていますか？
          </h1>
          <p className="mt-6 text-lg text-slate-600">ChatGPT・Geminiでの紹介状況を調べ、競合との差と改善すべきページを明らかにします。</p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Link href="/ai/apply" className={btn("primary", "lg")}>
              無料で診断する
            </Link>
            <Link href="/ai/sample" className={btn("secondary", "lg")}>
              サンプルレポートを見る
            </Link>
          </div>
          <p className="mt-3 text-sm text-slate-600">登録は不要です。3営業日以内に、結果をメールでお送りします。</p>
        </div>
        <div className="min-w-0">
          <p className="mb-2 flex items-center justify-between text-sm text-slate-600">
            <span className="font-semibold text-slate-900">お届けする結果の例</span>
            <span className="rounded-md border border-slate-300 px-2 py-0.5 text-xs font-semibold text-slate-700">デモ・架空の求人サイト</span>
          </p>
          <ReportSummaryHero d={d} linkDetails={false} />
          <Link href="/ai/sample" className="mt-3 inline-block text-sm font-semibold text-brand hover:underline">
            レポートの全体を見る →
          </Link>
        </div>
      </section>

      {/* 1. 何が分かるか */}
      <section id="features" className="mt-24 scroll-mt-20">
        <SectionHead label="1. 何が分かるか" title="どの質問で、どの競合に負けているか。" />
        <p className="mt-3 max-w-2xl text-slate-600">見込み客が聞きそうな質問ごとに、自社の順位と、代わりに紹介されたサービスを記録します。</p>
        <div className="mt-8 grid gap-5 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
          <div className="min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
            <QuestionsTable d={d} limit={4} />
          </div>
          <div className="min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
            <p className="mb-3 font-semibold text-slate-900">比較ランキング</p>
            <RankingTable d={d} />
          </div>
        </div>
      </section>

      {/* 2. 何を直すか */}
      <section className="mt-20">
        <SectionHead label="2. 何を直すか" title="直す場所と書き換え例まで。" />
        <p className="mt-3 max-w-2xl text-slate-600">事実を確かめる必要があるものは「要確認」、効果が確かでないものは「仮説」と分けてお伝えします。</p>
        <div className="mt-8 grid gap-5 lg:grid-cols-2">
          {pitch && (
            <div className="min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
              <ul>
                <AuditItem c={pitch} />
              </ul>
            </div>
          )}
          {plan && (
            <div className="min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
              <PlanList plans={[plan]} />
            </div>
          )}
        </div>
      </section>

      {/* 3. なぜ信用できるか */}
      <section className="mt-20">
        <SectionHead label="3. なぜ信用できるか" title="測り方と限界を、先にお見せします。" />
        <div className="mt-8">
          <MethodSteps />
        </div>
        <div className="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
          <dl className="grid overflow-hidden rounded-xl border border-slate-200 sm:grid-cols-2">
            {[
              ["14.5%", "ユーザーのうちChatGPT経由", "469人中68人"],
              ["51.6%", "キーイベントのうちChatGPT経由", "95件中49件"],
            ].map(([v, k, n], i) => (
              <div key={k} className={`p-5 ${i ? "border-t border-slate-200 sm:border-l sm:border-t-0" : ""}`}>
                <dd className="font-num text-[2rem] font-semibold leading-tight text-slate-900">{v}</dd>
                <dt className="mt-1 font-medium text-slate-800">{k}</dt>
                <p className="text-sm text-slate-600">{n}</p>
              </div>
            ))}
          </dl>
          <div className="min-w-0 text-sm text-slate-700">
            <p className="font-semibold text-slate-900">運営元の求人サイト「zaito」の28日間（GA4、2026年9月24日〜10月21日）</p>
            <p className="mt-2">キーイベントは「興味あり」「応募ページへ進む」などの操作の件数で、応募の完了率ではありません。サイト1件の数字で、対策と流入増の因果関係は検証していません。</p>
            <Link href="/ai/method" className="mt-3 inline-block font-semibold text-brand hover:underline">
              計測のしかたと限界を見る →
            </Link>
          </div>
        </div>
      </section>

      {/* 4. いくらで試せるか */}
      <section className="mt-20">
        <SectionHead label="4. いくらで試せるか" title="まず無料で。次に49,800円で1ページ直して確かめる。" />
        <ol className="mt-8 grid overflow-hidden rounded-xl border border-slate-200 md:grid-cols-3">
          {STEPS.map((s, i) => (
            <li key={s.title} className={`p-5 ${i ? "border-t border-slate-200 md:border-l md:border-t-0" : ""} ${i === 1 ? "bg-brand-soft" : ""}`}>
              <p className="font-num text-sm font-semibold text-slate-500">STEP {i + 1}</p>
              <h3 className="mt-1 font-bold text-slate-900">{s.title}</h3>
              <p className="mt-2 font-num text-2xl font-semibold text-slate-900">{s.price}</p>
              <p className="text-sm text-slate-600">{s.term}</p>
              <p className="mt-3 text-slate-700">{s.body}</p>
            </li>
          ))}
        </ol>
        <div className="mt-8">
          <MarketCompare />
        </div>
        <div className="mt-8 flex flex-wrap gap-3">
          <Link href="/ai/apply" className={btn("primary", "lg")}>
            無料で診断する
          </Link>
          <Link href="/ai/pricing" className={btn("secondary", "lg")}>
            料金の詳細
          </Link>
        </div>
        <p className="mt-3 text-sm text-slate-600">AIの回答は毎回変わるため、結果は目安です。紹介や問い合わせの増加をお約束するものではありません。</p>
      </section>
    </div>
  );
}
