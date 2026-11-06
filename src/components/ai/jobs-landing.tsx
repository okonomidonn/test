import Image from "next/image";
import Link from "next/link";
import { HeroLeadForm } from "@/components/ai/hero-lead-form";
import { ArrowLink, CtaBand, Faq, Head, Section, Stat } from "@/components/ai/lp";

// 求人サイト・人材サービス専門のAIススメル（トップ /ai と /ai/for/jobs で同じ中身を出す）
// 文章は短く、数字は大きく。出典や条件は各セクションの下に小さくまとめる

const SEEKER_STATS = [
  { label: "転職活動で生成AIを使う20代", value: "63.5", unit: "%" },
  { label: "生成AIを使って転職した20代", value: "53.7", unit: "%" },
  { label: "応募先をAIで調べた転職者", value: "26.0", unit: "%" },
  { label: "生成AIを使い続ける大学生", value: "84.5", unit: "%" },
];
const SEEKER_SOURCES = "左から 学情「20代転職意識調査」2026年8月・n=186／マイナビ転職「転職者の生成AI活用に関する実態調査」2026年4月・n=700（直近1年に転職した20代）／Itera「転職活動における企業情報の収集方法に関する調査」2026年8月・n=880／産業能率大学「学生メディア・コンテンツ利用調査2026」・n=2,607";

const WORK = [
  { title: "サイトの中", body: "説明文やよくある質問を、AIが読み取りやすい形に直します。" },
  { title: "サイトの外", body: "AIが参考にする比較記事へ、御社の情報を届けます。" },
  { title: "毎月の計測", body: "同じ質問で測り直し、変化とAIからの流入を報告します。" },
];

