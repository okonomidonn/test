import Link from "next/link";
import { sampleReport } from "@/lib/ai-check/sample";
import { AuditItem, QuestionsTable, RankingTable, ReportDashboard, REPORT_SECTIONS, SourcesTable, todoOf } from "@/components/ai/report-dashboard";
import { AppWindow, SectionHead, btn } from "@/components/ai/ds";

const STEPS = [
  { title: "無料チェック", price: "0円", term: "3営業日以内にメールで", body: "主な質問でAIに紹介されているかを測り、直すところの上位3つをお送りします。" },
  { title: "GEO診断・改善トライアル", price: "49,800円", term: "1回・30日間", body: "2つのAIで計測、直す点を3つ決めて1ページを実際に直し、30日後に測り直します。" },
  { title: "継続支援", price: "月98,000円〜", term: "最低3か月", body: "毎月計測し、紹介されなかった質問に答えるページを作ります。" },
];

// 機能の1行：左に短い説明、右に報告書の実物
function FeatureRow({ no, title, body, children }: { no: string; title: string; body: string; children: React.ReactNode }) {
  return (
    <div className="grid gap-6 border-t border-slate-200 py-10 lg:grid-cols-[minmax(0,20rem)_minmax(0,1fr)] lg:gap-16">
      <div className="min-w-0">
        <p className="font-num text-sm font-semibold text-slate-500">{no}</p>
        <h3 className="mt-2 text-xl font-bold text-slate-900">{title}</h3>
        <p className="mt-2 text-slate-600">{body}</p>
      </div>
      <div className="min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">{children}</div>
    </div>
  );
}

