import Image from "next/image";
import Link from "next/link";
import { HeroLeadForm } from "@/components/ai/hero-lead-form";
import { ServicePlans } from "@/components/ai/service-plans";
import { Cta, Faq, Lead, Num, Section, ZAITO } from "@/components/ai/lp";

// 求人サイト・人材サービス専門のAIススメル（トップ /ai と /ai/for/jobs で同じ中身を出す）

const SEEKER_STATS = [
  { n: "63.5%", text: "20代の求職者のうち、就職・転職活動で生成AIを利用", src: "学情「20代転職意識調査」2026年8月・n=186（前年は46.5%）" },
  { n: "53.7%", text: "直近1年に転職した20代のうち、生成AIを活用", src: "マイナビ転職「転職者の生成AI活用に関する実態調査」2026年4月・n=700" },
  { n: "26.0%", text: "応募先の企業を、生成AIに質問して調べた転職活動者（口コミサイトの約2倍）", src: "Itera「転職活動における企業情報の収集方法に関する調査」2026年8月・n=880" },
  { n: "84.5%", text: "生成AIを使い続けている大学生（うちChatGPTが90.4%）", src: "産業能率大学「学生メディア・コンテンツ利用調査2026」・n=2,607" },
];

const QUESTION_TYPES = [
  { title: "地域で探す", ex: "大阪で未経験OKの求人を探すならどのサイト？" },
  { title: "職種・条件で探す", ex: "在宅でできるバイトを探せる求人サイトのおすすめは？" },
  { title: "採用企業が媒体を探す", ex: "アルバイト採用におすすめの求人媒体は？掲載料金も比べたい" },
  { title: "サイト名で聞く", ex: "〇〇（サイト名）の評判は？使っても大丈夫？" },
];

const FINDINGS = [
  ["紹介された回数と、何番目か", "同じ質問を、ChatGPT・Geminiで条件をそろえて何度も聞き、数えます。"],
  ["一緒に紹介される競合", "1番目に挙がっているのはどこか。分野ごとに、競合との差が分かります。"],
  ["AIの説明が、事実どおりか", "評判・料金・求人数など、名前で聞かれたときの説明を、公式の情報と照らし合わせます。"],
  ["弱い分野と、その原因", "紹介されない質問で、AIが何を根拠に答えているか（検索の上位にある記事など）を調べます。"],
  ["最初に直すこと", "効果が大きく、すぐ取りかかれることから、3つに絞ってお伝えします。"],
];

const WORK = [
  { title: "サイトの中", body: "求人一覧・特集ページの説明文、よくある質問、構造化データの原稿をお渡しします。反映は御社のご担当者さま、またはCMSの範囲でこちらが行います。" },
  { title: "サイトの外", body: "AIが根拠にしている比較記事の運営元への情報提供、求人データをもとにしたレポートの発信、口コミを増やす仕組みづくりを進めます。" },
  { title: "毎月の計測", body: "同じ質問で測り直し、紹介された回数・順番の変化と、AIからの流入（GA4）をご報告します。" },
];

const FAQ: [string, string][] = [
  ["大手の求人サイトがある中で、紹介されるようになりますか？", "大手は「おすすめの求人サイトは？」のような広い質問で強いため、地域・職種・働き方など、条件のついた質問で紹介されることを目指します。条件が細かいほど、専門の求人サイトが選ばれやすくなります。"],
  ["無料診断のあと、契約しないといけませんか？", "いいえ。結果を見てからご検討ください。フォームの送信だけで、有料の契約や自動の課金は発生しません。"],
  ["結果はいつ届きますか？", "無料診断は3営業日以内にメールでお送りします。"],
  ["必ずAIに紹介されるようになりますか？", "紹介や応募の増加はお約束できません。AIの回答は毎回変わるため、同じ条件で測り直して、変化を数字でお見せします。"],
  ["応募数が増えたかは分かりますか？", "月額プランでは、GA4でAIから来た人数と「応募ページへ進む」などの行動を毎月確かめます。"],
  ["サイトの外の作業とは、何をしますか？", "AIが根拠にしている比較記事の運営元への情報提供、求人データのレポート発信、口コミを増やす仕組みづくりなどです。御社の名前で外に出るものは、すべて事前にご確認いただきます。偽の口コミなど、ステルスマーケティングにあたることは一切行いません。"],
  ["支払い方法は？", "銀行振込（請求書払い・前払い）です。詳しくは特定商取引法に基づく表記をご覧ください。"],
];

