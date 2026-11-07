import Image from "next/image";
import Link from "next/link";
import { HeroLeadForm } from "@/components/ai/hero-lead-form";
import { ArrowLink, CtaBand, Faq, Head, Section, Stat } from "@/components/ai/lp";

// 求人サイト・人材サービス専門のAIススメル（トップ /ai と /ai/for/jobs で同じ中身を出す）
// 文章は短く、数字は大きく。出典や条件は各セクションの下に小さくまとめる


const NEWS = [
  ["2026.11.06", "求人サイト・人材サービス専門のAI検索対策として、サイトをリニューアルしました。"],
  ["2026.11", "求人サイトを対象に、ChatGPT・Geminiでの紹介のされ方を調べる調査を行いました。"],
];

const FAQ: [string, string][] = [
  ["AI検索対策とは、何をすることですか？", "ChatGPTやGeminiに「おすすめの求人サイトは？」と聞かれたとき、御社が候補として紹介されるように整える取り組みです。GEO・LLMOとも呼ばれます。"],
  ["SEO対策とは何が違いますか？", "SEOは検索結果での順位を上げる取り組みです。AI検索対策は、AIが答えをつくるときに参考にする情報を整え、答えの中で紹介されることを目指します。重なる部分も多いので、今のSEOを活かしながら進めます。"],
  ["大手の求人サイトがある中で、紹介されるようになりますか？", "大手は「おすすめの求人サイトは？」のような広い質問で強いため、地域・職種・働き方など、条件のついた質問で紹介されることを目指します。条件が細かいほど、専門の求人サイトが選ばれやすくなります。"],
  ["無料診断だけでも大丈夫ですか？", "はい、無料診断だけのご利用も歓迎です。結果をご覧になってから、改善をお任せいただくかどうかを決めてください。費用がかかるのは、作業の内容と金額にご納得いただき、正式にご依頼いただいてからです。"],
  ["準備するものはありますか？", "無料診断は、求人サイトのURLだけで始められます。資料の準備はいりません。月額プランでは、サイトを更新するための権限や、強み・実績などの情報をご共有いただきます。"],
  ["どのAIを調べますか？", "ChatGPTとGeminiです。無料診断は、ChatGPTだけで質問の数を絞った簡易版です。"],
  ["必ずAIに紹介されるようになりますか？", "AIでの表示や順位、応募数の保証はできません。AIの答えは毎回変わるため、同じ条件で測り直し、変化を数字でご報告します。"],
  ["効果が出るまで、どれくらいかかりますか？", "AIや検索に反映されるまで、数週間〜数か月かかります。3か月を目安に、毎月測りながら進めることをおすすめしています。"],
  ["応募数が増えたかは分かりますか？", "月額プランでは、GA4でAIから来た人数と、そのうち「応募ページへ進む」などの行動をした数を毎月お伝えします。応募完了まで数えられるかは、御社の応募の仕組みによります。"],
  ["比較記事への働きかけでは、何をしますか？", "AIが参考にしている比較記事や紹介ページの運営元へ、御社の正しい情報をお届けします。掲載するかどうかは各媒体の判断です。御社の名前で外に出るものは、すべて事前にご確認いただきます。偽の口コミなど、ステルスマーケティングにあたることは一切行いません。"],
  ["追加の費用はかかりますか？", "月額料金の範囲を超える作業（新しい機能の開発や大きな改修など）が必要なときだけ、先にお見積もりをお出しします。合意なしに費用が増えることはありません。"],
  ["解約はいつでもできますか？", "はい。最低契約期間はありません。当月20日までにご連絡いただくと、当月末で終了します。"],
  ["支払い方法は？", "銀行振込（請求書払い・前払い）です。詳しくは特定商取引法に基づく表記をご覧ください。"],
];

