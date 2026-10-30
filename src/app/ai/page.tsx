import Link from "next/link";
import { ServicePlans } from "@/components/ai/service-plans";
import { LeadForm } from "@/components/ai/lead-form";

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
  ["SEO対策とは何が違いますか？", "SEOは検索結果の順位、AI検索対策はAIの回答で紹介されることが目的です。サイトの情報を整える点は共通しており、SEOにも役立つ改善が中心です。"],
  ["支払い方法は？", "銀行振込（請求書払い・前払い）です。詳しくは特定商取引法に基づく表記をご覧ください。"],
];
const FLOW = [
  ["サイトのURLを送る", "会社・サービス名と、ご連絡先を入力。アカウント登録は不要です。"],
  ["AIススメルが調べる", "AIでの紹介状況と、公開されているサイトの情報を確認します。"],
  ["結果がメールで届く", "3営業日以内に、紹介状況と最初に直す3つのことをお送りします。"],
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
      <p className={`font-num text-sm font-bold tracking-[0.2em] ${light ? "text-blue-100" : "text-brand"}`}>{en}</p>
      <h2 className={`mt-3 text-[1.625rem] font-bold leading-snug sm:text-[2.25rem] ${light ? "text-white" : "text-slate-900"}`}>{children}</h2>
      {sub && <p className={`mx-auto mt-4 max-w-2xl leading-relaxed ${light ? "text-blue-50" : "text-slate-700"}`}>{sub}</p>}
    </div>
  );
}

const em = "text-brand";

function Cta() {
  return (
    <div className="mt-10 text-center">
      <Link href="/ai/apply" className="inline-flex min-h-14 w-full max-w-sm items-center justify-center rounded-full bg-[#ffcc4d] px-8 text-lg font-bold text-slate-900 shadow-sm transition hover:bg-[#ffc229] sm:w-auto">
        無料でチェックしてみる
      </Link>
      <p className="mt-3 text-sm text-slate-600">3営業日以内に、結果をメールでお届けします。</p>
    </div>
  );
}

export default function AiLandingPage() {
  return (
    <div className="-mt-8">
      {/* ファーストビュー：左にコピー、右に申し込みフォーム */}
      <Band className="bg-gradient-to-r from-[#3b82f6] via-[#5b6cf0] to-[#8b5cf6]">
        <div className="grid items-center gap-10 lg:grid-cols-[1.1fr_1fr]">
          <div className="text-center text-white lg:text-left">
            <p className="text-lg font-bold sm:text-xl">【無料チェック受付中】</p>
            <h1 className="mt-3 text-[2rem] font-bold leading-[1.35] sm:text-[3rem]">
              AIに
              <br />
              おすすめされる
              <br />
              サービスへ。
            </h1>
            <p className="mx-auto mt-5 max-w-xl leading-relaxed text-blue-50 sm:text-lg lg:mx-0">ChatGPT・Geminiで「おすすめは？」と聞かれたとき、あなたのサービスが選ばれるように。今の状態を調べて、直し方までお伝えします。</p>
            <ul className="mx-auto mt-6 grid max-w-md grid-cols-3 gap-2 text-center lg:mx-0">
              {[["0円", "で診断"], ["3営業日", "でお届け"], ["登録", "不要"]].map(([a, b]) => (
                <li key={a} className="rounded-xl bg-white/15 px-2 py-3 ring-1 ring-white/30">
                  <p className="font-num text-xl font-bold">{a}</p>
                  <p className="text-xs text-blue-50">{b}</p>
                </li>
              ))}
            </ul>
          </div>
          <div id="apply" className="rounded-2xl bg-white p-5 shadow-xl sm:p-7">
            <p className="text-center text-xl font-bold text-brand">無料チェックはこちらから</p>
            <div className="mt-5">
              <LeadForm kind="CHECK" />
            </div>
          </div>
        </div>
      </Band>

      {/* ABOUT：AI検索対策とは＋計測→分析→改善の流れ */}
      <Band id="about" className="bg-white">
        <div className="grid items-center gap-12 lg:grid-cols-2">
          <div className="text-center lg:text-left">
            <p className="font-num text-sm font-bold tracking-[0.2em] text-brand">ABOUT</p>
            <h2 className="mt-3 text-[1.625rem] font-bold leading-snug text-slate-900 sm:text-[2.25rem]">
              <span className={em}>AIに選ばれる</span>ための
              <br />
              計測から改善までを一続きで
            </h2>
            <p className="mt-5 leading-loose text-slate-700">AI検索対策（LLMO・GEO）は、AIの回答で自社が紹介されるようにサイトの情報を整えることです。AIススメルは「いま自社がどう紹介されているか」を測り、直すところまでお手伝いします。</p>
          </div>
          <ol className="relative mx-auto grid w-full max-w-md grid-cols-2 gap-4">
            {CYCLE.map(([t, b], i) => (
              <li key={t} className={`grid aspect-square place-items-center rounded-full p-5 text-center ${["bg-[#c7f0ee] col-span-2 mx-auto w-1/2", "bg-[#cfe2ff]", "bg-[#e5dcfb]"][i]}`}>
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
      <Band className="bg-[#ece6fb]">
        <Head en="PROBLEMS" sub="ChatGPTやGeminiの中で「何が起きているか」は、検索順位を見ても分かりません。AIススメルなら数字で確かめられます。">
          <span className={em}>AI時代の集客の悩み</span>を解決します
        </Head>
        <div className="mt-10 grid gap-5 md:grid-cols-3">
          {WORRIES.slice(0, 3).map((w, i) => (
            <div key={w} className="flex flex-col items-center rounded-2xl bg-white p-6 text-center">
              <p className="relative w-full rounded-full bg-gradient-to-r from-[#3b82f6] to-[#8b5cf6] px-5 py-4 font-bold leading-snug text-white">
                {w}……
                <span aria-hidden className="absolute -bottom-2 left-1/2 h-4 w-4 -translate-x-1/2 rotate-45 bg-[#6366f1]" />
              </p>
              <p className="mt-8 leading-relaxed text-slate-700">{WORRY_NOTES[i]}</p>
            </div>
          ))}
        </div>
        <Cta />
      </Band>

      {/* FEATURES：強み（番号つきの大きなパネル） */}
      <Band id="features" className="bg-white">
        <Head en="FEATURES">
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
        <Head en="FLOW">
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
      <Band id="pricing" className="bg-[#5b86d8]">
        <Head en="PRICE" light sub="無料チェックだけでもご利用いただけます。有料プランへの自動切り替えはありません。">
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
        <Head en="ABOUT US">
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
        <Head en="FAQ">よくある質問</Head>
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
                <span className="mr-2 font-bold text-[#8b5cf6]">A.</span>
                {a}
              </p>
            </details>
          ))}
        </div>
      </Band>

      {/* 最後の申し込み */}
      <Band className="bg-gradient-to-r from-[#3b82f6] via-[#5b6cf0] to-[#8b5cf6]">
        <div className="text-center text-white">
          <h2 className="text-[1.625rem] font-bold leading-snug sm:text-[2.25rem]">まずは、今の状態を知るところから。</h2>
          <p className="mt-4 text-blue-50">自社のURLを送ると、3営業日以内に診断結果が届きます。</p>
          <Link href="/ai/apply" className="mt-8 inline-flex min-h-14 w-full max-w-sm items-center justify-center rounded-full bg-[#ffcc4d] px-8 text-lg font-bold text-slate-900 shadow-sm hover:bg-[#ffc229] sm:w-auto">
            無料でチェックしてみる
          </Link>
        </div>
      </Band>
    </div>
  );
}
