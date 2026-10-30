import Link from "next/link";
import { ServicePlans } from "@/components/ai/service-plans";
import { HeroLeadForm } from "@/components/ai/hero-lead-form";

const COMPARE = [
  ["目的", "検索結果で上位に表示される", "AIの回答で紹介・引用される"],
  ["相手", "Googleなどの検索エンジン", "ChatGPT・Geminiなどの生成AI"],
  ["見る数字", "順位・クリック数", "紹介された回数・順番・引用元"],
];
const SERVICES = ["見込み客が聞く質問で、AIに紹介されるかを計測", "競合と比べた紹介回数・順番の比較", "AIが引用しているサイトの確認", "公式サイトの診断（足りない情報の洗い出し）", "直すページと書き換え文章のご提案", "30日後の再計測と毎月のレポート"];
const WORRIES = ["ChatGPTで業界名を聞いても、自社が出てこない", "AIが競合ばかりおすすめしている", "AI検索対策が気になるが、何から始めればいいか分からない", "専門の会社に頼むと高く、効果が見えにくい"];
const STRENGTHS = [
  ["自社サイトで実践した方法", "運営する求人サイトで、AIからの流入を測りながら改善してきた方法をそのまま使います。"],
  ["競合と同じ質問で比べる", "同じ質問・同じ条件で自社と競合を測るので、どこで負けているかがはっきりします。"],
  ["直す文章まで具体的に", "「どのページに、何を書くか」まで書き換え例つきでお渡しします。"],
  ["始めやすい価格", "診断は49,800円（税別）から。公開料金の中央値（診断15万円）より手軽に試せます。"],
];
const FAQ = [
  ["どんなサービスが対象ですか？", "求人・人材サービス、スクール、SaaSなど、Webサイトで問い合わせや申し込みを受け付けているサービスが対象です。"],
  ["無料チェックのあと、契約しないといけませんか？", "いいえ。結果を見てからご検討ください。フォーム送信だけで有料契約や自動課金は発生しません。"],
  ["結果はいつ届きますか？", "無料チェックは3営業日以内にメールでお送りします。トライアルのご相談は2営業日以内に進め方とお見積もりをご連絡します。"],
  ["改善すれば、必ずAIに紹介されますか？", "紹介や問い合わせの増加は保証していません。AIの回答は毎回変わるため、同じ条件で測り直して変化を確かめます。"],
  ["GoogleのAIによる概要（AI Overviews）も対象ですか？", "現在の計測対象はChatGPTとGeminiです。AI Overviewsやほかの生成AIは、ご要望に応じて対象に加えるかをご相談します。"],
  ["SEO対策とは何が違いますか？", "SEOは検索結果の順位、AI検索対策はAIの回答で紹介されることが目的です。サイトの情報を整える点は共通しており、SEOにも役立つ改善が中心です。"],
  ["支払い方法は？", "銀行振込（請求書払い・前払い）です。詳しくは特定商取引法に基づく表記をご覧ください。"],
];
const FLOW = [
  ["サイトのURLを送る", "会社・サービス名と、ご連絡先を入力。アカウント登録は不要です。"],
  ["AIススメルが調べる", "AIでの紹介状況と、公開されているサイトの情報を確認します。"],
  ["結果がメールで届く", "3営業日以内に、紹介状況と最初に直す3つのことをお送りします。"],
];

const QUESTION_TYPES = [
  ["悩みの質問", "在宅でできる仕事の探し方は？", "サービス名を出さずに、困りごとを聞く質問。ここで名前が出ると、最初の候補に入れます。"],
  ["比較・おすすめの質問", "未経験OKの求人サイトのおすすめは？", "候補を選ぶための質問。いちばん成果につながりやすく、診断の中心です。"],
  ["サービス名の質問", "〇〇の評判は？", "名前を指定した質問。AIが自社を正しく説明しているかを確かめます。"],
];
const WORRY_NOTES = ["同じ質問で自社と競合を測り、紹介された回数で比べます。", "AIからの流入と紹介状況を数字でまとめ、社内で説明しやすくします。", "最初に直す3つを、優先順位つきでお伝えします。"];
const CYCLE = [
  ["計測", "AIでの紹介状況を数字で見える化"],
  ["分析", "競合との差と原因を明確に"],
  ["改善", "直すページと文章をご提案・実施"],
];

