import Image from "next/image";
import { BookForm } from "@/components/ai/book-form";
import { Band, Head, em } from "@/components/ai/lp";

export const metadata = {
  title: "求人サイトのAI集客はじめてBOOK｜無料ダウンロード | AIススメル",
  description: "求職者がChatGPTで求人を探す時代に、AIに紹介される求人サイトになるための無料ガイド。仕組み・選ばれるサイトの共通点・実践チェックリスト付き。",
};

const PDF = "/ai/jobs-ai-book.pdf";

const LEARN = ["AIが求人サイトを紹介する仕組み", "AIに紹介されやすいサイト・されにくいサイトの共通点", "トップページの「最初の一文」の書き方", "今日から使える12項目の実践チェックリスト", "ChatGPTで自社の紹介状況を確かめる方法"];
const BAD = ["トップページが求人の検索窓と一覧だけで、サイトの説明文がない", "「在宅」「未経験OK」などの強みが、絞り込みボタンにしか書かれていない", "大手の求人サイトと同じ言葉で説明していて、違いが伝わらない"];
const FAQ: [string, string][] = [
  ["AIの知識がなくても読めますか？", "はい。専門用語をできるだけ使わず、求人サイトの運営者が「何から始めればよいか」を中心にまとめています。"],
  ["どんな求人サイト向けですか？", "職種・地域・働き方などに特化した求人サイトや、求人メディアを運営している方に向けた内容です。"],
  ["ダウンロードしたら営業の電話がかかってきますか？", "電話での営業はしません。資料に関するご案内を、メールで1〜2回お送りすることがあります。"],
];

export default function JobsBookPage() {
  return (
    <div className="-mt-8">
      <section className="ml-[calc(50%-50vw)] w-screen border-b border-slate-200 bg-[linear-gradient(180deg,#f3f7ff_0%,#ffffff_100%)]">
        <div className="mx-auto grid max-w-6xl items-center gap-10 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1.1fr_1fr]">
          <div>
            <p className="inline-flex rounded-md bg-brand/10 px-3 py-1 text-sm font-bold text-brand">求人サイト運営者のための無料ガイド</p>
            <h1 className="mt-4 text-[2rem] font-bold leading-[1.3] tracking-tight text-slate-900 sm:text-[2.75rem]">
              求人サイトの
              <br />
              <span className="text-brand">AI集客</span>はじめてBOOK
            </h1>
            <p className="mt-5 max-w-lg leading-relaxed text-slate-700 sm:text-lg">求職者がChatGPTで求人を探す時代に、「AIに紹介される求人サイト」になるための対策を7ページにまとめました。実践チェックリスト付きです。</p>
            <div className="mt-7 flex items-end gap-6">
              <Image src="/ai/jobs-ai-book-cover.jpg" alt="求人サイトのAI集客はじめてBOOKの表紙" width={662} height={936} className="w-40 rounded-md shadow-[0_12px_32px_-12px_rgba(17,24,39,0.35)] ring-1 ring-slate-200 sm:w-48" />
              <ul className="space-y-1.5 text-sm text-slate-700">
                <li>✓ 全7ページ・PDF</li>
                <li>✓ 実践チェックリスト付き</li>
                <li>✓ 30秒で入力完了</li>
              </ul>
            </div>
          </div>
          <div id="download" className="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_12px_32px_-12px_rgba(17,24,39,0.18)] sm:p-7">
            <p className="text-xl font-bold text-slate-900">無料でダウンロード</p>
            <p className="mt-1 mb-5 text-sm text-slate-600">入力したメールアドレスに、資料をお送りします</p>
            <BookForm pdf={PDF} />
          </div>
        </div>
      </section>

      <Band className="bg-white">
        <Head en="いま何が起きているか">
          求職者は、<span className={em}>AIに相談しながら</span>仕事を探しはじめています
        </Head>
        <div className="mt-10 grid gap-5 md:grid-cols-2">
          <div className="rounded-2xl bg-[#f5f8fd] p-6 sm:p-8">
            <p className="font-num text-5xl font-bold text-brand">46.5%</p>
            <p className="mt-2 font-bold text-slate-900">20代の転職希望者のうち、就職・転職活動で生成AIを利用している人</p>
            <p className="mt-3 text-xs leading-relaxed text-slate-600">「よく利用」19.8%＋「時々利用」26.7%。出典：株式会社学情「20代転職希望者の生成AI活用に関する調査」（2025年8月2日〜18日、20代専門転職サイト「Re就活」来訪者、n=303）</p>
          </div>
          <div className="rounded-2xl bg-[#f5f8fd] p-6 sm:p-8">
            <p className="font-num text-5xl font-bold text-brand">84.5%</p>
            <p className="mt-2 font-bold text-slate-900">生成AIを「継続して利用している」大学生。利用サービスはChatGPTが90.4%</p>
            <p className="mt-3 text-xs leading-relaxed text-slate-600">出典：産業能率大学コンテンツビジネス研究所「学生メディア・コンテンツ利用調査2026 −生成AI編−」（2026年4月7日〜7月3日、同大学の在学生、n=2,607）</p>
          </div>
        </div>
        <p className="mx-auto mt-8 max-w-3xl text-center leading-relaxed text-slate-700">若い世代ほどAIを日常的に使っています。「どこで仕事を探せばいいか」をAIに聞く求職者が増えるほど、AIの回答に名前が出るかどうかで差がつきます。</p>
      </Band>

      <Band className="bg-[#f5f8fd]">
        <Head en="こんな求人サイトは要注意">
          AIに<span className={em}>紹介されにくい</span>求人サイトの共通点
        </Head>
        <ul className="mx-auto mt-8 max-w-3xl space-y-3">
          {BAD.map((b) => (
            <li key={b} className="flex gap-3 rounded-xl bg-white p-4 text-slate-800 ring-1 ring-slate-200">
              <span aria-hidden className="font-bold text-rose-600">✕</span>
              {b}
            </li>
          ))}
        </ul>
        <p className="mt-6 text-center font-bold text-slate-900">では、何をどう直せばよいのか。資料で具体的に解説しています。</p>
      </Band>

      <Band className="bg-white">
        <Head en="資料の内容">このBOOKで分かること</Head>
        <ul className="mx-auto mt-8 grid max-w-3xl gap-3 sm:grid-cols-2">
          {LEARN.map((l) => (
            <li key={l} className="flex gap-3 rounded-xl bg-[#f5f8fd] p-4 text-slate-800">
              <span aria-hidden className="font-bold text-brand">✓</span>
              {l}
            </li>
          ))}
        </ul>
        <div className="mt-10 text-center">
          <a href="#download" className="inline-flex min-h-14 w-full max-w-sm items-center justify-center rounded-lg bg-brand px-8 text-lg font-bold text-white shadow-sm hover:bg-brand-dark sm:w-auto">
            無料でダウンロードする
          </a>
          <p className="mt-2 text-sm text-slate-600">※30秒で入力完了</p>
        </div>
      </Band>

      <Band className="bg-[#f5f8fd]">
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
              <p className="pb-5 leading-relaxed text-slate-700">{a}</p>
            </details>
          ))}
        </div>
      </Band>
    </div>
  );
}
