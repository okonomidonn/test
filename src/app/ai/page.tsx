import Link from "next/link";
import { ServicePlans } from "@/components/ai/service-plans";
import { HeroLeadForm } from "@/components/ai/hero-lead-form";
import { Band, Cta, Faq, Head, ZAITO } from "@/components/ai/lp";
import { VERTICALS } from "@/lib/ai-check/verticals";
import { CompetitorTable } from "@/components/ai/marketing";
import { ImprovementExample } from "@/components/ai/improvement-example";

const COMPARE = [
  ["目的", "検索結果で上位に表示される", "AIの回答で紹介・引用される"],
  ["相手", "Googleなどの検索エンジン", "ChatGPT・Geminiなどの生成AI"],
  ["見る数字", "順位・クリック数", "紹介された回数・順番・引用元"],
];
const WORK = [
  ["測る", "見込み客が聞きそうな質問を、ChatGPTとGeminiに実際に聞きます。名前が出た回数と、何番目に出たかを数えます。"],
  ["比べる", "同じ質問・同じ条件で競合も測ります。どの質問で負けているのか、AIがどのサイトを根拠にしているのかが分かります。"],
  ["直す", "「どのページに、何を書くか」を書き換え例つきでお渡しします。有料プランでは、CMSへの反映まで行います。"],
  ["測り直す", "30日後に同じ条件で測り直します。月額プランでは、AIから来た人数と申し込みにつながった行動をGA4で確かめます。"],
];
const QUESTION_TYPES = [
  ["悩みの質問", "在宅でできる仕事の探し方は？", "サービス名を出さずに、困りごとを聞く質問。ここで名前が出ると、最初の候補に入れます。"],
  ["比較・おすすめの質問", "未経験OKの求人サイトのおすすめは？", "候補を選ぶための質問。いちばん成果につながりやすく、診断の中心です。"],
  ["サービス名の質問", "〇〇の評判は？", "名前を指定した質問。AIが自社を正しく説明しているかを確かめます。"],
];
const FLOW = [
  ["サイトのURLを送る", "会社・サービス名と、ご連絡先を入力します。アカウント登録はいりません。"],
  ["こちらで調べる", "AIでの紹介状況と、公開されているサイトの情報を確認します。"],
  ["結果がメールで届く", "3営業日以内に、紹介状況と最初に直す3つのことをお送りします。"],
];
const FAQ: [string, string][] = [
  ["どんなサービスが対象ですか？", "求人・人材サービス、スクール、SaaSなど、Webサイトで問い合わせや申し込みを受け付けているサービスが対象です。"],
  ["無料チェックのあと、契約しないといけませんか？", "いいえ。結果を見てからご検討ください。フォーム送信だけで有料契約や自動課金は発生しません。"],
  ["結果はいつ届きますか？", "無料チェックは3営業日以内にメールでお送りします。トライアルのご相談は2営業日以内に進め方とお見積もりをご連絡します。"],
  ["改善すれば、必ずAIに紹介されますか？", "紹介や問い合わせの増加は保証していません。AIの回答は毎回変わるため、同じ条件で測り直して変化を確かめます。"],
  ["GoogleのAIによる概要（AI Overviews）も対象ですか？", "現在の計測対象はChatGPTとGeminiです。AI Overviewsやほかの生成AIは、ご要望に応じて対象に加えるかをご相談します。"],
  ["SEO対策とは何が違いますか？", "SEOは検索結果の順位、AI検索対策はAIの回答で紹介されることが目的です。サイトの情報を整える点は共通しており、SEOにも役立つ改善が中心です。"],
  ["支払い方法は？", "銀行振込（請求書払い・前払い）です。詳しくは特定商取引法に基づく表記をご覧ください。"],
];