// 画面いっぱいの帯（背景色をページ幅いっぱいに敷く）
function Band({ className = "", children, id }: { className?: string; children: React.ReactNode; id?: string }) {
  return (
    <section id={id} className={`ml-[calc(50%-50vw)] w-screen scroll-mt-16 ${className}`}>
      <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">{children}</div>
    </section>
  );
}

// 英字の小見出し＋中央寄せの見出し。強調したい語は青に
function Head({ en, children, sub, light }: { en: string; children: React.ReactNode; sub?: string; light?: boolean }) {
  return (
    <div className="text-center">
      <p className={`text-sm font-bold ${light ? "text-blue-100" : "text-brand"}`}>{en}</p>
      <h2 className={`mt-3 text-[1.625rem] font-bold leading-snug sm:text-[2.25rem] ${light ? "text-white" : "text-slate-900"}`}>{children}</h2>
      {sub && <p className={`mx-auto mt-4 max-w-2xl leading-relaxed ${light ? "text-blue-50" : "text-slate-700"}`}>{sub}</p>}
    </div>
  );
}

const em = "text-brand";

function Cta() {
  return (
    <div className="mt-10 text-center">
      <Link href="/ai/apply" className="inline-flex min-h-14 w-full max-w-sm items-center justify-center rounded-lg bg-brand px-8 text-lg font-bold text-white shadow-sm transition hover:bg-brand-dark sm:w-auto">
        無料で診断を依頼する
      </Link>
      <p className="mt-3 text-sm text-slate-600">3営業日以内に、結果をメールでお届けします。</p>
    </div>
  );
}

