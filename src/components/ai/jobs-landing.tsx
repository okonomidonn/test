import Image from "next/image";
import Link from "next/link";
import { HeroLeadForm } from "@/components/ai/hero-lead-form";
import { ServicePlans } from "@/components/ai/service-plans";
import { CtaBand, Faq, Head, Num, Section, ZAITO } from "@/components/ai/lp";

// 求人サイト・人材サービス専門のAIススメル（トップ /ai と /ai/for/jobs で同じ中身を出す）

const SEEKER_STATS = [
  { n: "63.5%", text: "20代の求職者のうち、就職・転職活動で生成AIを利用", src: "学情「20代転職意識調査」2026年8月・n=186（前年は46.5%）" },
  { n: "53.7%", text: "直近1年に転職した20代のうち、生成AIを活用", src: "マイナビ転職「転職者の生成AI活用に関する実態調査」2026年4月・n=700" },
  { n: "26.0%", text: "応募先の企業を調べるときに、生成AIに質問した転職活動者", src: "Itera「転職活動における企業情報の収集方法に関する調査」2026年8月・n=880" },
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
  ["AIの説明が、事実どおりか", "評判・求人数・特徴など、サイト名で聞かれたときの説明を、公式の情報と照らし合わせます。"],
  ["弱い分野と、その原因", "紹介されない質問で、AIが何を根拠に答えているか（検索の上位にある記事など）を調べます。"],
  ["最初に直すこと", "効果が大きく、すぐ取りかかれることから、3つに絞ってお伝えします。"],
];

const WORK = [
  { title: "サイトの中", body: "トップページや特集ページの説明文、よくある質問など、AIが読み取りやすい文章をつくります。管理画面（CMS）から更新できる部分は、こちらで反映もできます。" },
  { title: "サイトの外", body: "AIが参考にしている「おすすめ求人サイト」の比較記事へ、御社の情報を届けます。あわせて、求人データをもとにした調査記事の発信や、利用者の口コミが集まる仕組みづくりを進めます。" },
  { title: "毎月の計測", body: "同じ質問で測り直し、紹介された回数・順番の変化と、AIからの流入（GA4）をご報告します。" },
];

// zaito：前の28日間（9/24〜10/21）と直近28日間の比較
const ZAITO_GROWTH = [
  { big: "約7倍", label: "ChatGPTから来た人", before: 68, after: 494, unit: "人" },
  { big: "約6倍", label: "ChatGPTからの「応募ページへ進む」などの行動", before: 49, after: 297, unit: "件" },
];

const NEWS = [
  ["2026年11月6日", "求人サイト・人材サービス専門のAI検索対策として、サイトをリニューアルしました。"],
  ["2026年11月", "求人サイトを対象に、ChatGPT・Geminiへ計162回質問する調査を行いました。"],
];

const FAQ: [string, string][] = [
  ["大手の求人サイトがある中で、紹介されるようになりますか？", "大手は「おすすめの求人サイトは？」のような広い質問で強いため、地域・職種・働き方など、条件のついた質問で紹介されることを目指します。条件が細かいほど、専門の求人サイトが選ばれやすくなります。"],
  ["無料診断のあと、契約しないといけませんか？", "いいえ。結果を見てからご検討ください。フォームの送信だけで、有料の契約や自動の課金は発生しません。"],
  ["結果はいつ届きますか？", "無料診断は3営業日以内にメールでお送りします。"],
  ["必ずAIに紹介されるようになりますか？", "紹介や応募の増加はお約束できません。AIの回答は毎回変わるため、同じ条件で測り直して、変化を数字でお見せします。"],
  ["応募数が増えたかは分かりますか？", "月額プランでは、GA4でAIから来た人数と、そのうち「応募ページへ進む」などの行動をした数を毎月お伝えします。応募完了まで数えられるかは、御社の応募の仕組みによります。"],
  ["サイトの外の作業とは、何をしますか？", "AIが根拠にしている比較記事の運営元への情報提供、求人データのレポート発信、口コミを増やす仕組みづくりなどです。御社の名前で外に出るものは、すべて事前にご確認いただきます。偽の口コミなど、ステルスマーケティングにあたることは一切行いません。"],
  ["支払い方法は？", "銀行振込（請求書払い・前払い）です。詳しくは特定商取引法に基づく表記をご覧ください。"],
];