export default function AiLandingPage() {
  const d = sampleReport();
  const pitch = d.checks.find((c) => c.key === "pitch");
  const total = d.measured?.total ?? 10;
  const self = d.ranking.find((r) => r.self)!;
  const rival = [...d.ranking].filter((r) => !r.self).sort((a, b) => b.hits - a.hits)[0];
  const lost = d.questions.filter((q) => q.hits === 0 && q.others.includes(rival.label));
  const topSource = d.sources.find((x) => x.kind !== "official");
  const first = todoOf(d.checks)[0];
  const story = [
    {
      step: "1. 差が分かる",
      head: `「${rival.label}」は${total}問中${rival.hits}問で紹介。自社は${self.hits}問。`,
      body: `紹介されなかった質問のうち${lost.length}問で、代わりに「${rival.label}」が紹介されていました。`,
    },
    {
      step: "2. 理由が分かる",
      head: `AIが根拠にしたのは「${topSource?.domain}」などの比較記事。`,
      body: "競合が引用されているページの種類と、自社サイトに足りない情報（例：「週3日から」の記載がない）が分かります。",
    },
    {
      step: "3. 次にやることが決まる",
      head: `まず「${first.title}」。`,
      body: "直す場所・書き換え例・事前に確かめることを渡します。直したあと、同じ条件で測り直します。",
    },
  ];

  return (
    <div className="pb-8">
      {/* ファーストビュー：左揃えの見出しとCTA */}
      <section className="max-w-3xl pt-8 sm:pt-14">
        <p className="text-sm font-semibold text-brand">ChatGPT・Geminiでの競合比較とサイト改善</p>
        <h1 className="mt-4 text-[2.125rem] font-bold leading-[1.25] tracking-tight text-slate-900 sm:text-[3rem] sm:leading-[1.2]">
          あなたのサービスは、
          <br />
          <span>AIの比較候補に入っていますか？</span>
        </h1>
        <p className="mt-6 max-w-2xl text-lg text-slate-600">ChatGPT・Geminiでの紹介状況を調べ、競合との差と改善すべきページを明らかにします。</p>
        <div className="mt-8 flex flex-wrap gap-3">
          <Link href="/ai/apply" className={btn("primary", "lg")}>
            無料で診断する
          </Link>
          <Link href="/ai/sample" className={btn("secondary", "lg")}>
            サンプルレポートを見る
          </Link>
        </div>
        <p className="mt-3 text-sm text-slate-600">登録は不要です。3営業日以内に、結果をメールでお送りします。</p>
      </section>

      {/* 製品画面：実際の報告書（デモ） */}
      <section className="mt-12 sm:mt-16" aria-label="報告書の画面（デモ）">
        <AppWindow title="ススメル — AI検索の診断報告書" badge="デモ・架空のサービス">
          <div className="grid lg:grid-cols-[12rem_minmax(0,1fr)]">
            <aside className="hidden border-r border-slate-200 bg-slate-50 p-4 lg:block">
              <p className="text-sm font-semibold text-slate-900">リモ求人ナビ</p>
              <nav className="mt-4 space-y-0.5 text-sm" aria-hidden>
                {REPORT_SECTIONS.map((s, i) => (
                  <span key={s.id} className={`block rounded-md px-2 py-1.5 ${i === 0 ? "bg-white font-semibold text-slate-900 ring-1 ring-slate-200" : "text-slate-600"}`}>
                    {s.label}
                  </span>
                ))}
              </nav>
            </aside>
            <div className="min-w-0 p-4 sm:p-6">
              <ReportDashboard d={d} compact />
            </div>
          </div>
        </AppWindow>
        <p className="mt-3 text-sm text-slate-600">
          画面はデモです（架空の求人サイト）。
          <Link href="/ai/sample" className="font-semibold text-brand hover:underline">
            全項目を見る →
          </Link>
        </p>
      </section>

      {/* 問題 → 理由 → 行動：買ったあとに何が分かり、何をするか（デモの数字） */}
      <section className="mt-24">
        <SectionHead label="診断で分かること" title="差が分かり、理由が分かり、次にやることが決まる。" />
        <ol className="mt-8 grid overflow-hidden rounded-xl border border-slate-200 lg:grid-cols-3">
          {story.map((st, i) => (
            <li key={st.step} className={`min-w-0 p-5 sm:p-6 ${i ? "border-t border-slate-200 lg:border-l lg:border-t-0" : ""}`}>
              <p className="text-sm font-semibold text-brand">{st.step}</p>
              <p className="mt-2 text-lg font-bold leading-snug text-slate-900">{st.head}</p>
              <p className="mt-2 text-slate-700">{st.body}</p>
            </li>
          ))}
        </ol>
        <p className="mt-3 text-sm text-slate-600">数字はデモ（架空の求人サイト）です。紹介や問い合わせの増加をお約束するものではありません。</p>
      </section>

      {/* できること：説明は短く、報告書の実物で見せる */}
      <section id="features" className="mt-24 scroll-mt-20">
        <SectionHead label="報告書でわかること" title="数字で終わらせない。" sub="何を直すかまで渡します。" />
        <div className="mt-10">
          <FeatureRow no="01" title="どの質問で、何番目に紹介されたか" body="見込み客が聞きそうな質問ごとに、自社の順位と、代わりに紹介されたサービスを記録します。">
            <QuestionsTable d={d} limit={4} />
          </FeatureRow>
          <FeatureRow no="02" title="どの競合に、なぜ負けているか" body="回答に出た回数の比較と、AIが根拠にしたサイトの一覧。競合が引用されている種類のページが分かります。">
            <div className="grid gap-6 md:grid-cols-2">
              <RankingTable d={d} />
              <SourcesTable d={d} />
            </div>
          </FeatureRow>
          {pitch && (
            <FeatureRow no="03" title="サイトのどこを、どう直すか" body="直す場所・今の内容・そのまま使える書き換え例を出します。構造化データのコードも渡します。">
              <ul>
                <AuditItem c={pitch} />
              </ul>
            </FeatureRow>
          )}
        </div>
      </section>

      {/* 事例：数字の帯と、言えないこと */}
      <section className="mt-16 border-t border-slate-200 pt-10">
        <SectionHead label="事例" title="ChatGPT経由の人が、キーイベントの半分。" sub="運営元の求人サイト「zaito」の28日間です。" />
        <dl className="mt-8 grid overflow-hidden rounded-xl border border-slate-200 sm:grid-cols-3">
          {[
            ["14.5%", "ユーザーのうちChatGPT経由", "469人中68人"],
            ["51.6%", "キーイベントのうちChatGPT経由", "95件中49件"],
            ["46種類", "ChatGPT経由の人が見た求人ページ", "個別の求人ページが中心"],
          ].map(([v, k, n], i) => (
            <div key={k} className={`p-5 ${i ? "border-t border-slate-200 sm:border-l sm:border-t-0" : ""}`}>
              <dd className="font-num text-[2rem] font-semibold leading-tight text-slate-900">{v}</dd>
              <dt className="mt-1 font-medium text-slate-800">{k}</dt>
              <p className="text-sm text-slate-600">{n}</p>
            </div>
          ))}
        </dl>
        <p className="mt-4 max-w-3xl text-sm text-slate-600">
          2026年9月24日〜10月21日、GA4。キーイベントは「興味あり」「応募ページへ進む」などの操作の件数で、同じ人の複数回を含み、応募の完了率ではありません。サイト1件・28日間の数字で、対策と流入増の因果関係は検証していません。
        </p>
      </section>

      {/* 進め方と料金：罫線の表 */}
      <section className="mt-16 border-t border-slate-200 pt-10">
        <div className="flex flex-wrap items-end justify-between gap-3">
          <SectionHead label="進め方と料金" title="まず無料で、効果を見てから。" />
          <Link href="/ai/pricing" className="font-semibold text-brand hover:underline">
            料金の詳細 →
          </Link>
        </div>
        <ol className="mt-8 grid overflow-hidden rounded-xl border border-slate-200 md:grid-cols-3">
          {STEPS.map((s, i) => (
            <li key={s.title} className={`p-5 ${i ? "border-t border-slate-200 md:border-l md:border-t-0" : ""}`}>
              <p className="font-num text-sm font-semibold text-slate-500">STEP {i + 1}</p>
              <h3 className="mt-1 font-bold text-slate-900">{s.title}</h3>
              <p className="mt-2 font-num text-2xl font-semibold text-slate-900">{s.price}</p>
              <p className="text-sm text-slate-600">{s.term}</p>
              <p className="mt-3 text-slate-700">{s.body}</p>
            </li>
          ))}
        </ol>
        <p className="mt-3 text-sm text-slate-600">表示はすべて税別です。</p>
      </section>

      <section className="mt-16 flex flex-col gap-6 border-t border-slate-200 pt-10 sm:flex-row sm:items-end sm:justify-between">
        <div className="max-w-2xl">
          <h2 className="text-[1.625rem] font-bold text-slate-900 sm:text-[2rem]">
            まずは無料で診断。<span>会社名とURLだけで申し込めます。</span>
          </h2>
          <p className="mt-3 text-sm text-slate-600">AIの回答は毎回変わるため、結果は目安です。紹介や問い合わせの増加をお約束するものではありません。</p>
        </div>
        <div className="flex flex-wrap gap-3">
          <Link href="/ai/apply" className={btn("primary", "lg")}>
            無料で診断する
          </Link>
          <Link href="/ai/sample" className={btn("secondary", "lg")}>
            サンプルレポート
          </Link>
        </div>
      </section>
    </div>
  );
}