export default function AiLandingPage() {
  return (
    <div className="-mt-8">
      {/* ファーストビュー：左に見出しと画面の見本、右に短い申し込みフォーム */}
      <section className="ml-[calc(50%-50vw)] w-screen border-b border-slate-200 bg-[linear-gradient(180deg,#f3f7ff_0%,#ffffff_100%)]">
        <div className="mx-auto grid max-w-6xl items-center gap-10 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1.1fr_1fr]">
          <div>
            <p className="inline-flex rounded-md bg-brand/10 px-3 py-1 text-sm font-bold text-brand">Webサービス向け AI検索対策</p>
            <h1 className="mt-4 text-[2.25rem] font-bold leading-[1.3] tracking-tight text-slate-900 sm:text-[3.25rem]">
              AIに
              <br />
              <span className="text-brand">おすすめされる</span>
              <br />
              サービスへ。
            </h1>
            <p className="mt-5 max-w-lg leading-relaxed text-slate-700 sm:text-lg">ChatGPT・Geminiで「おすすめは？」と聞かれたとき、名前が挙がるように。今の状態を調べて、直し方までお伝えします。</p>
            <div className="mt-7 max-w-md rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
              <p className="text-xs font-bold text-slate-600">AIに紹介された回数（10回答中・架空の例）</p>
              <div className="mt-3 space-y-2">
                {[["自社", 4, "bg-brand"], ["競合A", 7, "bg-slate-400"], ["競合B", 5, "bg-slate-300"]].map(([n, v, c]) => (
                  <div key={n as string} className="grid grid-cols-[3.5rem_1fr_2rem] items-center gap-2 text-sm">
                    <span className="font-semibold text-slate-700">{n as string}</span>
                    <span className="h-3 rounded-full bg-slate-100">
                      <span className={`block h-3 rounded-full ${c}`} style={{ width: `${(v as number) * 10}%` }} />
                    </span>
                    <span className="text-right font-num font-bold text-slate-900">{v as number}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
          <div id="apply" className="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_12px_32px_-12px_rgba(17,24,39,0.18)] sm:p-7">
            <p className="text-xl font-bold text-slate-900">無料で診断を依頼する</p>
            <p className="mt-1 text-sm text-slate-600">4項目・1分で入力できます</p>
            <div className="mt-5">
              <HeroLeadForm />
            </div>
          </div>
        </div>
      </section>

      {/* 3つの約束 */}
      <section className="ml-[calc(50%-50vw)] w-screen bg-white">
        <ul className="mx-auto grid max-w-6xl gap-3 px-4 py-6 sm:grid-cols-3 sm:px-6">
          {[["費用はかかりません", "無料チェックは0円。有料プランへの自動切り替えもありません。"], ["3営業日以内にお届け", "紹介状況と、最初に直す3つのことをメールでお送りします。"], ["アカウント登録は不要", "サイトのURLとご連絡先を送るだけで始められます。"]].map(([t, b]) => (
            <li key={t} className="flex gap-3 rounded-xl bg-[#f5f8fd] p-4">
              <span aria-hidden className="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand text-sm font-bold text-white">✓</span>
              <div>
                <p className="font-bold text-slate-900">{t}</p>
                <p className="mt-1 text-sm leading-relaxed text-slate-600">{b}</p>
              </div>
            </li>
          ))}
        </ul>
      </section>

      {/* なぜ今：サイトに来る前にAIで候補が絞られる */}
      <Band id="why" className="bg-[#f5f8fd]">
        <Head en="なぜ今、AI検索対策なのか" sub="見込み客は、サイトを見る前にAIへ質問し、候補を絞り込んでからやって来ます。AIの回答に名前がなければ、比べてもらうこともできません。">
          お客さまは、<span className={em}>AIで比べてから</span>サイトに来ます
        </Head>
        <ol className="mt-10 grid gap-2 md:grid-cols-4">
          {[["知る", "SNS・広告・紹介", false], ["調べる", "検索・比較サイト", false], ["AIで比べる", "「おすすめは？」と質問し、候補を絞る", true], ["申し込む", "絞った候補のサイトを見て決める", false]].map(([t, b, hot], i) => (
            <li key={t as string} className={`relative rounded-xl p-5 ${hot ? "bg-brand text-white" : "bg-white text-slate-900 ring-1 ring-slate-200"}`}>
              <p className={`text-sm font-bold ${hot ? "text-blue-100" : "text-slate-500"}`}>{i + 1}</p>
              <p className="mt-1 text-lg font-bold">{t as string}</p>
              <p className={`mt-1 text-sm leading-relaxed ${hot ? "text-blue-50" : "text-slate-600"}`}>{b as string}</p>
            </li>
          ))}
        </ol>
        <h3 className="mt-12 text-center text-xl font-bold text-slate-900">AIススメルが測る、3種類の質問</h3>
        <div className="mt-6 grid gap-4 md:grid-cols-3">
          {QUESTION_TYPES.map(([t, ex, b]) => (
            <div key={t} className="rounded-xl bg-white p-5 ring-1 ring-slate-200">
              <p className="font-bold text-brand">{t}</p>
              <p className="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-800">例：{ex}</p>
              <p className="mt-3 text-sm leading-relaxed text-slate-700">{b}</p>
            </div>
          ))}
        </div>
      </Band>

      {/* ABOUT：AI検索対策とは＋計測→分析→改善の流れ */}
      <Band id="about" className="bg-white">
        <div className="grid items-center gap-12 lg:grid-cols-2">
          <div className="text-center lg:text-left">
            <p className="text-sm font-bold text-brand">AI検索対策とは</p>
            <h2 className="mt-3 text-[1.625rem] font-bold leading-snug text-slate-900 sm:text-[2.25rem]">
              <span className={em}>AIに選ばれる</span>ための
              <br />
              計測から改善までを一続きで
            </h2>
            <p className="mt-5 leading-loose text-slate-700">AI検索対策（LLMO・GEO）は、AIの回答で自社が紹介されるようにサイトの情報を整えることです。AIススメルは「いま自社がどう紹介されているか」を測り、直すところまでお手伝いします。</p>
          </div>
          <ol className="relative mx-auto grid w-full max-w-md grid-cols-2 gap-4">
            {CYCLE.map(([t, b], i) => (
              <li key={t} className={`grid aspect-square place-items-center rounded-full p-5 text-center ${["bg-[#e0f2fe] col-span-2 mx-auto w-1/2", "bg-[#cfe2ff]", "bg-[#dbeafe]"][i]}`}>
                <div>
                  <p className="font-num text-xs font-bold text-slate-500">STEP {i + 1}</p>
                  <p className="text-xl font-bold text-brand">{t}</p>
                  <p className="mt-1 text-sm font-medium leading-snug text-slate-700">{b}</p>
                </div>
              </li>
            ))}
          </ol>
        </div>
        <div className="mt-14 overflow-hidden rounded-2xl border border-slate-200">
          <table className="w-full text-left text-sm sm:text-base">
            <thead className="bg-slate-50">
              <tr>
                <th className="w-20 p-3 sm:w-40 sm:p-4" />
                <th className="p-3 font-bold text-slate-900 sm:p-4">SEO</th>
                <th className="p-3 font-bold text-brand sm:p-4">AI検索対策</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-200">
              {COMPARE.map(([k, a, b]) => (
                <tr key={k}>
                  <th scope="row" className="bg-slate-50 p-3 font-semibold text-slate-700 sm:p-4">{k}</th>
                  <td className="p-3 text-slate-700 sm:p-4">{a}</td>
                  <td className="p-3 font-medium text-slate-900 sm:p-4">{b}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        <h3 className="mt-12 text-center text-xl font-bold text-slate-900">AIススメルでできること</h3>
        <ul className="mx-auto mt-6 grid max-w-4xl gap-3 sm:grid-cols-2">
          {SERVICES.map((t) => (
            <li key={t} className="flex gap-3 rounded-xl bg-[#f5f7fb] p-4 text-slate-800">
              <span aria-hidden className="font-bold text-brand">✓</span>
              {t}
            </li>
          ))}
        </ul>
        <Cta />
      </Band>

      {/* PROBLEMS：お悩み */}
      <Band className="bg-[#f1f5f9]">
        <Head en="こんなお悩み" sub="ChatGPTやGeminiの中で「何が起きているか」は、検索順位を見ても分かりません。AIススメルなら数字で確かめられます。">
          <span className={em}>AI時代の集客の悩み</span>を解決します
        </Head>
        <div className="mt-10 grid gap-5 md:grid-cols-3">
          {WORRIES.slice(0, 3).map((w, i) => (
            <div key={w} className="flex flex-col items-center rounded-2xl bg-white p-6 text-center">
              <p className="relative w-full rounded-full bg-[#13284f] px-5 py-4 font-bold leading-snug text-white">
                {w}……
                <span aria-hidden className="absolute -bottom-2 left-1/2 h-4 w-4 -translate-x-1/2 rotate-45 bg-[#13284f]" />
              </p>
              <p className="mt-8 leading-relaxed text-slate-700">{WORRY_NOTES[i]}</p>
            </div>
          ))}
        </div>
        <Cta />
      </Band>

      {/* FEATURES：強み（番号つきの大きなパネル） */}
      <Band id="features" className="bg-white">
        <Head en="AIススメルの強み">
          <span className={em}>4つの強み</span>で、AI検索対策を確かに
        </Head>
        <div className="mt-10 space-y-6">
          {STRENGTHS.map(([t, b], i) => (
            <div key={t} className="grid items-center gap-6 rounded-2xl bg-[#e3ebf5] p-6 sm:p-10 md:grid-cols-[auto_1fr]">
              <p className="font-num text-4xl font-bold text-brand underline decoration-4 underline-offset-8 sm:text-5xl">0{i + 1}</p>
              <div>
                <h3 className="text-xl font-bold text-slate-900 sm:text-2xl">{t}</h3>
                <p className="mt-3 leading-relaxed text-slate-700">{b}</p>
              </div>
            </div>
          ))}
        </div>
        <div className="mt-8 text-center">
          <Link href="/ai/sample" className="inline-flex min-h-12 items-center rounded-full border-2 border-brand bg-white px-8 font-bold text-brand hover:bg-blue-50">
            ＞ レポートの見本を見る
          </Link>
        </div>
      </Band>

      {/* FLOW */}
      <Band id="flow" className="bg-[#e3ebf5]">
        <Head en="ご利用の流れ">
          あなたがするのは、<span className={em}>URLを送るだけ</span>
        </Head>
        <ol className="mt-10 grid gap-5 md:grid-cols-3">
          {FLOW.map(([t, b], i) => (
            <li key={t} className="rounded-2xl bg-white p-6 text-center">
              <span className="mx-auto grid h-12 w-12 place-items-center rounded-full bg-brand font-num text-lg font-bold text-white">{i + 1}</span>
              <h3 className="mt-4 text-lg font-bold text-slate-900">{t}</h3>
              <p className="mt-2 text-sm leading-relaxed text-slate-700">{b}</p>
            </li>
          ))}
        </ol>
      </Band>

      {/* PRICE：青い帯に料金カード */}
      <Band id="pricing" className="bg-[#13284f]">
        <Head en="料金" light sub="無料チェックだけでもご利用いただけます。有料プランへの自動切り替えはありません。">
          まず無料で。改善の依頼はそのあとで。
        </Head>
        <div className="mt-10 rounded-2xl bg-white/10 p-1">
          <div className="rounded-2xl bg-white p-4 sm:p-6">
            <ServicePlans />
          </div>
        </div>
        <div className="mt-8 text-center">
          <Link href="/ai/pricing" className="inline-flex min-h-12 items-center rounded-full bg-white px-8 font-bold text-brand hover:bg-blue-50">
            ＞ 料金の詳細を見る
          </Link>
        </div>
      </Band>

      {/* 代表と事例 */}
      <Band className="bg-white">
        <Head en="運営者">
          <span className={em}>自社サイトで実践した方法</span>をお届けします
        </Head>
        <div className="mt-10 grid gap-6 md:grid-cols-2">
          <div className="rounded-2xl bg-[#e3ebf5] p-6 sm:p-8">
            <p className="text-sm font-bold text-brand">代表紹介</p>
            <p className="mt-2 text-2xl font-bold text-slate-900">鳥居 潤</p>
            <p className="text-sm text-slate-600">AIススメル 代表</p>
            <p className="mt-4 leading-relaxed text-slate-700">求人サイト「zaito」を運営しています。自分のサイトでChatGPTからの流入が増えたことをきっかけに、AIでの紹介状況を測って改善する方法を作り、同じ悩みを持つWebサービスに提供しています。</p>
          </div>
          <div className="rounded-2xl bg-[#e3ebf5] p-6 sm:p-8">
            <p className="text-sm font-bold text-brand">運営サイトの事例：求人サイト「zaito」</p>
            <dl className="mt-4 grid grid-cols-2 gap-4">
              {[["14.5%", "ユーザーのうちChatGPT経由"], ["51.6%", "キーイベントのうちChatGPT経由"]].map(([v, k]) => (
                <div key={k} className="rounded-xl bg-white p-4 text-center">
                  <dd className="font-num text-4xl font-bold text-brand">{v}</dd>
                  <dt className="mt-1 text-sm text-slate-700">{k}</dt>
                </div>
              ))}
            </dl>
            <p className="mt-4 text-xs leading-relaxed text-slate-600">GA4、2026年9月24日〜10月21日の28日間（469人中68人、95件中49件）。キーイベントは「応募ページへ進む」などの操作数で、応募完了率ではありません。サイト1件の数字で、効果を保証するものではありません。</p>
          </div>
        </div>
      </Band>

      {/* FAQ */}
      <Band id="faq" className="bg-[#f5f7fb]">
        <Head en="Q&A">よくある質問</Head>
        <div className="mx-auto mt-10 max-w-3xl space-y-3">
          {FAQ.map(([q, a]) => (
            <details key={q} className="group rounded-xl bg-white px-5 ring-1 ring-slate-200">
              <summary className="flex cursor-pointer list-none items-start justify-between gap-4 py-5 font-bold text-slate-900">
                <span>
                  <span className="mr-2 text-brand">Q.</span>
                  {q}
                </span>
                <span aria-hidden className="shrink-0 text-brand group-open:hidden">＋</span>
                <span aria-hidden className="hidden shrink-0 text-brand group-open:inline">−</span>
              </summary>
              <p className="pb-5 leading-relaxed text-slate-700">
                <span className="mr-2 font-bold text-slate-500">A.</span>
                {a}
              </p>
            </details>
          ))}
        </div>
      </Band>

      {/* 最後の申し込み */}
      <Band className="bg-[#13284f]">
        <div className="text-center text-white">
          <h2 className="text-[1.625rem] font-bold leading-snug sm:text-[2.25rem]">まずは、今の状態を知るところから。</h2>
          <p className="mt-4 text-blue-50">自社のURLを送ると、3営業日以内に診断結果が届きます。</p>
          <Link href="/ai/apply" className="mt-8 inline-flex min-h-14 w-full max-w-sm items-center justify-center rounded-lg bg-white px-8 text-lg font-bold text-[#13284f] shadow-sm hover:bg-blue-50 sm:w-auto">
            無料で診断を依頼する
          </Link>
        </div>
      </Band>
    </div>
  );
}