export function JobsLanding() {
  return (
    <div className="-mb-8 -mt-8">
      {/* ファーストビュー（濃い青の帯に、大きな見出しと診断画面） */}
      <section className="relative ml-[calc(50%-50vw)] w-screen overflow-hidden bg-[linear-gradient(135deg,#0A2463_0%,#1442B8_60%,#2563EB_100%)] text-white">
        <div aria-hidden className="pointer-events-none absolute -right-40 -top-40 h-[36rem] w-[36rem] rounded-full bg-[radial-gradient(closest-side,rgba(147,197,253,0.35),transparent)]" />
        <div className="relative mx-auto grid max-w-6xl items-center gap-12 px-4 pb-16 pt-14 sm:px-6 sm:pb-20 sm:pt-20 lg:grid-cols-[1.05fr_1fr]">
          <div className="[word-break:auto-phrase]">
            <p className="text-sm font-bold text-blue-200">求人サイト・人材サービス専門のAI検索対策</p>
            <h1 className="mt-4 text-[2.5rem] font-black leading-[1.2] sm:text-[3.5rem] xl:text-[3.875rem]">
              求人は、
              <br />
              <span className="whitespace-nowrap">AIで探す時代へ。</span>
            </h1>
            <p className="mt-6 max-w-xl leading-loose text-blue-50 sm:text-lg">ChatGPT・Geminiに「おすすめの求人サイトは？」と聞いたとき、御社は何番目に紹介されるか。実際に聞いて数え、どこを直せば紹介されるかまでお伝えします。</p>
            <div className="mt-9 flex flex-col gap-3 sm:flex-row">
              <Link href="/ai/apply" className="inline-flex min-h-14 items-center justify-center gap-3 rounded-md bg-white px-8 text-lg font-bold text-brand shadow-[0_8px_24px_rgba(0,0,0,0.18)] transition hover:bg-blue-50">無料で診断する<span aria-hidden>→</span></Link>
              <Link href="/ai/sample" className="inline-flex min-h-14 items-center justify-center rounded-md border border-white/60 px-8 font-bold text-white transition hover:bg-white/10">レポートの見本を見る</Link>
            </div>
            <p className="mt-5 text-sm text-blue-100">費用0円　｜　結果は3営業日以内　｜　登録不要</p>
          </div>
          <figure>
            <div className="rounded-lg bg-white/10 p-2 ring-1 ring-white/20 backdrop-blur-sm sm:p-3">
              <Image src="/ai/img/report-v2.jpg" alt="AIススメルの診断レポートの画面（見本）" width={1600} height={1209} priority className="w-full rounded-md shadow-[0_24px_60px_rgba(0,0,0,0.35)]" />
            </div>
            <figcaption className="mt-3 text-center text-xs text-blue-100">診断レポートの見本（架空の求人サイト）</figcaption>
          </figure>
        </div>
      </section>

      {/* 求職者のAI利用（数字のカード） */}
      <Section id="why">
        <div className="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
          <Head title={<>求職者の仕事探しは、<br className="hidden sm:inline" />検索からAIへ。</>} desc="若い世代ほど、仕事探しや企業研究にChatGPTなどの生成AIを使っています。" />
        </div>
        <div className="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          {SEEKER_STATS.map((s) => (
            <div key={s.n} className="flex flex-col rounded-md bg-[#F1F6FE] p-6">
              <p className="text-sm font-bold leading-relaxed text-slate-800">{s.text}</p>
              <Num className="mt-4 text-[2.75rem] leading-none text-brand">{s.n}</Num>
              <p className="mt-auto pt-5 text-xs leading-relaxed text-slate-500">{s.src}</p>
            </div>
          ))}
        </div>
      </Section>

      {/* AIの答えは平均6件（図を左、説明を右） */}
      <Section tone="soft">
        <div className="grid items-center gap-12 lg:grid-cols-2">
          <div className="flex items-center gap-4 rounded-md bg-white p-5 shadow-[0_8px_30px_rgba(30,60,120,0.08)] sm:gap-6 sm:p-8">
            <div className="flex flex-1 flex-col gap-2.5">
              <p className="text-sm font-bold text-slate-500">検索結果</p>
              {Array.from({ length: 10 }, (_, i) => <span key={i} className="block h-3 rounded-sm bg-slate-200" />)}
              <p className="text-xs text-slate-500">何十件から、自分で選ぶ</p>
            </div>
            <span aria-hidden className="text-2xl font-bold text-blue-300">→</span>
            <div className="flex flex-1 flex-col gap-2.5 rounded-md border-2 border-brand bg-[#EEF4FE] p-4">
              <p className="text-sm font-bold text-brand">AIの答え</p>
              {["bg-brand", "bg-[#5B8FEA]", "bg-[#8FB2F1]", "bg-[#BCD0F6]", "bg-[#BCD0F6]", "bg-[#BCD0F6]"].map((c, i) => (
                <span key={i} className="flex items-center gap-2"><Num className="w-4 text-xs text-brand">{i + 1}</Num><span className={`block h-5 flex-1 rounded-sm ${c}`} /></span>
              ))}
              <p className="text-xs font-bold text-slate-900">最初から、候補が絞られて届く</p>
            </div>
          </div>
          <div>
            <Head title={<>AIの答えに載るのは、<br className="hidden sm:inline" />平均<Num className="px-1 text-brand">6</Num>件だけ。</>} />
            <dl className="mt-8 space-y-6">
              <div><dt className="text-lg font-bold text-slate-900">6件に入るか、何番目か</dt><dd className="mt-1 leading-relaxed text-slate-700">ここに入れないと、求職者に比べてもらうこともできません。</dd></div>
              <div><dt className="text-lg font-bold text-slate-900">サイト名で聞かれたときの説明も、AIしだい</dt><dd className="mt-1 leading-relaxed text-slate-700">「〇〇ってどんなサイト？」と聞かれたときの評判や特徴の説明も、AIが読んでいる情報で決まります。</dd></div>
            </dl>
            <p className="mt-8 text-xs leading-relaxed text-slate-500">「平均6件」は、当社が求人サイトを対象に行った調査（2026年11月、ChatGPT・Geminiに計162回質問）での、1回の答えに挙がった求人サイト・サービスの数の平均です。</p>
          </div>
        </div>
      </Section>

      {/* zaitoの実例（濃い青の帯に、白いカード） */}
      <Section id="case" tone="navy">
        <Head light title={<>私たちの求人サイトでは、<br className="hidden sm:inline" />3人に1人がChatGPTから。</>} desc="運営する大学生向けの在宅求人サイト「zaito」の、直近28日間の数字です。" />
        <div className="mt-10 grid gap-8 rounded-lg bg-white p-6 text-slate-900 sm:p-10 lg:grid-cols-[1fr_1.1fr] lg:items-center">
          <figure>
            <div className="relative pb-10">
              <Image src="/ai/img/zaito-pc.jpg" alt="求人サイトzaitoのトップページ（パソコン）" width={1800} height={1125} className="w-[86%] rounded-md border border-slate-200 shadow-[0_16px_40px_rgba(30,60,120,0.14)]" />
              <Image src="/ai/img/zaito-sp.jpg" alt="求人サイトzaitoのトップページ（スマホ）" width={700} height={1191} className="absolute bottom-0 right-0 w-[27%] rounded-xl border-4 border-slate-800 shadow-[0_16px_40px_rgba(30,60,120,0.22)]" />
            </div>
            <figcaption className="mt-3 text-xs text-slate-500">zaito：大学生専門の完全在宅バイト・長期インターン求人サイト</figcaption>
          </figure>
          <div className="grid grid-cols-2 gap-2 sm:gap-3">
            <div className="rounded-md bg-[#F1F6FE] p-4 sm:p-5">
              <p className="text-sm font-bold text-slate-800">サイトに来た人のうち、ChatGPTから</p>
              <Num className="mt-3 block text-[2rem] leading-none text-brand sm:text-[2.75rem]">{ZAITO.users.rate}</Num>
              <p className="mt-2 text-xs text-slate-500">{ZAITO.users.n}</p>
            </div>
            <div className="rounded-md bg-[#F1F6FE] p-4 sm:p-5">
              <p className="text-sm font-bold text-slate-800">「応募ページへ進む」などの行動のうち、ChatGPTから</p>
              <Num className="mt-3 block text-[2rem] leading-none text-brand sm:text-[2.75rem]">{ZAITO.events.rate}</Num>
              <p className="mt-2 text-xs text-slate-500">{ZAITO.events.n}</p>
            </div>
            {ZAITO_GROWTH.map((g) => (
              <div key={g.label} className="rounded-md bg-[#F1F6FE] p-4 sm:p-5">
                <p className="text-sm font-bold text-slate-800">{g.label}</p>
                <div className="mt-3 flex items-end justify-between gap-3">
                  <Num className="text-[1.75rem] leading-none text-brand sm:text-4xl">{g.big}</Num>
                  <div aria-hidden className="flex h-12 items-end gap-1 sm:h-16 sm:gap-1.5">
                    <span className="w-3 rounded-t-sm bg-slate-300 sm:w-5" style={{ height: `${Math.max(8, (g.before / g.after) * 100)}%` }} />
                    <span className="h-full w-3 rounded-t-sm bg-brand sm:w-5" />
                  </div>
                </div>
                <p className="mt-2 text-xs text-slate-500">前の28日間 {g.before}{g.unit} → {g.after}{g.unit}</p>
              </div>
            ))}
          </div>
        </div>
        <p className="mt-6 text-xs leading-relaxed text-blue-100">{ZAITO.period}。{ZAITO.note}</p>
      </Section>

      <CtaBand />

      {/* 診断の内容（見出しを左、内容を右） */}
      <Section id="features">
        <div className="grid gap-12 lg:grid-cols-[0.9fr_1.1fr]">
          <div>
            <Head title={<>AIに実際に聞いて、<br className="hidden sm:inline" />紹介された回数を数えます。</>} desc="求職者や採用担当者がChatGPT・Geminiに聞きそうな質問を作り、条件をそろえて何度も聞きます。たとえば、こんな質問です。" />
            <ul className="mt-8 space-y-3">
              {QUESTION_TYPES.map((q) => (
                <li key={q.title} className="rounded-md border border-slate-200 px-5 py-4"><p className="text-xs font-bold text-brand">{q.title}</p><p className="mt-1 font-medium text-slate-900">「{q.ex}」</p></li>
              ))}
            </ul>
          </div>
          <div>
            <p className="text-lg font-bold text-slate-900">診断でわかること</p>
            <ol className="mt-4 divide-y divide-slate-200 rounded-md bg-[#F5F8FD]">
              {FINDINGS.map(([t, b]) => (
                <li key={t} className="px-6 py-5">
                  <p className="text-lg font-bold text-slate-900">{t}</p>
                  <p className="mt-1 leading-relaxed text-slate-700">{b}</p>
                </li>
              ))}
            </ol>
            <Link href="/ai/sample" className="mt-6 inline-flex items-center gap-2 font-bold text-brand hover:underline">レポートの見本を見る<span aria-hidden>→</span></Link>
          </div>
        </div>
      </Section>

      {/* 改善の進め方 */}
      <Section tone="soft">
        <Head center title={<>サイトの中だけでなく、<br />AIが読む<span className="whitespace-nowrap">「サイトの外」</span>まで整えます。</>} />
        <div className="mt-12 grid gap-4 lg:grid-cols-3">
          {WORK.map((w) => (
            <div key={w.title} className="rounded-md bg-white p-7 shadow-[0_8px_30px_rgba(30,60,120,0.06)]">
              <p className="text-xl font-bold text-brand">{w.title}</p>
              <p className="mt-3 leading-relaxed text-slate-700">{w.body}</p>
            </div>
          ))}
        </div>
        <div className="mt-8 grid overflow-hidden rounded-md sm:grid-cols-3">
          {[["1か月目", "サイトの文章を直し、比較記事などへの働きかけを始める", "bg-[#9DBCF5]"], ["2か月目", "測り直し、まだ紹介されない質問を重点的に直す", "bg-[#5B8FEA]"], ["3か月目", "同じ条件で測り直し、効果を報告", "bg-brand"]].map(([m, t, c]) => (
            <div key={m} className={`${c} p-5 text-white`}><p className="text-sm font-bold">{m}</p><p className="mt-1 font-bold">{t}</p></div>
          ))}
        </div>
        <p className="mt-4 text-sm text-slate-600">AIや検索に反映されるまで、数週間〜数か月かかります。そのため、3か月で効果を見る計画をおすすめしています。</p>
      </Section>

      {/* 料金 */}
      <Section id="pricing">
        <Head center title="改善のご依頼は、結果を見てから。" desc="無料診断だけのご利用もできます。有料プランへの自動切り替えはありません。" />
        <div className="mt-12">
          <ServicePlans />
        </div>
        <p className="mt-8 text-center">
          <Link href="/ai/pricing" className="inline-flex items-center gap-2 font-bold text-brand hover:underline">料金の詳細と、他社との比較を見る<span aria-hidden>→</span></Link>
        </p>
      </Section>

      <CtaBand />

      {/* 運営者 */}
      <Section>
        <div className="grid gap-10 lg:grid-cols-[0.9fr_1.1fr]">
          <Head title="求人サイトを自分で運営しながら、AI検索対策を試してきました。" />
          <div className="rounded-md bg-[#F5F8FD] p-7 sm:p-9">
            <p className="text-2xl font-black text-slate-900">鳥居 潤</p>
            <p className="mt-1 text-sm font-bold text-brand">AIススメル 代表</p>
            <div className="mt-5 space-y-4 leading-loose text-slate-700">
              <p>大学生専門の在宅求人サイト「zaito」を運営しています。ChatGPTから来る人が増えたことをきっかけに、AIにどう紹介されているかを測り、直し、また測ることを、自分のサイトで続けてきました。</p>
              <p>AIススメルは、その手順を、ほかの求人サイト・人材サービスでも使えるようにしたものです。</p>
            </div>
          </div>
        </div>
      </Section>

      {/* よくある質問とお知らせ */}
      <Section id="faq" tone="soft">
        <div className="grid gap-10 lg:grid-cols-[0.6fr_1.4fr]">
          <Head title="よくある質問" />
          <div className="rounded-md bg-white px-6 sm:px-8 [&>div]:mt-0 [&>div]:border-0">
            <Faq items={FAQ} />
          </div>
        </div>
        <div className="mt-16 grid gap-10 lg:grid-cols-[0.6fr_1.4fr]">
          <Head title="お知らせ" />
          <ul className="divide-y divide-slate-200 border-y border-slate-200">
            {NEWS.map(([d, t]) => (
              <li key={t} className="grid gap-1 py-5 sm:grid-cols-[9rem_1fr] sm:gap-6"><p className="font-num text-sm font-semibold text-slate-500">{d}</p><p className="text-slate-800">{t}</p></li>
            ))}
          </ul>
        </div>
      </Section>

      {/* 申し込み */}
      <Section tone="navy">
        <Head light center title="3営業日以内に、診断結果をお送りします。" desc="求人サイトのURLを送るだけ。費用はかかりません。" />
        <div id="apply" className="mx-auto mt-10 max-w-xl rounded-md bg-white p-6 text-slate-900 shadow-[0_24px_60px_rgba(0,0,0,0.25)] sm:p-8">
          <HeroLeadForm urlLabel="求人サイトのURL" />
        </div>
      </Section>
    </div>
  );
}