export function JobsLanding() {
  return (
    <div className="-mt-8">
      {/* ファーストビュー */}
      <section className="ml-[calc(50%-50vw)] w-screen border-b border-slate-200 bg-[linear-gradient(120deg,#FFFFFF_0%,#FFFFFF_50%,#EAF2FE_100%)]">
        <div className="mx-auto grid max-w-6xl items-center gap-12 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-[1fr_1.05fr]">
          <div>
            <p className="text-sm font-bold text-brand">求人サイト・人材サービスのAI検索対策</p>
            <h1 className="mt-4 text-[2.5rem] font-black leading-[1.25] text-slate-900 sm:text-[3.5rem]">
              求人は、
              <br />
              AIで探す時代へ。
            </h1>
            <p className="mt-6 max-w-xl leading-loose text-slate-700 sm:text-lg">ChatGPT・Geminiに「おすすめの求人サイトは？」と聞いたとき、御社は何番目に紹介されるか。実際に聞いて数え、どこを直せば紹介されるかまでお伝えします。</p>
            <div className="mt-8 flex flex-col gap-3 sm:flex-row">
              <Link href="/ai/apply" className="inline-flex min-h-13 items-center justify-center rounded-md bg-brand px-8 font-bold text-white transition hover:bg-brand-dark">無料で診断する</Link>
              <Link href="/ai/for/jobs/book" className="inline-flex min-h-13 items-center justify-center rounded-md border border-slate-300 bg-white px-8 font-bold text-slate-900 transition hover:bg-slate-50">資料をダウンロード</Link>
            </div>
            <p className="mt-5 text-sm text-slate-500">費用0円　｜　3営業日以内にお届け　｜　登録不要</p>
          </div>
          <figure>
            <Image src="/ai/img/report-sample.jpg" alt="AIススメルの診断レポートの画面（見本）" width={1600} height={1000} priority className="w-full rounded-md border border-slate-200 shadow-[0_24px_60px_rgba(30,60,120,0.16)]" />
            <figcaption className="mt-3 text-center text-xs text-slate-500">診断レポートの見本（架空の求人サイト）</figcaption>
          </figure>
        </div>
      </section>

      {/* 求職者のAI利用 */}
      <Section id="why" tone="soft">
        <Lead line="若い世代ほど、仕事探しや企業研究にAIを使っています。" em="20代の求職者の6割超が、転職活動で生成AIを利用" />
        <div className="mt-12 grid gap-8 border-t-2 border-slate-800 pt-8 sm:grid-cols-2 lg:grid-cols-4">
          {SEEKER_STATS.map((s) => (
            <div key={s.n} className="flex flex-col gap-3">
              <Num className="text-5xl text-brand">{s.n}</Num>
              <p className="font-bold leading-relaxed text-slate-900">{s.text}</p>
              <p className="text-xs leading-relaxed text-slate-500">{s.src}</p>
            </div>
          ))}
        </div>
      </Section>

      {/* AIの答えは平均6件 */}
      <Section>
        <Lead line="検索結果のように、何十件も並ぶのではなく" em="AIの答えに載るのは、平均6件だけです" />
        <div className="mt-12 grid items-center gap-10 lg:grid-cols-[1.1fr_1fr]">
          <div className="flex items-center gap-4 sm:gap-6">
            <div className="flex flex-1 flex-col gap-2.5 rounded-md bg-slate-50 p-5">
              <p className="text-sm font-bold text-slate-500">検索結果</p>
              {Array.from({ length: 10 }, (_, i) => <span key={i} className="block h-3 bg-slate-200" />)}
              <p className="text-xs text-slate-500">何十件から、自分で選ぶ</p>
            </div>
            <span aria-hidden className="text-2xl font-bold text-blue-300">→</span>
            <div className="flex flex-1 flex-col gap-2.5 rounded-md border-2 border-brand bg-[#EEF4FE] p-5">
              <p className="text-sm font-bold text-brand">AIの答え</p>
              {["bg-brand", "bg-[#5B8FEA]", "bg-[#8FB2F1]", "bg-[#BCD0F6]", "bg-[#BCD0F6]", "bg-[#BCD0F6]"].map((c, i) => (
                <span key={i} className="flex items-center gap-2"><Num className="w-4 text-xs text-brand">{i + 1}</Num><span className={`block h-5 flex-1 ${c}`} /></span>
              ))}
              <p className="text-xs font-bold text-slate-900">最初から、候補が絞られて届く</p>
            </div>
          </div>
          <div className="divide-y divide-slate-200 border-y-2 border-t-slate-800 border-b-slate-200">
            <div className="py-6"><p className="text-lg font-bold text-slate-900">6件に入るか、何番目か</p><p className="mt-2 leading-relaxed text-slate-700">ここに入れないと、求職者に比べてもらうこともできません。</p></div>
            <div className="py-6"><p className="text-lg font-bold text-slate-900">サイト名で聞かれたときの説明も</p><p className="mt-2 leading-relaxed text-slate-700">評判・料金・求人数をどう説明するかも、AIが読んでいる情報で決まります。</p></div>
            <p className="py-4 text-xs leading-relaxed text-slate-500">「平均6件」は、当社が求人サイトを対象に行った調査（2026年11月、ChatGPT・Geminiに計162回質問）での、1回の答えに挙がったサービスの数の平均です。</p>
          </div>
        </div>
      </Section>

      {/* zaitoの実例 */}
      <Section id="case" tone="soft">
        <Lead line="運営する求人サイト「zaito」では、ChatGPTから来た人が全体の約3分の1。" em="応募に近い操作では、約半分を占めています" />
        <div className="mt-12 grid items-center gap-12 lg:grid-cols-[0.9fr_1.1fr]">
          <figure className="mx-auto w-full max-w-xl">
            <div className="relative pb-10">
            <Image src="/ai/img/zaito-pc.jpg" alt="求人サイトzaitoのトップページ（パソコン）" width={1800} height={1125} className="w-[86%] rounded-md border border-slate-200 shadow-[0_16px_40px_rgba(30,60,120,0.14)]" />
            <Image src="/ai/img/zaito-sp.jpg" alt="求人サイトzaitoのトップページ（スマホ）" width={700} height={1191} className="absolute bottom-0 right-0 w-[27%] rounded-xl border-4 border-slate-800 shadow-[0_16px_40px_rgba(30,60,120,0.22)]" />
            </div>
            <figcaption className="mt-3 text-xs text-slate-500">zaito：大学生専門の完全在宅バイト・長期インターン求人サイト</figcaption>
          </figure>
          <div className="flex flex-col gap-8">
            {[
              { label: "サイトに来た人のうち、ChatGPTから", v: ZAITO.users, w: "35.5%", color: "bg-[#8FB2F1]", tc: "text-[#5B8FEA]" },
              { label: "「応募ページへ進む」などの操作のうち、ChatGPTから", v: ZAITO.events, w: "48.7%", color: "bg-brand", tc: "text-brand" },
            ].map((b) => (
              <div key={b.label}>
                <div className="flex items-baseline justify-between gap-4"><p className="font-bold text-slate-900">{b.label}</p><Num className={`text-4xl ${b.tc}`}>{b.v.rate}</Num></div>
                <div className="mt-2 h-8 bg-slate-200/70"><div className={`h-8 ${b.color}`} style={{ width: b.w }} /></div>
                <p className="mt-1 text-sm text-slate-500">{b.v.n}</p>
              </div>
            ))}
            <div className="grid border-t-2 border-slate-800 sm:grid-cols-3">
              {[["約7倍", "ChatGPTから来た人", ZAITO.growthUsers], ["約6倍", "ChatGPTからの操作", ZAITO.growthEvents], ["約4割", "登録した人のうちChatGPTから", ZAITO.signups]].map(([n, k, d], i) => (
                <div key={k} className={`py-4 sm:pb-0 sm:pt-5 ${i ? "border-t border-slate-200 sm:border-l sm:border-t-0 sm:pl-4" : "sm:pr-4"}`}><Num className="text-3xl text-brand">{n}</Num><p className="mt-1 text-sm font-bold text-slate-900">{k}</p><p className="text-xs text-slate-500">{d}</p></div>
              ))}
            </div>
          </div>
        </div>
        <p className="mt-10 text-xs leading-relaxed text-slate-500">{ZAITO.period}。{ZAITO.note}</p>
      </Section>

      {/* 診断の内容 */}
      <Section id="features">
        <Lead line="求職者や採用担当者がAIに聞きそうな質問を、" em="ChatGPT・Geminiに実際に聞いて、数えます" />
        <div className="mt-12 grid gap-px overflow-hidden rounded-md border border-slate-200 bg-slate-200 sm:grid-cols-2 lg:grid-cols-4">
          {QUESTION_TYPES.map((q) => (
            <div key={q.title} className="bg-white p-6"><p className="font-bold text-brand">{q.title}</p><p className="mt-3 leading-relaxed text-slate-900">「{q.ex}」</p></div>
          ))}
        </div>
        <div className="mx-auto mt-14 max-w-4xl">
          <p className="text-center text-lg font-bold text-slate-900">診断でわかること</p>
          <ol className="mt-6 divide-y divide-slate-200 border-y-2 border-t-slate-800 border-b-slate-200">
            {FINDINGS.map(([t, b]) => (
              <li key={t} className="grid gap-2 py-6 sm:grid-cols-[16rem_1fr] sm:items-baseline sm:gap-6">
                <p className="font-bold text-slate-900">{t}</p>
                <p className="leading-relaxed text-slate-700">{b}</p>
              </li>
            ))}
          </ol>
        </div>
        <Cta />
      </Section>

      {/* 改善の進め方 */}
      <Section tone="soft">
        <Lead line="サイトの中だけでなく、" em="AIが読んでいる「サイトの外」の情報まで整えます" />
        <div className="mt-12 grid gap-6 lg:grid-cols-3">
          {WORK.map((w) => (
            <div key={w.title} className="border-t-4 border-brand bg-white p-7">
              <p className="text-xl font-bold text-slate-900">{w.title}</p>
              <p className="mt-3 leading-relaxed text-slate-700">{w.body}</p>
            </div>
          ))}
        </div>
        <div className="mt-10 grid overflow-hidden rounded-md sm:grid-cols-3">
          {[["1か月目", "原稿をつくり、外への働きかけを始める", "bg-[#9DBCF5]"], ["2か月目", "測り直して、弱い所を直し、足す", "bg-[#5B8FEA]"], ["3か月目", "同じ条件で測り直し、効果を報告", "bg-brand"]].map(([m, t, c]) => (
            <div key={m} className={`${c} p-5 text-white`}><p className="text-sm font-bold">{m}</p><p className="mt-1 font-bold">{t}</p></div>
          ))}
        </div>
        <p className="mt-4 text-sm text-slate-600">AIや検索に反映されるまで、数週間〜数か月かかります。そのため、3か月で効果を見る計画をおすすめしています。</p>
      </Section>

      {/* 料金 */}
      <Section id="pricing">
        <Lead line="無料診断だけのご利用もできます。" em="改善のご依頼は、結果を見てから" />
        <div className="mt-12">
          <ServicePlans />
        </div>
        <p className="mt-8 text-center">
          <Link href="/ai/pricing" className="font-bold text-brand underline underline-offset-4">料金の詳細と、他社との比較を見る</Link>
        </p>
      </Section>

      {/* 運営者 */}
      <Section tone="soft">
        <Lead em={<>求人サイトを自分で運営しながら、<br className="hidden sm:inline" />AI検索対策を試してきました</>} />
        <div className="mx-auto mt-12 grid max-w-4xl gap-8 sm:grid-cols-[14rem_1fr]">
          <div>
            <p className="text-3xl font-black text-slate-900">鳥居 潤</p>
            <p className="mt-1 text-sm font-bold text-brand">AIススメル 代表</p>
          </div>
          <div className="space-y-4 leading-loose text-slate-700">
            <p>大学生専門の在宅求人サイト「zaito」を運営しています。ChatGPTから来る人が増えたことをきっかけに、AIにどう紹介されているかを測り、直し、また測ることを、自分のサイトで続けてきました。</p>
            <p>AIススメルは、その手順を、ほかの求人サイト・人材サービスでも使えるようにしたものです。</p>
          </div>
        </div>
      </Section>

      {/* よくある質問 */}
      <Section id="faq">
        <Lead em="ご検討中の方からよくいただく質問" />
        <Faq items={FAQ} />
      </Section>

      {/* 申し込み */}
      <Section tone="blue">
        <Lead light line="求人サイトのURLを送るだけ。" em="3営業日以内に、診断結果をお送りします" />
        <div id="apply" className="mx-auto mt-10 max-w-xl rounded-md bg-white p-6 text-slate-900 sm:p-8">
          <HeroLeadForm urlLabel="求人サイトのURL" />
        </div>
      </Section>
    </div>
  );
}