export default function AiLandingPage() {
  return (
    <div className="-mt-8">
      {/* ファーストビュー：左に見出しと実際の数字、右に短い申し込みフォーム */}
      <section className="ml-[calc(50%-50vw)] w-screen bg-white">
        <div className="mx-auto grid max-w-6xl gap-10 px-4 pb-14 pt-10 sm:px-6 sm:pt-16 lg:grid-cols-[1.15fr_1fr] lg:gap-14">
          <div>
            <p className="text-sm text-slate-600">WebサービスのAI検索対策</p>
            <h1 className="mt-3 text-[2rem] font-bold leading-[1.35] text-slate-900 sm:text-[2.75rem]">
              ChatGPTに、
              <br />
              御社をおすすめしてもらう。
            </h1>
            <p className="mt-6 max-w-lg leading-loose text-slate-700">
              ChatGPTやGeminiに「おすすめは？」と聞かれたとき、御社の名前は出ているでしょうか。実際に質問して数え、競合との差と、直すページをお伝えします。
            </p>
            <figure className="mt-8 max-w-lg border-l-2 border-brand pl-5">
              <blockquote className="leading-relaxed text-slate-900">
                私が運営する求人サイト「zaito」では、応募ページへ進む操作の<strong className="font-num text-brand">{ZAITO.events}</strong>が、ChatGPTから来た人によるものでした。
              </blockquote>
              <figcaption className="mt-2 text-sm text-slate-600">AIススメル 代表　鳥居 潤</figcaption>
            </figure>
          </div>
          <div id="apply" className="rounded-lg border border-slate-300 bg-white p-5 sm:p-7">
            <p className="text-lg font-bold text-slate-900">無料で診断を依頼する</p>
            <p className="mt-1 text-sm text-slate-600">費用0円・登録不要。3営業日以内にメールでお送りします。</p>
            <div className="mt-5">
              <HeroLeadForm />
            </div>
          </div>
        </div>
      </section>

      {/* なぜ今：サイトに来る前にAIで候補が絞られる */}
      <Band id="why" className="bg-slate-50">
        <Head sub="見込み客は、サイトを見る前にAIへ質問し、候補を絞り込んでからやって来ます。AIの回答に名前がなければ、比べてもらうこともできません。">お客さまは、AIで比べてからサイトに来ます</Head>
        <p className="mt-8 flex flex-wrap items-center gap-x-3 gap-y-2 text-slate-600">
          <span>知る</span>
          <span aria-hidden>→</span>
          <span>調べる</span>
          <span aria-hidden>→</span>
          <span className="rounded-lg bg-brand px-3 py-1 font-bold text-white">AIで比べる</span>
          <span aria-hidden>→</span>
          <span>申し込む</span>
        </p>
        <h3 className="mt-12 text-lg font-bold text-slate-900">測るのは、この3種類の質問です</h3>
        <dl className="mt-4 divide-y divide-slate-200 border-y border-slate-200">
          {QUESTION_TYPES.map(([t, ex, b]) => (
            <div key={t} className="grid gap-1 py-5 md:grid-cols-[11rem_16rem_1fr] md:gap-6">
              <dt className="font-bold text-slate-900">{t}</dt>
              <dd className="text-slate-900">「{ex}」</dd>
              <dd className="text-sm leading-relaxed text-slate-600">{b}</dd>
            </div>
          ))}
        </dl>
      </Band>

      {/* 何をするか：測る→比べる→直す→測り直す */}
      <Band id="features">
        <Head sub="AI検索対策（LLMO・GEO）は、AIの回答で自社が紹介されるように、サイトの情報を整えることです。">やることは、測る・比べる・直す・測り直す</Head>
        <dl className="mt-10 grid gap-x-10 gap-y-8 md:grid-cols-2">
          {WORK.map(([t, b]) => (
            <div key={t}>
              <dt className="text-lg font-bold text-slate-900">{t}</dt>
              <dd className="mt-2 leading-relaxed text-slate-700">{b}</dd>
            </div>
          ))}
        </dl>
        <h3 className="mt-14 text-lg font-bold text-slate-900">SEOとの違い</h3>
        <div className="mt-4 overflow-x-auto">
          <table className="w-full min-w-[520px] border-y border-slate-200 text-left text-sm sm:text-base">
            <thead>
              <tr className="border-b border-slate-200">
                <th className="w-24 py-3 pr-4 sm:w-32" />
                <th className="py-3 pr-4 font-bold text-slate-600">SEO</th>
                <th className="py-3 font-bold text-slate-900">AI検索対策</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-200">
              {COMPARE.map(([k, a, b]) => (
                <tr key={k}>
                  <th scope="row" className="py-3 pr-4 font-medium text-slate-600">{k}</th>
                  <td className="py-3 pr-4 text-slate-600">{a}</td>
                  <td className="py-3 text-slate-900">{b}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        <p className="mt-8">
          <Link href="/ai/sample" className="font-semibold text-brand underline underline-offset-4">
            レポートの見本を見る
          </Link>
        </p>
      </Band>

      <Band className="bg-slate-50">
        <ImprovementExample />
      </Band>

      {/* 運営者：一人称で、なぜこのサービスをやっているか */}
      <Band>
        <div className="grid gap-10 md:grid-cols-[1.3fr_1fr]">
          <div>
            <Head>自分のサイトで試してきた方法です</Head>
            <div className="mt-6 max-w-2xl space-y-4 leading-loose text-slate-700">
              <p>AIススメルの鳥居です。在宅ワークの求人サイト「zaito」を運営しています。</p>
              <p>自分のサイトでChatGPTからの流入が増えてきたのがきっかけで、AIにどう紹介されているかを測り、直し、また測ることを続けてきました。AIススメルは、その手順をほかのWebサービスでも使えるようにしたものです。</p>
            </div>
            <p className="mt-6 font-bold text-slate-900">鳥居 潤</p>
          </div>
          <div className="self-end">
            <p className="text-sm font-bold text-slate-900">zaitoの数字（GA4・28日間）</p>
            <dl className="mt-3 divide-y divide-slate-200 border-y border-slate-200">
              {[[ZAITO.users, "ユーザーのうちChatGPT経由"], [ZAITO.events, "キーイベントのうちChatGPT経由"]].map(([v, k]) => (
                <div key={k} className="flex items-baseline justify-between gap-4 py-4">
                  <dt className="text-slate-700">{k}</dt>
                  <dd className="font-num text-3xl font-bold text-slate-900">{v}</dd>
                </div>
              ))}
            </dl>
            <p className="mt-3 text-xs leading-relaxed text-slate-500">{ZAITO.note}</p>
          </div>
        </div>
      </Band>

      {/* 流れ */}
      <Band id="flow" className="bg-slate-50">
        <Head>無料チェックは、URLと連絡先だけで始められます</Head>
        <ol className="mt-10 grid gap-8 md:grid-cols-3">
          {FLOW.map(([t, b], i) => (
            <li key={t} className="border-t-2 border-slate-900 pt-4">
              <p className="text-sm text-slate-500">{i === 0 ? "まず" : i === 1 ? "次に" : "最後に"}</p>
              <h3 className="mt-1 text-lg font-bold text-slate-900">{t}</h3>
              <p className="mt-2 leading-relaxed text-slate-700">{b}</p>
            </li>
          ))}
        </ol>
        <Cta />
      </Band>

      {/* 料金 */}
      <Band id="pricing">
        <Head sub="無料チェックだけでもご利用いただけます。有料プランへの自動切り替えはありません。">まず無料で。改善の依頼は、結果を見てから。</Head>
        <div className="mt-10">
          <ServicePlans />
        </div>
        <h3 className="mt-14 text-lg font-bold text-slate-900">他社と比べると</h3>
        <div className="mt-4">
          <CompetitorTable />
        </div>
        <p className="mt-8">
          <Link href="/ai/pricing" className="font-semibold text-brand underline underline-offset-4">
            料金の詳細と作業範囲を見る
          </Link>
        </p>
      </Band>

      {/* 業種別のご案内 */}
      <Band className="bg-slate-50">
        <Head>業種別のご案内</Head>
        <ul className="mt-8 divide-y divide-slate-200 border-y border-slate-200">
          {VERTICALS.map((v) => (
            <li key={v.slug}>
              <Link href={`/ai/for/${v.slug}`} className="group flex items-center justify-between gap-4 py-5">
                <span>
                  <span className="block font-bold text-slate-900 group-hover:text-brand">{v.name}のAI検索対策</span>
                  <span className="mt-1 block text-sm text-slate-600">{v.customer}がAIに聞く質問と、{v.name}ならではのチェック項目</span>
                </span>
                <span aria-hidden className="text-slate-400 group-hover:text-brand">→</span>
              </Link>
            </li>
          ))}
        </ul>
      </Band>

      {/* よくある質問 */}
      <Band id="faq">
        <Head>よくある質問</Head>
        <Faq items={FAQ} />
      </Band>

      {/* 最後の申し込み */}
      <Band className="bg-slate-900">
        <Head light sub="自社のURLを送ると、3営業日以内に診断結果が届きます。">まずは、今の状態を知るところから。</Head>
        <Link href="/ai/apply" className="mt-8 inline-flex min-h-13 w-full items-center justify-center rounded-lg bg-white px-7 font-bold text-slate-900 hover:bg-slate-100 sm:w-auto">
          無料で診断を依頼する
        </Link>
      </Band>
    </div>
  );
}