export function JobsLanding() {
  return (
    <div className="-mb-8 -mt-8">
      {/* ファーストビュー */}
      <section className="relative ml-[calc(50%-50vw)] w-screen overflow-hidden bg-brand text-white">
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
            <p className="mt-5 text-sm text-blue-100">入力は1分　｜　資料の準備は不要　｜　3営業日以内にお届け</p>
          </div>
          <figure>
            <div className="rounded-lg bg-white/10 p-2 ring-1 ring-white/20 backdrop-blur-sm sm:p-3">
              <Image src="/ai/img/report-v3.jpg" alt="AIススメルの診断レポートの画面（見本）" width={1600} height={1209} priority className="w-full rounded-md shadow-[0_24px_60px_rgba(0,0,0,0.35)]" />
            </div>
            <figcaption className="mt-3 text-center text-xs text-blue-100">診断レポートの見本（架空の求人サイト）</figcaption>
          </figure>
        </div>
      </section>

      {/* 求職者のAI利用：数字は1つに絞り、1年での伸びを見せる */}
      <Section id="why">
        <div className="grid items-center gap-10 lg:grid-cols-[1fr_1.1fr] lg:gap-16">
          <Head title={<>求職者の仕事探しは、<br />検索からAIへ。</>} desc="転職活動で生成AIを使う20代は、1年で17ポイント増えました。" />
          <div className="rounded-md bg-[#F1F6FE] p-7 sm:p-9">
            <p className="palt font-bold text-slate-800">転職活動で生成AIを使った20代の求職者</p>
            <div className="mt-4 flex items-baseline gap-4">
              <Stat value="63.5" unit="%" />
              <span className="palt rounded-sm bg-brand px-2 py-1 text-sm font-bold text-white">1年で＋17.0ポイント</span>
            </div>
            <dl className="mt-8 space-y-4">
              {[
                { k: "前年の調査", v: 46.5, cls: "bg-slate-300", tc: "text-slate-500" },
                { k: "今年の調査", v: 63.5, cls: "bg-brand", tc: "text-brand" },
              ].map((r) => (
                <div key={r.k} className="grid grid-cols-[5.5rem_1fr_3.5rem] items-center gap-3">
                  <dt className="text-sm font-bold text-slate-600">{r.k}</dt>
                  <dd className="h-7 rounded-sm bg-white"><div className={`h-7 rounded-sm ${r.cls}`} style={{ width: `${r.v}%` }} /></dd>
                  <dd className={`font-num text-right text-lg font-bold ${r.tc}`}>{r.v}%</dd>
                </div>
              ))}
            </dl>
          </div>
        </div>
        <p className="mt-4 text-xs leading-relaxed text-slate-500 lg:text-right">出典：学情「20代転職意識調査」2026年8月・n=186（前年の同調査との比較）</p>
      </Section>

      {/* zaitoの実例 */}
      <Section id="case" tone="navy">
        <Head light title={<>私たちの求人サイトでは、<br />3人に1人がChatGPTから。</>} desc="運営する大学生向け在宅求人サイト「zaito」の直近28日間" />
        <div className="mt-10 grid gap-8 rounded-lg bg-white p-6 text-slate-900 sm:p-10 lg:grid-cols-[1fr_1.1fr] lg:items-center">
          <figure>
            <div className="relative pb-10">
              {/* パソコン：ブラウザの枠に入れる */}
              <div className="w-[86%] overflow-hidden rounded-lg bg-white shadow-[0_16px_40px_rgba(30,60,120,0.16)] ring-1 ring-slate-200">
                <div aria-hidden className="flex items-center gap-1.5 border-b border-slate-200 bg-slate-100 px-3 py-2">
                  <span className="h-2 w-2 rounded-full bg-slate-300" /><span className="h-2 w-2 rounded-full bg-slate-300" /><span className="h-2 w-2 rounded-full bg-slate-300" />
                  <span className="ml-2 h-3 flex-1 rounded-full bg-white" />
                </div>
                <Image src="/ai/img/zaito-pc.jpg" alt="求人サイトzaitoのトップページ（パソコン）" width={1800} height={1125} className="block w-full" />
              </div>
              {/* スマホ：角の丸い端末の枠に入れる */}
              <div className="absolute bottom-0 right-0 w-[28%] rounded-[1.6rem] bg-slate-900 p-[5px] shadow-[0_20px_44px_rgba(15,30,70,0.32)]">
                <div className="relative overflow-hidden rounded-[1.3rem] bg-white">
                  <span aria-hidden className="absolute left-1/2 top-1.5 z-10 h-[7px] w-[34%] -translate-x-1/2 rounded-full bg-slate-900" />
                  <Image src="/ai/img/zaito-sp.jpg" alt="求人サイトzaitoのトップページ（スマホ）" width={700} height={1191} className="block w-full pt-4" />
                </div>
              </div>
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

      {/* 診断の内容：丁寧さは伝えるが、やり方の細部や回数は書かない */}
      <Section id="features">
        <div className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
          <Head title={<>1回聞いて、<br />終わりにしない。</>} desc="同じ質問を何度もAIに聞き、全部の答えを読んで分析します。" />
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
        <p className="mt-3 text-xs text-slate-500">無料診断は、質問の数を絞った簡易版です。</p>
      </Section>

      {/* 改善の進め方：3つの取り組みを、イメージ図つきで見せる（やり方の細部は書かない） */}
      <Section tone="soft">
        <div className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
          <Head title={<>AIに選ばれる理由を、<br />つくる。</>} desc="ページを直すだけでは、AIは推薦しません。伝え方と、外での紹介のされ方まで整えます。" />
          <ArrowLink href="/ai/pricing">作業の範囲を見る</ArrowLink>
        </div>

        <div className="mt-12 space-y-3">
          {/* 1. 伝え方を直す */}
          <div className="grid items-center gap-8 rounded-md bg-white p-7 sm:p-10 lg:grid-cols-[1fr_1.1fr]">
            <div>
              <p className="palt text-2xl font-bold text-slate-900">何のサイトか、一文で伝わるように</p>
              <p className="mt-3 leading-relaxed text-slate-700">誰の、どんな仕事探しに強いサイトなのか。AIにも求職者にも伝わる言葉に直します。</p>
            </div>
            <div className="space-y-2">
              <div className="rounded-md bg-[#F5F6F8] p-5"><p className="text-xs font-bold text-slate-500">よくある説明</p><p className="palt mt-1 font-bold text-slate-500">あなたにぴったりの仕事が見つかる求人サイト。</p></div>
              <div className="rounded-md border-2 border-brand bg-[#F1F6FE] p-5"><p className="text-xs font-bold text-brand">伝わる説明</p><p className="palt mt-1 font-bold text-slate-900">関西の飲食店で、未経験から始められるアルバイトを集めた求人サイト。</p></div>
              <p className="text-xs text-slate-500">架空の求人サイトの例です。</p>
            </div>
          </div>

          {/* 2. 外での紹介を増やす */}
          <div className="grid items-center gap-8 rounded-md bg-white p-7 sm:p-10 lg:grid-cols-[1fr_1.1fr]">
            <div>
              <p className="palt text-2xl font-bold text-slate-900">AIが参考にする記事に、正しく載るように</p>
              <p className="mt-3 leading-relaxed text-slate-700">AIは、比較記事や紹介ページを読んで答えをつくります。そこに御社が正しく載るよう、運営元への情報提供などを進めます。</p>
            </div>
            <div className="rounded-md bg-[#F5F8FD] p-5">
              <p className="text-xs font-bold text-slate-500">AIが答えのもとにしたページ（イメージ）</p>
              <ul className="mt-3 space-y-2 text-sm">
                {[["おすすめ求人サイトの比較記事", "掲載なし → 掲載"], ["地域の求人情報まとめ", "情報が古い → 更新"], ["働き方別の求人サイト紹介", "掲載なし → 掲載"]].map(([t, v]) => (
                  <li key={t} className="flex items-center justify-between gap-3 rounded-md bg-white px-4 py-3"><span className="font-medium text-slate-800">{t}</span><span className="shrink-0 text-xs font-bold text-brand">{v}</span></li>
                ))}
              </ul>
            </div>
          </div>

          {/* 3. 毎月測る */}
          <div className="grid items-center gap-8 rounded-md bg-white p-7 sm:p-10 lg:grid-cols-[1fr_1.1fr]">
            <div>
              <p className="palt text-2xl font-bold text-slate-900">毎月測って、次の手を決める</p>
              <p className="mt-3 leading-relaxed text-slate-700">同じ条件で測り直し、紹介のされ方の変化と、AIから来た人数を毎月ご報告します。</p>
            </div>
            <ol className="grid gap-2 sm:grid-cols-3">
              {[["1か月目", "直す・働きかける"], ["2か月目", "測り直して、足りない所を直す"], ["3か月目", "同じ条件で測り、効果を報告"]].map(([m, t]) => (
                <li key={m} className="rounded-md bg-[#F1F6FE] p-4"><p className="text-sm font-bold text-brand">{m}</p><p className="palt mt-1 text-sm font-bold text-slate-900">{t}</p></li>
              ))}
            </ol>
          </div>
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
            { name: "無料診断", price: "0", unit: "円", body: "AIでの紹介のされ方と、最初に直す3つをお届けします。", href: "/ai/apply", cta: "無料で診断する" },
            { name: "基本プラン", price: "10", unit: "万円／月", body: "毎月の計測と、既存ページの改善（月2件）。", href: "/ai/apply?plan=basic", cta: "基本プランを相談する" },
            { name: "制作プラン", price: "20", unit: "万円／月", body: "基本プランに加えて、選ばれる理由を伝えるページを毎月2本。", href: "/ai/apply?plan=production", cta: "制作プランを相談する", em: true, badge: "おすすめ" },
          ].map((p) => (
            <Link key={p.name} href={p.href} className={`group flex flex-col rounded-md p-7 transition hover:-translate-y-0.5 ${p.em ? "bg-brand text-white" : "bg-[#F1F6FE] text-slate-900"}`}>
              <p className="palt flex items-center gap-2 text-lg font-bold">{p.name}{"badge" in p && p.badge && <span className="rounded-sm bg-white px-2 py-0.5 text-xs font-bold text-brand">{p.badge}</span>}</p>
              <div className="mt-4"><Stat value={p.price} unit={p.unit} className={p.em ? "text-white" : "text-slate-900"} /></div>
              <p className={`mt-4 flex-1 text-sm leading-relaxed ${p.em ? "text-blue-50" : "text-slate-600"}`}>{p.body}</p>
              <span className={`mt-6 inline-flex items-center justify-between border-t pt-4 text-sm font-bold ${p.em ? "border-white/30" : "border-slate-300"}`}>{p.cta}<span aria-hidden className="transition group-hover:translate-x-1">→</span></span>
            </Link>
          ))}
        </div>
        <p className="mt-3 text-xs leading-relaxed text-slate-500">価格は税別です（税込：基本プラン110,000円、制作プラン220,000円）。1か月単位の契約で、最低契約期間はありません。AIでの表示・順位や応募数を保証するものではありません。</p>
      </Section>

      {/* お申し込みの流れ */}
      <Section>
        <Head title="お申し込みの流れ" desc="無料診断から、改善のご依頼まで。どこで止めても費用はかかりません。" />
        <ol className="mt-10 grid gap-3 lg:grid-cols-3">
          {[
            { t: "フォームに入力", b: "会社名・求人サイトのURL・お名前・メールアドレスだけ。1分で終わります。", tag: "1分" },
            { t: "診断結果をお届け", b: "AIでの紹介のされ方と、最初に直す3つを、メールでお送りします。", tag: "3営業日以内" },
            { t: "ご希望なら改善のご相談", b: "結果をもとに、進め方とお見積もりをご案内します。", tag: "無料" },
          ].map((x) => (
            <li key={x.t} className="rounded-md bg-[#F1F6FE] p-7">
              <span className="palt inline-block rounded-sm bg-white px-2 py-0.5 text-xs font-bold text-brand">{x.tag}</span>
              <p className="palt mt-3 text-xl font-bold text-slate-900">{x.t}</p>
              <p className="mt-2 leading-relaxed text-slate-700">{x.b}</p>
            </li>
          ))}
        </ol>
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
        <Head light center title="3営業日以内に、診断結果をお送りします。" desc="求人サイトのURLを送るだけ。資料の準備も、費用もいりません。" />
        <div id="apply" className="mx-auto mt-10 max-w-xl rounded-md bg-white p-6 text-slate-900 shadow-[0_24px_60px_rgba(0,0,0,0.25)] sm:p-8">
          <HeroLeadForm urlLabel="求人サイトのURL" />
        </div>
      </Section>
    </div>
  );
}
