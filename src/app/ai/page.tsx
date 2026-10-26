import Link from "next/link";
import { sampleReport } from "@/lib/ai-check/sample";
import { todoOf, OthersAndSources, QuestionTable } from "@/components/ai/report-view";
import { FixList, PagePlans } from "@/components/ai/fix-list";

const STEPS = [
  { title: "無料チェック", price: "0円", body: "主な質問でAIに紹介されているかを測り、直すところの上位3つをお送りします。" },
  { title: "GEO診断・改善トライアル", price: "49,800円", body: "2つのAIで計測、直す点を3つ決めて1ページを実際に直し、30日後に測り直します。" },
  { title: "継続支援", price: "月98,000円〜", body: "毎月計測し、紹介されなかった質問に答えるページを作ります。" },
];

// 冒頭に置く、報告書の縮小版（架空のサービスのサンプル）
function ReportPreview() {
  const d = sampleReport();
  const todo = todoOf(d.checks).slice(0, 3);
  const rate = d.measured ? Math.round((d.measured.hits / d.measured.total) * 100) : 0;
  return (
    <figure className="min-w-0 rounded-2xl border border-slate-300 bg-white" aria-label="診断報告書のサンプル">
      <div className="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-3">
        <span className="text-sm font-semibold text-brand">AI検索の診断報告書</span>
        <span className="shrink-0 rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">サンプル（架空）</span>
      </div>
      <div className="space-y-5 px-5 py-5">
        <p className="font-display text-xl font-black text-slate-900">{d.name}</p>
        <dl className="grid grid-cols-3 gap-3 border-y border-slate-200 py-4">
          <div>
            <dt className="text-xs font-semibold text-slate-600">紹介された割合</dt>
            <dd className="font-num text-3xl font-semibold text-slate-900">
              {rate}
              <span className="text-lg text-slate-500">%</span>
            </dd>
          </div>
          <div>
            <dt className="text-xs font-semibold text-slate-600">サイト診断</dt>
            <dd className="font-num text-3xl font-semibold text-slate-900">
              {d.score}
              <span className="text-lg text-slate-500">点</span>
            </dd>
          </div>
          <div>
            <dt className="text-xs font-semibold text-slate-600">負けている競合</dt>
            <dd className="font-num text-3xl font-semibold text-slate-900">{d.others.length}</dd>
          </div>
        </dl>
        <div>
          <p className="text-sm font-bold text-slate-900">直すところ（上から順に）</p>
          <ol className="mt-2 space-y-2">
            {todo.map((c, i) => (
              <li key={c.key} className="grid grid-cols-[1.5rem_1fr] gap-1 text-sm">
                <span className="font-num font-semibold text-slate-500">{i + 1}</span>
                <span className="min-w-0">
                  <span className="font-semibold text-slate-900">{c.title}</span>
                  {c.where && <span className="block truncate text-slate-600">{c.where}</span>}
                </span>
              </li>
            ))}
          </ol>
        </div>
      </div>
      <figcaption className="border-t border-slate-200 bg-slate-50 px-5 py-3 text-sm">
        <Link href="/ai/sample" className="font-semibold text-brand hover:underline">
          サンプルの報告書を全部見る →
        </Link>
      </figcaption>
    </figure>
  );
}

// 報告書の各部分を、実物のまま並べて説明する
function Feature({ no, title, body, children }: { no: number; title: string; body: string; children: React.ReactNode }) {
  return (
    <div className="grid gap-6 border-t border-slate-300 pt-8 lg:grid-cols-[minmax(0,22rem)_1fr] lg:gap-12">
      <div className="min-w-0">
        <span className="grid h-9 w-9 place-items-center rounded-full bg-slate-900 font-num font-semibold text-white">{no}</span>
        <h3 className="mt-3 text-xl font-bold text-slate-900">{title}</h3>
        <p className="mt-2 text-slate-700">{body}</p>
      </div>
      <div className="min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5">{children}</div>
    </div>
  );
}