const NEWS = [
  ["2026.11.06", "求人サイト・人材サービス専門のAI検索対策として、サイトをリニューアルしました。"],
  ["2026.11", "求人サイトを対象に、ChatGPT・Geminiへ計162回質問する調査を行いました。"],
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
      {/* ファーストビュー */}
      <section className="relative ml-[calc(50%-50vw)] w-screen overflow-hidden bg-[linear-gradient(135deg,#0A2463_0%,#1442B8_60%,#2563EB_100%)] text-white">
        <div aria-hidden className="pointer-events-none absolute -right-40 -top-40 h-[36rem] w-[36rem] rounded-full bg-[radial-gradient(closest-side,rgba(147,197,253,0.35),transparent)]" />
        <div className="relative mx-auto grid max-w-6xl items-center gap-12 px-4 pb-16 pt-14 sm:px-6 sm:pb-20 sm:pt-20 lg:grid-cols-[1.05fr_1fr]">
          <div>
            <p className="palt text-sm font-bold text-blue-200">求人サイト・人材サービス専門のAI検索対策</p>
            <h1 className="mt-4 text-[2.5rem] font-black leading-[1.2] sm:text-[3.5rem] xl:text-[3.875rem]">
              求人は、
              <br />
              <span className="whitespace-nowrap">AIで探す時代へ。</span>
            </h1>
            <p className="palt mt-6 text-lg font-bold leading-relaxed text-blue-50 sm:text-xl">ChatGPTに、御社をおすすめしてもらう。</p>
            <div className="mt-9 flex flex-col gap-3 sm:flex-row">
              <Link href="/ai/apply" className="inline-flex min-h-14 items-center justify-center gap-3 rounded-md bg-white px-8 text-lg font-bold text-brand shadow-[0_8px_24px_rgba(0,0,0,0.18)] transition hover:bg-blue-50">無料で診断する<span aria-hidden>→</span></Link>
              <Link href="/ai/sample" className="inline-flex min-h-14 items-center justify-center rounded-md border border-white/60 px-8 font-bold text-white transition hover:bg-white/10">レポートの見本を見る</Link>
            </div>
            <p className="mt-5 text-sm text-blue-100">費用0円　｜　3営業日以内にお届け　｜　登録不要</p>
          </div>
          <figure>
            <div className="rounded-lg bg-white/10 p-2 ring-1 ring-white/20 backdrop-blur-sm sm:p-3">
              <Image src="/ai/img/report-v2.jpg" alt="AIススメルの診断レポートの画面（見本）" width={1600} height={1209} priority className="w-full rounded-md shadow-[0_24px_60px_rgba(0,0,0,0.35)]" />
            </div>
            <figcaption className="mt-3 text-center text-xs text-blue-100">診断レポートの見本（架空の求人サイト）</figcaption>
          </figure>
        </div>
      </section>

      {/* 求職者のAI利用 */}
      <Section id="why">
        <div className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
          <Head title={<>求職者の仕事探しは、<br />検索からAIへ。</>} />
          <ArrowLink href="/ai/method">調べ方と数字の見方</ArrowLink>
        </div>
        <div className="mt-10 grid grid-cols-2 gap-2 sm:gap-3 lg:grid-cols-4">
          {SEEKER_STATS.map((s) => (
            <div key={s.label} className="rounded-md bg-[#F1F6FE] px-4 py-5 sm:px-6 sm:py-7">
              <p className="palt text-[0.9375rem] font-bold text-slate-800">{s.label}</p>
              <div className="mt-4"><Stat value={s.value} unit={s.unit} /></div>
            </div>
          ))}
        </div>
        <p className="mt-4 text-xs leading-relaxed text-slate-500">{SEEKER_SOURCES}</p>
      </Section>

      {/* zaitoの実例 */}
      <Section id="case" tone="navy">
        <Head light title={<>私たちの求人サイトでは、<br />3人に1人がChatGPTから。</>} desc="運営する大学生向け在宅求人サイト「zaito」の直近28日間" />
        <div className="mt-10 grid gap-8 rounded-lg bg-white p-6 text-slate-900 sm:p-10 lg:grid-cols-[1fr_1.1fr] lg:items-center">
          <figure>
            <div className="relative pb-10">
              <Image src="/ai/img/zaito-pc.jpg" alt="求人サイトzaitoのトップページ（パソコン）" width={1800} height={1125} className="w-[86%] rounded-md border border-slate-200 shadow-[0_16px_40px_rgba(30,60,120,0.14)]" />
              <Image src="/ai/img/zaito-sp.jpg" alt="求人サイトzaitoのトップページ（スマホ）" width={700} height={1191} className="absolute bottom-0 right-0 w-[27%] rounded-xl border-4 border-slate-800 shadow-[0_16px_40px_rgba(30,60,120,0.22)]" />
            </div>
          </figure>
          <div className="grid grid-cols-2 gap-2 sm:gap-3">
            {[
              { label: "来た人のうちChatGPTから", value: "35.5", unit: "%", sub: "1,390人中494人" },
              { label: "応募ページへ進んだうちChatGPTから", value: "48.7", unit: "%", sub: "610件中297件" },
            ].map((c) => (
              <div key={c.label} className="rounded-md bg-[#F1F6FE] p-4 sm:p-5">
                <p className="palt text-sm font-bold text-slate-800">{c.label}</p>
                <div className="mt-3"><Stat value={c.value} unit={c.unit} /></div>
                <p className="mt-2 text-xs text-slate-500">{c.sub}</p>
              </div>
            ))}
            {[
              { label: "ChatGPTから来た人", big: "7", before: 68, after: 494, unit: "人" },
              { label: "ChatGPTから応募ページへ", big: "6", before: 49, after: 297, unit: "件" },
            ].map((g) => (
              <div key={g.label} className="rounded-md bg-[#F1F6FE] p-4 sm:p-5">
                <p className="palt text-sm font-bold text-slate-800">{g.label}</p>
                <div className="mt-3 flex items-end justify-between gap-2">
                  <Stat pre="約" value={g.big} unit="倍" />
                  <div aria-hidden className="flex h-12 items-end gap-1 sm:h-14">
                    <span className="w-3 rounded-t-sm bg-slate-300 sm:w-4" style={{ height: `${Math.max(10, (g.before / g.after) * 100)}%` }} />
                    <span className="h-full w-3 rounded-t-sm bg-brand sm:w-4" />
                  </div>
                </div>
                <p className="mt-2 text-xs text-slate-500">{g.before}{g.unit} → {g.after}{g.unit}</p>
              </div>
            ))}
          </div>
        </div>
        <p className="mt-6 text-xs leading-relaxed text-blue-100">2026年10月8日〜11月4日・GA4。ChatGPTはchatgpt.comからのみ。「応募ページへ進む」などは応募完了の数ではありません。Instagram広告からの訪問を含みます。倍率は前の28日間（9月24日〜10月21日）との比較で、公開直後の伸びを含みます。同じ効果を保証するものではありません。</p>
      </Section>

      <CtaBand title="まずは、今の紹介のされ方を知る。" />

      {/* 診断の内容：丁寧さは伝えるが、やり方の細部は書かない */}
      <Section id="features">
        <div className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
          <Head title={<>1つの求人サイトのために、<br />AIに<span className="font-num px-1 text-brand">162</span>回聞きました。</>} desc="1回聞いて終わり、ではありません。何度も聞いて、全部の答えを読みます。" />
          <ArrowLink href="/ai/sample">レポートの見本を見る</ArrowLink>
        </div>
        <div className="mt-10 grid gap-3 lg:grid-cols-3">
          {[
            { t: "どの質問で、紹介されるか", b: "紹介される場面と、されない場面がわかります。" },
            { t: "どの競合が、先に出るか", b: "AIの答えの中での立ち位置がわかります。" },
            { t: "なぜ、紹介されないのか", b: "原因と、最初に直すことがわかります。" },
          ].map((c) => (
            <div key={c.t} className="rounded-md bg-[#F1F6FE] p-7">
              <p className="palt text-xl font-bold text-slate-900">{c.t}</p>
              <p className="mt-2 text-slate-600">{c.b}</p>
            </div>
          ))}
        </div>
        <p className="mt-3 text-xs text-slate-500">162回は、ある求人サイトの診断（2026年11月）での回数です。回数はサイトに合わせて決めます。無料診断は簡易版です。</p>
      </Section>

      {/* 改善の進め方 */}
      <Section tone="soft">
        <div className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
          <Head title={<>サイトの中だけでなく、<br />「サイトの外」まで。</>} />
          <ArrowLink href="/ai/pricing">作業の範囲を見る</ArrowLink>
        </div>
        <div className="mt-10 grid gap-3 lg:grid-cols-3">
          {WORK.map((w) => (
            <div key={w.title} className="rounded-md bg-white p-7">
              <p className="palt text-2xl font-bold text-brand">{w.title}</p>
              <p className="mt-3 leading-relaxed text-slate-700">{w.body}</p>
            </div>
          ))}
        </div>
        <div className="mt-3 grid overflow-hidden rounded-md sm:grid-cols-3">
          {[["1か月目", "直す・働きかける", "bg-[#9DBCF5]"], ["2か月目", "測り直して、足りない所を直す", "bg-[#5B8FEA]"], ["3か月目", "同じ条件で測り、効果を報告", "bg-brand"]].map(([m, t, c]) => (
            <div key={m} className={`${c} p-5 text-white`}><p className="text-sm font-bold">{m}</p><p className="palt mt-1 font-bold">{t}</p></div>
          ))}
        </div>
      </Section>

      {/* 料金（3つだけ大きく見せる） */}
      <Section id="pricing">
        <div className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
          <Head title="まずは無料診断から。" desc="改善のご依頼は、結果を見てから決めてください。" />
          <ArrowLink href="/ai/pricing">料金の詳細・他社との比較</ArrowLink>
        </div>
        <div className="mt-10 grid gap-3 lg:grid-cols-3">
          {[
            { name: "無料診断", price: "0", unit: "円", body: "AIでの紹介のされ方と、最初に直す3つをお届けします。", href: "/ai/apply", cta: "無料で診断する", em: true },
            { name: "基本プラン", price: "10", unit: "万円／月", body: "毎月の計測と、既存ページの改善（月2件）。", href: "/ai/apply?plan=basic", cta: "基本プランを相談する" },
            { name: "制作プラン", price: "20", unit: "万円／月", body: "基本プランに加えて、新しいページの制作（月2本）。", href: "/ai/apply?plan=production", cta: "制作プランを相談する" },
          ].map((p) => (
            <Link key={p.name} href={p.href} className={`group flex flex-col rounded-md p-7 transition hover:-translate-y-0.5 ${p.em ? "bg-brand text-white" : "bg-[#F1F6FE] text-slate-900"}`}>
              <p className="palt text-lg font-bold">{p.name}</p>
              <div className="mt-4"><Stat value={p.price} unit={p.unit} className={p.em ? "text-white" : "text-slate-900"} /></div>
              <p className={`mt-4 flex-1 text-sm leading-relaxed ${p.em ? "text-blue-50" : "text-slate-600"}`}>{p.body}</p>
              <span className={`mt-6 inline-flex items-center justify-between border-t pt-4 text-sm font-bold ${p.em ? "border-white/30" : "border-slate-300"}`}>{p.cta}<span aria-hidden className="transition group-hover:translate-x-1">→</span></span>
            </Link>
          ))}
        </div>
        <p className="mt-3 text-xs text-slate-500">価格は税別です（税込：基本プラン110,000円、制作プラン220,000円）。1か月単位の契約で、最低契約期間はありません。</p>
      </Section>

      {/* よくある質問・お知らせ */}
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
              <li key={t} className="grid gap-1 py-5 sm:grid-cols-[7rem_1fr] sm:gap-6"><p className="font-num text-sm font-semibold text-slate-500">{d}</p><p className="text-slate-800">{t}</p></li>
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
