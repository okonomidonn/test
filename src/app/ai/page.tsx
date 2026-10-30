import Link from "next/link";
import { btn } from "@/components/ai/ds";
import { ServicePlans } from "@/components/ai/service-plans";

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

function Head({ label, title }: { label: string; title: string }) {
  return <div><p className="text-sm font-semibold text-brand">{label}</p><h2 className="mt-2 text-2xl font-bold leading-snug text-slate-900 sm:text-3xl">{title}</h2></div>;
}

function Cta() {
  return <div className="mt-8 text-center"><Link href="/ai/apply" className={`${btn("primary", "lg")} w-full sm:w-auto`}>まずは無料でチェックする <span aria-hidden>→</span></Link><p className="mt-2 text-sm text-slate-600">3営業日以内に、結果をメールでお届けします。</p></div>;
}

export default function AiLandingPage() {
  return (
    <div className="space-y-16 pb-6 sm:space-y-24">
      <section className="grid items-start gap-8 pt-2 sm:pt-8 lg:grid-cols-[1.1fr_1fr] lg:gap-12">
        <div className="min-w-0 lg:py-6">
          <p className="inline-flex rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-800">Webサービス向け AI検索対策</p>
          <h1 className="mt-5 text-[clamp(1.5rem,7vw,3rem)] font-bold leading-[1.4] tracking-tight text-slate-900 sm:leading-[1.35]">
            <span className="block">AIに</span>
            <span className="block"><span className="text-brand">おすすめされる</span>サービスへ。</span>
          </h1>
          <p className="mt-5 max-w-xl text-base leading-relaxed text-slate-700 sm:text-lg">ChatGPTやGeminiに「おすすめは？」と聞かれたとき、あなたのサービスが名前を挙げてもらえるように。今の状態を調べて、サイトの直し方までお伝えします。</p>
          <div className="mt-7">
            <Link href="/ai/apply" className={`${btn("primary", "lg")} w-full sm:w-auto`}>まずは無料でチェックする <span aria-hidden>→</span></Link>
            <p className="mt-3 text-sm text-slate-600">登録不要・費用0円・3営業日以内にメールでお届け</p>
          </div>
        </div>
        <div className="min-w-0 rounded-2xl bg-[#edf3fc] p-4 sm:p-6">
          <div className="mb-3 flex flex-wrap items-center justify-between gap-2 text-sm">
            <p className="font-bold text-slate-900">診断レポートの見本</p>
            <span className="rounded bg-white px-2 py-0.5 text-xs text-slate-600">トライアルの例</span>
          </div>
          <div className="rounded-xl border border-blue-100 bg-white p-5 sm:p-6">
            <p className="text-sm font-semibold text-slate-600">10回答中、紹介された回数</p>
            <p className="mt-2 text-xl font-bold leading-snug text-slate-900"><span className="inline-block">自社は4回。</span><span className="inline-block">競合は7回。</span></p>
            <dl className="mt-5 space-y-4">
              {[{ name: "自社", count: 4, color: "bg-brand" }, { name: "競合の例", count: 7, color: "bg-slate-300" }].map((r) => <div key={r.name}>
                <div className="mb-1.5 flex items-baseline justify-between text-sm"><dt className="font-semibold text-slate-700">{r.name}</dt><dd className="font-num text-slate-600"><strong className="text-xl text-slate-900">{r.count}</strong> / 10回</dd></div>
                <div className="h-3 overflow-hidden rounded-full bg-slate-100"><div className={`h-full rounded-full ${r.color}`} style={{ width: `${r.count * 10}%` }} /></div>
              </div>)}
            </dl>
            <div className="mt-6 border-t border-slate-200 pt-4">
              <p className="text-sm font-semibold text-brand">最初に直すことの例</p>
              <p className="mt-1 font-bold text-slate-900">トップページに「誰のためのサービスか」を書く</p>
            </div>
          </div>
          <Link href="/ai/sample" className="mt-4 inline-flex min-h-10 items-center text-sm font-semibold text-brand underline underline-offset-4">詳しいレポートの見本を見る <span aria-hidden className="ml-2">→</span></Link>
          <p className="mt-1 text-sm text-slate-600">数値は架空の例です。無料チェックは紹介状況と改善点を、トライアルは競合比較までお届けします。</p>
        </div>
      </section>
      <section id="about" className="scroll-mt-24">
        <Head label="AI検索対策とは" title="AIの回答で「おすすめ」に挙がるための対策です。" />
        <p className="mt-4 max-w-3xl leading-relaxed text-slate-700">ChatGPTやGeminiに「おすすめの〇〇は？」と聞くと、AIはいくつかのサービスを挙げて答えます。そこに自社が入るように、サイトの情報を整えるのがAI検索対策（LLMO・GEOとも呼ばれます）です。</p>
        <div className="mt-6 overflow-hidden rounded-xl border border-slate-200">
          <table className="w-full text-left text-sm sm:text-base">
            <thead className="bg-slate-50"><tr><th className="w-24 p-3 sm:w-40 sm:p-4" /><th className="p-3 font-bold text-slate-900 sm:p-4">SEO</th><th className="p-3 font-bold text-brand sm:p-4">AI検索対策</th></tr></thead>
            <tbody className="divide-y divide-slate-200">
              {COMPARE.map(([k, a, b]) => <tr key={k}><th scope="row" className="bg-slate-50 p-3 font-semibold text-slate-700 sm:p-4">{k}</th><td className="p-3 text-slate-700 sm:p-4">{a}</td><td className="p-3 text-slate-900 sm:p-4">{b}</td></tr>)}
            </tbody>
          </table>
        </div>
        <h3 className="mt-10 text-lg font-bold text-slate-900">AIススメルでできること</h3>
        <ul className="mt-4 grid gap-x-8 gap-y-3 sm:grid-cols-2">
          {SERVICES.map((t) => <li key={t} className="flex gap-2 text-slate-800"><span aria-hidden className="font-bold text-brand">✓</span>{t}</li>)}
        </ul>
      </section>

      <section className="rounded-2xl bg-slate-50 p-5 sm:p-8">
        <Head label="こんなお悩みありませんか？" title="AIで競合ばかり紹介されていませんか。" />
        <ul className="mt-6 grid gap-3 sm:grid-cols-2">
          {WORRIES.map((t) => <li key={t} className="rounded-lg bg-white p-4 text-slate-800 ring-1 ring-slate-200">{t}</li>)}
        </ul>
        <p className="mt-6 text-lg font-bold text-slate-900">そのお悩み、AIススメルが<span className="text-brand">調べて、直すところまで</span>お手伝いします。</p>
      </section>

      <section id="features" className="scroll-mt-24">
        <Head label="AIススメルの強み" title="選ばれる理由は4つです。" />
        <div className="mt-7 grid gap-4 md:grid-cols-2">
          {STRENGTHS.map(([title, body], i) => <div key={title} className="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
            <p className="font-num text-2xl font-bold text-brand">0{i + 1}</p><h3 className="mt-2 text-lg font-bold text-slate-900">{title}</h3><p className="mt-2 leading-relaxed text-slate-700">{body}</p>
          </div>)}
        </div>
        <Cta />
      </section>

      <section id="flow" className="scroll-mt-24">
        <Head label="ご利用の流れ" title="あなたがするのは、URLを送るだけ。" />
        <ol className="mt-7 grid gap-6 md:grid-cols-3">
          {FLOW.map(([title, body], i) => <li key={title} className="flex gap-3"><span className="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand font-num font-bold text-white">{i + 1}</span><div><h3 className="font-bold text-slate-900">{title}</h3><p className="mt-2 text-sm leading-relaxed text-slate-700">{body}</p></div></li>)}
        </ol>
      </section>

      <section id="pricing" className="scroll-mt-24">
        <Head label="料金プラン" title="まず無料で。改善の依頼はそのあとで。" />
        <p className="mt-3 text-slate-700">無料チェックだけでもご利用いただけます。有料プランへの自動切り替えはありません。</p>
        <div className="mt-7"><ServicePlans /></div>
        <Link href="/ai/pricing" className="mt-5 inline-flex min-h-11 items-center font-semibold text-brand underline underline-offset-4">料金と作業内容を詳しく見る <span aria-hidden className="ml-2">→</span></Link>
      </section>

      <section className="grid gap-6 md:grid-cols-2">
        <div className="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
          <p className="text-sm font-semibold text-brand">代表紹介</p>
          <h2 className="mt-2 text-xl font-bold text-slate-900">鳥居 潤</h2>
          <p className="text-sm text-slate-600">AIススメル 代表</p>
          <p className="mt-4 leading-relaxed text-slate-700">求人サイト「zaito」を運営しています。自分のサイトでChatGPTからの流入が増えたことをきっかけに、AIでの紹介状況を測って改善する方法を作り、同じ悩みを持つWebサービスに提供しています。</p>
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
          <p className="text-sm font-semibold text-brand">運営サイトの事例</p>
          <h2 className="mt-2 text-xl font-bold text-slate-900">求人サイト「zaito」</h2>
          <dl className="mt-4 grid grid-cols-2 gap-4">
            {[["14.5%", "ユーザーのうちChatGPT経由"], ["51.6%", "キーイベントのうちChatGPT経由"]].map(([v, k]) => <div key={k}><dd className="font-num text-3xl font-bold text-slate-900">{v}</dd><dt className="mt-1 text-sm text-slate-700">{k}</dt></div>)}
          </dl>
          <p className="mt-4 text-xs leading-relaxed text-slate-600">GA4、2026年9月24日〜10月21日の28日間（469人中68人、95件中49件）。キーイベントは「応募ページへ進む」などの操作数で、応募完了率ではありません。サイト1件の数字で、効果を保証するものではありません。</p>
        </div>
      </section>

      <section id="faq" className="mx-auto max-w-3xl scroll-mt-24">
        <h2 className="text-2xl font-bold text-slate-900 sm:text-3xl">よくある質問</h2>
        <div className="mt-5 divide-y divide-slate-200 border-y border-slate-200">
          {FAQ.map(([q, a]) => <details key={q} className="group py-1"><summary className="flex cursor-pointer list-none items-start justify-between gap-4 py-4 font-semibold text-slate-900"><span><span className="mr-2 text-brand">Q.</span>{q}</span><span aria-hidden className="shrink-0 text-brand group-open:hidden">＋</span><span aria-hidden className="hidden shrink-0 text-brand group-open:inline">−</span></summary><p className="pb-5 leading-relaxed text-slate-700">{a}</p></details>)}
        </div>
      </section>
      <section className="rounded-2xl bg-[#142b50] px-5 py-9 text-center text-white sm:px-8 sm:py-12">
        <h2 className="text-2xl font-bold leading-snug sm:text-3xl">まずは、今の状態を知るところから。</h2>
        <p className="mt-3 text-blue-100">自社のURLを送ると、3営業日以内に診断結果が届きます。</p>
        <Link href="/ai/apply" className={`${btn("secondary", "lg")} mt-6 w-full sm:w-auto`}>無料チェックを依頼する <span aria-hidden>→</span></Link>
      </section>
    </div>
  );
}