export default function AiLandingPage() {
  const d = sampleReport();
  const fix = d.checks.find((c) => c.key === "pitch");
  const plan = d.plans.find((p) => p.type === "条件に答えるページ");

  return (
    <div className="space-y-24 pb-12">
      <section className="grid items-center gap-12 pt-6 lg:grid-cols-[1fr_1fr]">
        <div className="min-w-0">
          <p className="font-semibold text-brand">ススメル｜WebサービスのAI検索診断と改善</p>
          <h1 className="mt-4 text-[2rem] font-black leading-[1.3] text-slate-900 sm:text-[2.75rem] sm:leading-[1.25]">
            あなたのサービスは、
            <br />
            AIの<span className="marker">比較候補</span>に
            <br />
            入っていますか？
          </h1>
          <p className="mt-6 max-w-[34rem] text-slate-700">
            見込み客がChatGPT・Geminiに聞きそうな質問で、御社が紹介されるか、どの競合に負けているか、サイトのどこを直せばいいかを調べ、報告書にしてお渡しします。
          </p>
          <div className="mt-8 flex flex-wrap items-center gap-3">
            <Link href="/ai/apply" className="rounded-lg bg-brand px-6 py-3.5 text-lg font-bold text-white hover:bg-brand-dark">
              無料チェックを申し込む
            </Link>
            <Link href="/ai/sample" className="rounded-lg border border-slate-300 bg-white px-5 py-3.5 text-lg font-semibold text-slate-800 hover:bg-slate-50">
              サンプルの報告書を見る
            </Link>
          </div>
          <p className="mt-3 text-sm text-slate-600">登録は不要です。3営業日以内に、結果をメールでお送りします。</p>
        </div>
        <ReportPreview />
      </section>

      <section className="space-y-10">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 sm:text-3xl">報告書でわかること</h2>
          <p className="mt-2 text-slate-700">下の表やコードは、サンプルの報告書の一部をそのまま載せたものです（架空のサービス）。</p>
        </div>
        <Feature no={1} title="どの質問で紹介されたか" body="見込み客が聞きそうな質問ごとに、紹介されたか、何番目かを記録します。紹介されなかった質問が、直すべき場所の手がかりになります。">
          <QuestionTable d={{ ...d, questions: d.questions.slice(0, 5) }} />
        </Feature>
        <Feature no={2} title="どの競合に負けているか" body="代わりに紹介されたサービスと、AIが根拠にしたサイト（比較記事・レビューなど）を並べます。">
          <OthersAndSources d={d} />
        </Feature>
        {fix && (
          <Feature no={3} title="サイトのどこを、どう直すか" body="直す場所、今の内容、そのまま使える書き換え例を出します。構造化データのコードや、トップに書く一文もそのまま渡します。">
            <FixList checks={[fix]} />
          </Feature>
        )}
        {plan && (
          <Feature no={4} title="どんなページを作ればいいか" body="紹介されなかった質問ごとに、作るページの題名・最初の一文・入れる内容を出します。">
            <PagePlans plans={[plan]} />
          </Feature>
        )}
      </section>

      <section className="grid gap-8 border-t-2 border-slate-900 pt-8 lg:grid-cols-[1fr_1fr] lg:gap-12">
        <div className="min-w-0">
          <p className="text-sm font-semibold text-slate-600">事例｜運営元の求人サイト「zaito」・2026年9月24日〜10月21日（GA4）</p>
          <h2 className="mt-2 text-2xl font-bold text-slate-900">全ユーザーの14.5%を占めるChatGPT経由の人が、キーイベントの51.6%を発生させました</h2>
          <p className="mt-4 text-slate-700">
            Googleではほとんど上位に出ない開設2か月のサイトです。求人を1件1ページにし、一文の説明と構造化データを整えたあと、ChatGPTからの訪問が始まりました。ChatGPT経由の人が見たのは、主に個別の求人ページでした。
          </p>
        </div>
        <dl className="min-w-0 divide-y divide-slate-200 border-y border-slate-200 text-sm">
          {[
            ["ユーザー", "469人中 68人がChatGPT経由（14.5%）"],
            ["キーイベント", "95件中 49件がChatGPT経由（51.6%）"],
            ["キーイベントとは", "「興味あり」「応募ページへ進む」などの操作の件数。同じ人の複数回を含み、応募の完了率ではありません"],
            ["言えないこと", "サイト1件・28日間の数字です。対策と流入増の因果関係は検証していません"],
          ].map(([k, v]) => (
            <div key={k} className="grid grid-cols-[7rem_1fr] gap-3 py-3">
              <dt className="font-semibold text-slate-600">{k}</dt>
              <dd className="text-slate-800">{v}</dd>
            </div>
          ))}
        </dl>
      </section>

      <section className="space-y-8">
        <div className="flex flex-wrap items-end justify-between gap-3">
          <h2 className="text-2xl font-bold text-slate-900 sm:text-3xl">進め方と料金</h2>
          <Link href="/ai/pricing" className="font-semibold text-brand hover:underline">
            料金の詳細 →
          </Link>
        </div>
        <ol className="grid gap-6 md:grid-cols-3">
          {STEPS.map((s, i) => (
            <li key={s.title} className="min-w-0">
              <div className="flex items-center gap-3">
                <span className="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-900 font-num text-lg font-semibold text-white">{i + 1}</span>
                {i < STEPS.length - 1 && <span className="hidden h-px flex-1 bg-slate-300 md:block" aria-hidden />}
              </div>
              <h3 className="mt-4 text-lg font-bold text-slate-900">{s.title}</h3>
              <p className="font-num text-2xl font-semibold text-slate-900">{s.price}</p>
              <p className="mt-1 text-slate-700">{s.body}</p>
            </li>
          ))}
        </ol>
        <p className="text-sm text-slate-600">
          税別。自分で計測したい方は、
          <Link href="/ai/tool" className="underline">
            セルフ版のツール
          </Link>
          もあります。
        </p>
      </section>

      <section className="rounded-2xl bg-slate-900 px-6 py-12 text-white sm:px-12">
        <div className="max-w-2xl">
          <h2 className="text-2xl font-bold sm:text-3xl">
            まずは<span className="text-mark">無料チェック</span>から
          </h2>
          <p className="mt-3 text-slate-300">会社名・サイトのURL・メールアドレスだけで申し込めます。AIの回答は毎回変わるため、結果は目安です。紹介や問い合わせの増加をお約束するものではありません。</p>
          <div className="mt-7 flex flex-wrap gap-3">
            <Link href="/ai/apply" className="rounded-lg bg-mark px-6 py-3.5 text-lg font-bold text-slate-900 hover:brightness-95">
              無料チェックを申し込む
            </Link>
            <Link href="/ai/sample" className="rounded-lg border border-slate-600 px-6 py-3.5 text-lg font-semibold text-white hover:bg-slate-800">
              サンプルの報告書を見る
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
}
